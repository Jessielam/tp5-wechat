<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

// NOTE: id 是客户端传过来的任何东西都是不可信的，需要验证规则 -- 参数校验
Route::get('api/:version/banner/:id', 'api/:version.Banner/getBanner');

Route::group('api/:version/theme', function(){
    // url: /v1/theme?ids=1
    Route::get('', 'api/:version.Theme/getSimpleList');
    // 获取某一主题下的商品
    Route::get('/:id', 'api/:version.Theme/getComplexOne');
});

// 商品api
Route::group('api/:version/product', function(){
    Route::get('/recent', 'api/:version.Product/getRecent');
    Route::get('/by_category/paginate', 'api/:version.Product/getPageProductsByCategoryId');
    Route::get('/by_category', 'api/:version.Product/getAllProductsByCategoryId');
    Route::get('/:id', 'api/:version.Product/getProductById', [], ['id' => '\d+']); // 商品详情
});

Route::get('api/:version/category/all', 'api/:version.Category/getAllCategories');

Route::post('api/:version/token/user', 'api/:version.Token/getToken');

// 用户地址
Route::post('api/:version/address', 'api/:version.Address/createOrUpdateAddress');

// 订单
Route::post('api/:version/order', 'api/:version.Order/placeOrder');
Route::post('api/:version/order/by_user', 'api/:version.Order/getOrderListByUser');
Route::get('api/:version/order/:id', 'api/:version.Order/getOrderDetail', [], ['id' => '\d+']);

// 支付
Route::post('api/:version/pay/pre_order', 'api/:version.Pay/getPreOrder');
Route::post('api/:version/pay/notify', 'api/:version.Pay/payNotify');