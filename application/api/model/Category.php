<?php

namespace app\api\model;

class Category extends Base
{
    protected $hidden = ['topic_img_id', 'create_time', 'delete_time'];
    public function img()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public static function getAllCategories()
    {
        // 第二个参数是关联模型 string|array
        return self::all([], ['img']);
    }
}
