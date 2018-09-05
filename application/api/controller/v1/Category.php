<?php

namespace app\api\controller\v1;

use think\Request;
use app\api\model\Category as CategoryModel;
use app\api\controller\BaseController;

class Category extends BaseController
{
    // 获取所有的分类信息
    public function getAllCategories()
    {
        $categories = CategoryModel::getAllCategories();

        if ($categories->isEmpty()) {
            throw new CategoryException();
        }

        return $categories->toArray();
    }
}
