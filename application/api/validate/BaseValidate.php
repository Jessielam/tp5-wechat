<?php

namespace app\api\validate;

use think\Validate;
use think\Request;
use app\lib\exception\ParameterException;

class BaseValidate extends Validate
{
    public function goCheck()
    {
        // 1.获取http 传入的参数
        $params =  Request::instance()->param();

        // 2.对传入的参数做校验
        $result = $this->check($params);

        if (!$result) {
            $exception = new ParameterException([
                'msg' => is_array($this->error) ? implode(';', $this->error) : $this->error,
            ]);
    
            throw $exception;
        } else {
            return true;
        }
    }

    protected function isPostiveInteger($value, $rule = '', $data = '', $field = '')
    {
        // id 必须是正整数
        if (preg_match('/^[1-9]\d*$/', $value)) {
            return true;
        } else {
            return false;
        }
    }
}