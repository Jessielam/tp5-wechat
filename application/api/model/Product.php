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

    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    public function properties()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }

    public function getMainImgUrlAttr(string $value='', array $data) : string
    {
        return $this->prefixImgUrl($value, $data);
    }

    public static function getMostRecent(int $count)
    {  
        $products = self::limit($count)
            ->order('create_time', 'desc')
            ->select();
        
        return $products;
    }

    /**
     * 获取某分类下的商品数据
     * 
     * @param int $categoryId 分类id
     * @param boolean $paginate 是否分页
     * @param int $page 当前页
     * @param int $size 每页商品最大数量
     */
    public static function getProductsByCateogryId($categoryId, $paginate = true, $page = 1, $size = 30)
    {
        $query = self::where('category_id', '=', $categoryId);
        if (!$paginate) {
            return $query->select();
        } else {
            return $query->paginate($size, true, ['page' => $page]);
        }
    }

    public static function getProductDetail($id)
    {
        // $product = self::with(['imgs.imgUrl', 'properties'])->find($id);
        $product = self::with([
            'imgs' => function($query) {
                $query->with(['imgUrl'])->order('order', 'asc');
            }])
            ->with('properties')
            ->find($id); 

        return $product;
    }
}
