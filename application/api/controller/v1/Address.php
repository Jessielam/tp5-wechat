<?php

namespace app\api\controller\v1;

use think\Request;
use app\api\validate\{AddressNew, SuccessMessage};
use app\api\service\Token as TokenService;
use app\api\controller\BaseController;

class Address extends BaseController
{
    // 权限验证
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress']  
    ];

    public function createOrUpdateAddress()
    {
        $validate = (new AddressNew())->goCheck();
        // 根据token查找用户的uid
        $uid = TokenService::getCurrentUid();
        // 根据uid获取用户信息，判断用户是否存在，如果不存在则抛出异常
        $user = User::get($uid); // model ==> get  all
        if ($user) {
            throw new UserException();
        } 
        $userAddress = $user->address; // 关联获取地址信息

        // 获取提交的地址信息, 必须是通过验证的数据
        $postData = $validate->getDataByRule(input('post.'));

        if (!$userAddress) {
            // 如果该用户还没地址，则新建
            $user->address()->save($postData);
        } else {
            // 如果该用户已经拥有地址，则更新
            $user->address->save($postData);
        }

        return json(new SuccessMessage(), 201);
    }
}
