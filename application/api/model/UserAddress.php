<?php

namespace app\api\model;

class UserAddress extends Base
{
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id');
    }
}