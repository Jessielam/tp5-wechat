<?php

namespace app\api\service;

use app\api\model\User;
use app\lib\expection\OrderException;

class DeliveryMessage extends WxMessage
{
    const DELIERY_MSG_ID = '';
     
    public function sendDeliveryMessage($order, $temp_url = '')
    {
        if (!$order) {
            throw new OrderException();
        }

        $this->tplID = self::DELIERY_MSG_ID;
        $this->fromID = $order->prepay_id;
        $this->page = $temp_url;
        $this->prepareMessageData($order);
        $this->emphasisKeyWord = 'keyword2.DATA';

        return parent::sendMessage($this->getUserOpenID($order->user_id));
    }

    private function prepareMessageData($order)
    {
        $dt = new \DateTime();

        // 模板参数，视设置而定
        $data = [
            'keywords1' => [
                'values1' => 'xxx'
            ],
            'keywords2' => [
                'values2' => 'xxx'
            ],
            'keywords3' => [
                'values3' => 'xxx'
            ],
            'keywords4' => [
                'values4' => 'xxx'
            ],
            'keywords5' => [
                'values5' => 'xxx'
            ]            
        ];

       $this->data = $data;
    }

    private function getUserOpenID($user_id)
    {
        $user = User::get($user_id);

        if (!$user) {
            throw new UserExceptino();
        }

        return $user->openid;
    }
}