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
// url: /v1/theme?ids=1
Route::get('api/:version/theme', 'api/:version.Theme/getSimpleList');

// 获取某一主题下的商品
Route::get('api/:version/theme/:id', 'api/:version.Theme/getComplexOne');
