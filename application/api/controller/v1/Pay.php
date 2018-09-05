<?php

namespace app\api\controller\v1;

use think\Request;
use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use think\Loader;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    // 请求预订单信息, 只有用户权限
    public function getPreOrder($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $pay = new PayService($id);
        return $pay->pay();  // signature
    }

    //微信回调通知 post xml格式的数据
    public function payNotify()
    {
        // 通知频率 15/15/30/180/1800....
        // 1.检测库存量: 没有库存
        // 2.更新订单状态
        // 3.修改库存量
        // 处理成功，响应微信服务器处理成功的消息
        $wxNotify = new WxNotify();
        $config = new \WxPayConfig();
        $wxNotify->Handle($config);        
    }
}
