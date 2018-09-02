<?php

namespace app\api\model;

class BannerItem extends Base
{
    protected $hidden = ['id', 'img_id', 'banner_id', 'delete_time', 'update_time'];
    
    public function banner()
    {
        return $this->belongsTo('Banner', 'banner_id', 'id');
    }
    //
    public function img()
    {
        return $this->belongsTo('Image', 'img_id', 'id');
    }
}
