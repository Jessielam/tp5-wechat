<?php

namespace app\api\service;

use think\Exception;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\api\service\Token as TokenService;
use app\lib\enum\OrderStatus;
use think\Loader;
use think\Log;

// 使用官方的sdk
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay
{
    private $orderId;
    private $orderNo;

    public function __construct($id)
    {
        if (!$id) {
            throw new Exception('订单号不允许为空');
        }
        $this->orderId = $id;
    }

    public function pay()
    {
        // 先检查订单是否合理
        $this->checkOrderValid();

        // 库存量检测
        $orderService = new Order();
        $orderStatus = $orderService->checkOrderStock($this->orderId);
        if (!$orderStatus['pass']) {
            return $orderStatus;
        }
        // 拉起微信支付
        $this->makeWxPay($orderStatus['orderPrice']);
    }

    private function makeWxPay($totalPrice)
    {
        $openId = TokenService::getCurrentTokenVal('openid');
        if (!$openId) {
            throw new TokenException();
        } 

        // 统一下单
        $wxPay = new \WxPayUnifiedOrder();
        $wxPay->SetOut_trade_no($this->orderNo);
        $wxPay->SetTrade_type('JSAPI');
        $wxPay->SetTotal_fee($totalPrice * 100);
        $wxPay->SetBody('零食商贩');
        $wxPay->SetOpenid($openId);
        $wxPay->SetNotify_url(config('wxpay.notify_url'));

        return $this->getPaySignature($wxPay);
    }

    //向微信请求订单号并生成签名
    private function getPaySignature($wxOrderData)
    {
        // 获取配置项
        $wxConfig = new \WxPayConfig();
        $wxOrder = \WxPayApi::unifiedOrder($wxConfig, $wxOrderData);
        // 失败时不会返回result_code
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
            throw new Exception('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxConfig, $wxOrder);

        return $signature;
    }

    // 生成签名
    private function sign($config, $wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        // $rand = md5(time() . mt_rand(0, 1000)); // 随机字符串
        $jsApiPayData->SetNonceStr(\WxPayApi::getNonceStr());
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        // $sign = $jsApiPayData->MakeSign($config);
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']); // 不要把appid返回到客户端去

        return $rawValues;
    }

    private function recordPreOrder($wxOrder)
    {
        // 更新 prepay_id 字段
        return OrderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    private function checkOrderValid()
    {
        // 获取订单信息
        $order = OrderModel::where('id', '=', $this->orderId)->find();
        if (!$order) {
            throw new OrderException();
        }

        // 检查订单与用户的关系，订单是否已经支付
        if (!TokenService::isValidateOperate($order->user_id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        if ($order->status != OrderStatus::UNPAY) {
            throw new OrderException([
                'msg' => '该订单已经支付了',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }

        // 订单编号
        $this->orderNo = $order->order_no;

        return true;
    }
}