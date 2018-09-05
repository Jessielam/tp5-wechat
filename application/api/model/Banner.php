<?php

namespace app\api\model;

class Banner extends Base
{
    protected $hidden = ['delete_time', 'update_time'];

    // 定义模型关联
    public function items()
    {
        // hasMany(关联模型的模型名， 外键， 当前模型的主键)
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    public static function getBannerById($id)
    {
        return self::with(['items', 'items.img'])->find($id);
    }
}