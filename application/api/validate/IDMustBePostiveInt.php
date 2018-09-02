<?php

namespace app\api\validate;

class IDMustBePostiveInt extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPostiveInteger'
    ];

    protected function isPostiveInteger($value, $rule = '', $data = '', $field = '')
    {
        // id 必须是正整数
        if (preg_match('/^[1-9]\d*$/', $value)) {
            return true;
        } else {
            return $field . "必须是正整数";
        }
    }
}