<?php

//  +---------------------------------
// 订单流程：
// NOTE: 用户在选择商品后, 向api提交所包含的商品信息
// api接收到信息后，需要检查订单相关商品的库存量
// 有库存，把数据存入数据库中，下单成功，返回客户端消息，告诉客户端可以支付
// 调用支付接口，进行订单支付
// 支付需要对库存量进行检查
// 服务器调用支付接口，进行支付。
// 支付服务器返回一个支付结果，对支付结果进行判断，对库存量进行操作（支付成功、 支付失败） 异步
// 即使支付成功 还需要一次对库存量的检测(支付过程中存在延时，或者时间过长)
// 成功、库存量量的扣除
//  +-----------------------------------

namespace app\api\controller\v1;

use think\Request;
use app\api\controller\BaseControler;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\api\controller\BaseController;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\api\model\Order as OrderModel;
use app\api\validate\IDMustBePostiveInt;

class Order extends BaseController
{
    // 定义前置方法，控制权限
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail,getOrderListByUser'],
        // 'checkSuperScope' => ['only' => 'delivery,getSummary']
    ];
    
    // 用户下单
    public function placeOrder()
    {
        (new OrderPlace())->goCheck();
        // 获取订单数据
        $products = input('post.products/a'); // 加 /a 才能获取数组
        $uid = TokenService::getCurrentUid();
        $order = (new OrderService())->place($uid, $products);

        return $order;
    }

    /**
     * 获取某一用户所有订单
     * @param int $page 当前页
     * @param int $size 每页最大数量
     */
    public function getOrderListByUser($page=1, $size=5)
    {
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);
        if ($pagingOrders->isEmpty())
        {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => ['data' => []]
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])->toArray();

        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    // 获取订单详情
    public function getOrderDetail($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $detail = OrderModel::get($id); // get 模型方法
        if (!$detail) {
            throw new OrderException();
        }

        $data = $detail->hidden(['prepay_id']);
        return $data->toArray();
    } 

    /**
     * 获取全部订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page=1, $size = 20){
        (new PagingParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByPage($page, $size);
        if ($pagingOrders->isEmpty())
        {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => ['data' => []]
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    // 订单发货
    public function delivery($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            return new SuccessMessage();
        }
    }
}
