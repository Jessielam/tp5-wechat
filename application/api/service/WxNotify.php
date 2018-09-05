<?php

namespace app\api\service;

use think\Loader;
use think\Log;
use think\Exception;
use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatus;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
    // 继承父类的方法
    public function NotifyProcess($data, $config, &$msg) 
    {
        // 如果交易成功
        if ($data['result_code'] == 'SUCCESS') {
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                $order = OrderModel::where('order_no', '=', $orderNo)->lock(true)->find();
                if ($order->status == OrderStatus::UNPAY) {
                    $orderService = new Order();
                    $stockStatus = $orderService->checkOrderStock($order->id);
                    if ($status['pass']) {
                        // 更新订单状态
                        $this->updateOrderStatus($order->id, true);
                        // 更新库存数量
                        $this->reduceStock($stockStatus);
                    } else {
                        $this->updateOrderStatus($order->id);
                    }
                }
                Db:commit();
            } catch (Exception $e) {
                Db::rollback();
                Log::error($e->getMessage());
                 // 如果出现异常，向微信返回false，请求重新发送通知
                return false;
            }
        }

        return true;
    }

    private function updateOrderStatus($orderId, $success = false)
    {
        $status = $success ? OrderStatus::PAID : OrderStatus::PAID_BUT_OUT_OF_STOCK;
        Order::where('id', '=', $orderId)->update(['status' => $status]);
    }

    // 减少商品库存
    private function reduceStock($stockStatus)
    {
        if ($stockStatus) { 
            foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
                Product::where('id', '=', $singlePStatus['id'])
                ->setDec('stock', $singlePStatus['count']);
            } 
        }

        return true;
    }
}