<?php

namespace app\api\model;

class Image extends Base
{
    protected $hidden = ['delete_time', 'id', 'from', 'update_time'];

    // 定义读获取器， 获取图片的完整链接  获取器
    public function getUrlAttr(string $value, array $data)
    {
        return $this->prefixImgUrl($value, $data);
    }
}
