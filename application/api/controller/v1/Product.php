<?php

namespace app\api\controller\v1;

use think\Request;
use app\api\validate\{Count as CountValiator,IDMustBePostiveInt, PagingParameter}; 
use app\api\model\Product as ProductModel;
use app\lib\exception\ProductException;
use app\api\controller\BaseController;

class Product extends BaseController
{
    /**
     * 获取最新的商品
     * @url api/v1/product/recent[?count=1]
     * @param int $count  默认显示15条记录
     */
    public function getRecent($count = 15)
    {
        (new CountValiator())->goCheck();
        $products = ProductModel::getMostRecent($count);
        if ($products->isEmpty()) {
            throw new ProuductException();
        }
        $products = $products->hidden(['summary'])->toArray();
        return $products;
    }

    /**
     * 获取分类下所有的商品数据（分页）
     * @url api/:version/product/by_category/paginate
     * @param int $id 分类id
     * @param int $page 页数
     * @param int $size 每页最大的商品数量
     * @return \think\Paginator
     */
    public function getPageProductsByCategoryId($id = 0, $page = 1, $size = 30)
    {
        (new IDMustBePostiveInt())->goCheck();
        (new PagingParameter())->goCheck();
        $pagingProducts =  ProductModel::getProductsByCateogryId($id, true, $page, $size);
        if ($pagingProducts->isEmpty()) {
            return [
                'current_page' => $pagingProducts->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingProducts->hidden(['summary'])->toArray();
        return [
            'current_page' => $pagingProducts->currentPage(),
            'data' => $data
        ];    
    }

    /**
     * 获取某分类下全部商品(不分页）
     * @url /product/all?id=:category_id
     * @param int $id 分类id号
     * @return \think\Paginator
     * @throws ThemeException
     */
    public function getAllProductsByCategoryId($id = 0)
    {            
        (new IDMustBePostiveInt())->goCheck();
        $products = ProductModel::getProductsByCateogryId($id, false);

        if ($products->isEmpty())
        {
            throw new ProductException();
        }
        
        return $products->hidden(['summary'])->toArray();
    }
    
    /**
     * 根据商品id获取商品详情信息
     */
    public function getProductById($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $product = ProductModel::getProductDetail($id);
        
        if (!$product) {
            throw new ProductException();
        }

        return $product->toArray();
    }

}
