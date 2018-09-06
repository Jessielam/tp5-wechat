<?php

namespace app\api\model;

class Order extends Base
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];
    protected $autoWriteTimestamp = true;

    public function products()
    {
        return $this->belongsToMany('Product', 'order_product', 'product_id', 'order_id');
    }

    public function getSnapItemsAttr($value)
    {
        if(empty($value))
        {
            return null;
        }
        return json_decode($value);
    }

    public function getSnapAddressAttr($value)
    {
        if(empty($value)){
            return null;
        }
        return json_decode(($value));
    }

    /**
     * @param int $uid 当前的用户id
     * @param int $page 当前页数
     * @param int $size 每一页最大显示数量
     */
    public static function getSummaryByUser($uid, $page=1, $size=15)
    {
        $pagingData = self::where('user_id', '=', $uid)->order('create_time desc')
            ->paginate($size, true, ['page' => $page]);

        return $pagingData ;
    }

    public static function getSummaryByPage($page=1, $size=20){
        $pagingData = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
            
        return $pagingData ;
    }

    public function getAddressByUser()
    {
        //TODO: 用户获取地址信息
    }

}
