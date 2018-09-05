<?php

namespace app\api\model;

class User extends Base
{
    public function address()
    {   
        // 拥有外键的表用belongsTo, 没有外键的用hasOne
        return $this->hasOne('UserAddress', 'user_id', 'id');
    }

    /**
     * 根据openid 获取用户,用户是否存在
     * @param string $openId
     */
    public static function getUserByOpenId($openId)
    {
        return Self::where('openid', '=', $openId)->find();
    }
}