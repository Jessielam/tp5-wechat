<?php

namespace app\api\model;

class Theme extends Base
{
    protected $hidden = ['delete_time', 'update_time', 'head_img_id', 'topic_img_id'];
    // 定义模型关联
    public function topImg()
    {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public function headImg()
    {
        return $this->belongsTo('Image', 'head_img_id', 'id');
    }

    public function getThemsListsByIds(array $ids)
    {
        return self::with(['topImg', 'headImg'])->select($ids);
    }

    public function products()
    {
        return $this->belongsToMany('Product', 'theme_product', 'product_id', 'theme_id');
    }

    public static function getThemeWithProducts($id)
    {
        return self::with(['products', 'headImg', 'topImg'])->find($id);
    }
}
