<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Request;
use app\api\validate\{IDCollection, IDMustBePostiveInt};
use app\api\model\Theme as ThemeModel;
use app\lib\exception\ThemeException;

class Theme extends Controller
{
    /**
     * 获取精选主题简单列表
     * @url /theme?ids=id1[,id2...]
     * @param string $ids
     */
    public function getSimpleList($ids = '') 
    {
        (new IDCollection())->goCheck();
        $ids = explode(',', $ids);
        $result = (new ThemeModel())->getThemsListsByIds($ids);
        if (!$result) {
            throw new ThemeException();
        }
        return $result;
    }

    /**
     * 获取某一主题下的商品数据
     * @url /thmem/:id
     * @param int $id
     */
    public function getComplexOne(int $id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $theme = ThemeModel::getThemeWithProducts($id);
        if (!$theme) {
            throw new ThemeException();
        }
        return $theme;
    }
}
