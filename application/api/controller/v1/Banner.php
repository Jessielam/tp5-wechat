<?php

namespace app\api\controller\v1;

use app\api\validate\IDMustBePostiveInt;
use app\api\model\Banner as BannerModel;
use app\lib\exception\MissException;
use app\api\controller\BaseController;

class Banner extends BaseController
{
    /**
     * 获取指定id的banner信息
     * @url /banner/:id
     * @param $id banner id
     * @method GET
     * @return array of by itms, code 200
     * @throws MissException
     */
    public function getBanner($id)
    {
        // 利用验证器验证参数
        (new IDMustBePostiveInt())->goCheck();
        $banner = BannerModel::getBannerById($id);
        if (!$banner) {
            throw new MissException([
                    'msg' => '请求banner不存在',
                    'errorCode' => 40000
                ]);
        }
        return $banner;
    }
}