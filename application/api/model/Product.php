<?php

namespace app\api\model;

class Product extends Base
{
    // 隐藏不需要返回的属性
    protected $hidden = [
        'delete_time',
        'main_img_id',
        'from',
        'create_time',
        'update_time',
        'pivot'  // 多对多自动加一个pivot属性 中间表数据
    ];

    public function getMainImgUrlAttr(string $value='', array $data) : string
    {
        return $this->prefixImgUrl($value, $data);
    }
}
