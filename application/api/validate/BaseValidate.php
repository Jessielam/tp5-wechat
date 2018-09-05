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

    protected function isNotEmpty($value)
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    // 手机号码验证
    protected function isMobile($value)
    {
        $rule = '/^1(3|4|5|7|8)[0-9]\d{8}$/';
        if (preg_match($rule, $value)) {
            return true;
        } else {
            return false;
        }
    }

    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        }

        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }

        return $newArray;
    }
}