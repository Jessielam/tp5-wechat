<?php

namespace app\api\service;

use think\Cache;
use think\Db;
use think\Exception;
use think\Request;
use app\lib\enum\ScopeEnum;
use app\lib\exception\{TokenException, ForbiddenException, OrderException};
use app\api\model\Product as ProductModel;
use app\api\model\UserAddress;
use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct;
use app\lib\exception\UserException;
use app\lib\enum\OrderStatus;

class Order
{
    // 订单的商品列表，客户下单的订单数据
    protected $oProducts;

    // 真是的商品信息，包括库存量
    protected $products;
    protected $uid;

    // public function __construct($uid, $oProducts)
    // {
    //     $this->oProducts = $oProducts;
    //     $this->uid = $uid;
    // }

    // 检测库存量
    // 创建订单
    public function place($uid, $oProducts)
    {
        // 根据订单的商品信息查询商品的实际数据
        $this->oProducts = $oProducts;
        $this->uid = $uid;
        $this->products = $this->getProductsByOrder($this->oProducts);
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        // 订单通过检测,创建订单快照
        $orderSnap =  $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;

        return $order;
    }

    // 创建订单
    private function createOrder(array $snap)
    {
        // NOTE: 考虑是否使用事务
        Db::startTrans();
        try{
            $order = new OrderModel();
            $orderNo = $this->makeOrderNo();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();

            $orderId = $order->id; // 新创建订单的id
            $create_time = $order->create_time; // 订单创建时间

            foreach($this->oProducts as &$product) {
                $product['order_id'] = $orderId;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'orderNo' => $orderNo,
                'order_id' => $orderId,
                'create_time' => $create_time
            ];
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    // 生成订单号
    public function makeOrderNo() : string
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));

        return $orderSn;
    }

    // 根据订单的商品信息查询商品的实际数据
    private function getProductsByOrder($oProducts)
    {
        $iProductIds = [];
        foreach ($oProducts as $item) {
            $iProductIds[] = $item['product_id'];
        }

        // 避免循环查询数据库
        $products = ProductModel::all($iProductIds)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();
        
        return $products;
    }

    // 给外部调用的入口，检查商品数据是否有库存
    public function checkOrderStock($orderId)
    {   
        if (!$orderId) {
            throw new Exception('没有找到订单号');
        }

        $oProducts = OrderProduct::where('order_id', '=', $orderId)->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($this->oProducts);
        $status = $this->getOrderStatus();

        return $status;
    }

    // 判断该订单的状态
    private function getOrderStatus()
    {
        $status = [
            'pass' => true, // 通过，允许下单
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => [] //保存订单里的详细信息 订单的商品
        ];

        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count']);
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $oProduct['count'];
            array_push($status['pStatusArray'], $pStatus);
        }

        return $status;
    }

    /**
     * 获取订单某一商品的状态
     * @param int $oProductId 订单某一商品id
     * @param int $oCount 订单某一商品的购买数量
     */
    private function getProductStatus($oProductId, $oCount)
    {
        $pIndex = -1;
        $pStatus = [
            'id' => '',
            'haveStock' => false,
            'counts' => 0,
            'name' => '',
            'price' => 0,
            'totalPrice' => 0,
            'main_img_url' => ''
        ];
        $products = $this->products;
        // 实际的商品数据是否有该商品记录
        foreach($products as $key => $product) {
            if ($oProductId == $product['id']) {
                $pIndex = $key;
            }
        }
        if ($pIndex == -1) {
            // 客户端传递的productid有可能根本不存在
            throw new OrderException([
                    'msg' => 'id为' . $oProductId . '的商品不存在，订单创建失败'
                ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['counts'] = $oCount;
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            $pStatus['price'] = $product['price'];
            $pStatus['main_img_url'] = $product['main_img_url'];
            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }

    private function snapOrder($status)
    {
        $snap = [
            'orderPrice' => $status['orderPrice'] ?? 0,
            'totalCount' => $status['totalCount'] ?? 0,
            'pStatus' => $status['pStatusArray'] ?? [],
            'snapAddress' => json_encode($this->getUserAddress()),
            'snapName' => $this->products[0]['name'], // 如果有多个商品，显示第一个
            'snapImg' =>  $this->products[0]['main_img_url']
        ];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }

        return $snap;
    }

    private function getUserAddress()
    {
        // 判断用户已经设置收货地址
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'errorCode' => 60001,
            ]);
        }

        return $userAddress->toArray();
    }

    public function delivery($id, $jumpPage = '')
    {
        $order = OrderModel::where('id', $id)->find();
        if (!$order) {
            throw new OrderException();
        }

        if ($order->status !== OrderStatus::PAID) {
            throw new OrderException([
                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatus::DELIVERED;
        $order->save();

        // 发送模板消息
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order);
    }
}
