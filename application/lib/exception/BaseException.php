<?php

namespace app\lib\exception;

class BaseException extends \Exception
{
    // HTTP 状态码
    public $code = 400;

    // 错误信息
    public $msg = 'Params error.';

    // 自定义错误码
    public $errorCode = 10000;

    public function __construct($params = []) 
    {
        if (!is_array($params)) {
            return ;
        }

        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }

        if (array_key_exists('msg', $params)) {
            $this->msg = $params['msg'];
        }

        if (array_key_exists('errorCode', $params)) {
            $this->msg = $params['errorCode'];
        }
    }
}