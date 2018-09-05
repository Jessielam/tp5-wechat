<?php
//  +---------------------------------
// order = [
//     [ 'id' => 1, 'count' => 10],
//     [ 'id' => 5, 'count' => 1],
//     .
//     .
//     .
// ];
//  +-----------------------------------


namespace app\api\validate;

use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    protected $rule = [
        'products' => 'checkOrder'
    ];

    protected $singleRule = [
        'product_id' => 'require|isPostiveInteger',
        'count' => 'require|isPostiveInteger'
    ];

    // 自定义验证器
    protected function checkOrder($value)
    {
        if (empty($value)) {
            throw new ParameterException([
                'msg' => '订单列表参数不合法'
            ]);
        }

        if (!is_array($value)) {
            throw new ParameterException(['订单列表不能为空']);
        }

        foreach ($value as $key => $val)
        {
            $this->checkOrderItem($val);
        }

        return true;
    }

    protected function checkOrderItem($product)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($product);
        
        if (!$result) {
            throw new ParameterException([
                'msg' => '订单商品参数错误'
            ]);
        }
    }
}