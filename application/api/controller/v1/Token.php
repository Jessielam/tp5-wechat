<?php

namespace app\api\controller\v1;

use app\api\validate\TokenGet;
use app\api\service\UserToken;
use app\api\controller\BaseController;
use app\lib\exception\ParamterException;
use app\api\service\Token as TokenService;
use app\api\service\AppToken as AppTokenService;
use app\api\validate\AppTokenGet;

/**
 * 获取令牌，相当于登录
 */
class Token extends BaseController
{
    /**
     * 用户获取令牌（登陆）
     * @url /token
     * @POST code
     * @note 虽然查询应该使用get，但为了稍微增强安全性，所以使用POST
     */
    public function getToken($code = '')
    {
        (new TokenGet())->goCheck();
        $wx = new UserToken($code);
        $token = $wx->get();

        return ['token'=> $token];
    }


    /**
     * 验证用户是否有效
     */
    public function verifyToken($token='')
    {
        if(!$token){
            throw new ParameterException([
                'token不允许为空'
            ]);
        }
        $valid = TokenService::verifyToken($token);
        return [
            'isValid' => $valid
        ];
    }

    /**
     * 第三方应用获取令牌
     * @url /app_token?
     * @method POST
     */
    public function getAppToken($ac='', $se='')
    {
        (new AppTokenGet())->goCheck();
        $app = new AppTokenService();
        $token = $app->get($ac, $se);
        return [
            'token' => $token
        ];
    }
}
