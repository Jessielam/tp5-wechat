<?php

namespace app\api\service;

use think\Exception;

class WxMessage
{
    private $sendUrl = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?" .
    "access_token=%s";

    private $toUser;
    private $color = 'black';

    protected $tplID;
    protected $fromID;
    protected $page;
    protected $data;
    protected $emphasisKeyWord;

    public function __construct()
    {
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $this->sendUrl = sprintf($this->sendUrl, $token);
    }

    protected function sendMessage($openId)
    {

        $data = [
            'toUser' => $openId,
            'template_id' => $this->tplID,
            'page' => $this->page,
            'data' => $this->data,
            'color' => $this->color,
            'emphasisKeyWord' => $this->emphasisKeyWord
        ];

        $result = json_decode(curl_get($this->sendUrl, $data), true);
        if ($result['errcode'] == 0) {
            return true;
        } else {
            throw new Exception('模板消息发送失败,' . $result['errmsg']);
        }
    }
}