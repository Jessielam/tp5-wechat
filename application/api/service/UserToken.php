<?php

namespace app\api\service;

use think\Exception;
use app\api\execption\WeChatException;
use app\api\model\User;

class UserToken extends Token
{
    protected $code;
    protected $appId;
    protected $appSecret;
    protected $loginUrl;

    public function __construct(string $code)
    {
        $this->code = $code;
        $this->appId = config('wx.app_id');
        $this->appSecret = config('wx.app_secret');
        $this->loginUrl = sprintf(config('wx.login_url'), $this->appId, $this->appSecret, $this->code);
    }

    public function get()
    {
        $result = curl_get($this->loginUrl);    
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        } else {
            // 如果返回的结果中如果有errcode，则是登录失败
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {
                $this->processLoginError($wxResult);
            } else {
                return $this->grantToken($wxResult);
            }
        }
    }

    /**
     * 处理错误
     */
    private function processLoginError($wxResult)
    {
        throw new WeChatException([
            'msg' => $wxResult['errmsg'],
            'errorCode' => $wxResult['errcode']
        ]);
    }

    /**
     * 生成令牌
     */
    private function grantToken($wxResult)
    {
        // 获取openid
        $openId = $wxResult['openid'];
        // 在数据库中查询该openid是否存在
        $user = User::getUserByOpenId($openId);
        // 如果存在则不处理，如果不存则生成
        if (!$user) {
            // 借助微信的openid作为用户标识 唯一
            // 如果记录不存在，则生成一个
            $uid = $this->newUser($openId);
        } else {
            $uid = $user->id;
        }
        // 生成令牌 存入缓存
        // 目的： 用户可以根据自己的令牌找到一系列的变量包括 权限，加快访问速度
        $cacheValue = $this->prepareCacheValue($wxResult, $uid);
        $token = $this->saveToCache($cacheValue);

        return $token;
    }

    /**
     * 准备缓存数据
     */
    private function prepareCacheValue($wxResult, $uid)
    {
        $cacheValue = $wxResult;
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = 16;  // 权限

        return $cacheValue;
    }

    private function newUser($openId)
    {
        $user = User::create([
            'openid' => $openId
        ]);

        return $user->id;
    }

    private function saveToCache($cacheValue)
    {
        $key = self::generateToken();
        $value = json_encode($cacheValue); 
        $expire_in = config('wx.token_expire_in'); //token 过期时间
        // 写入缓存
        $result = cache($key, $value, $expire_in); // 可以在配置文件设置缓存在哪里
        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }

        return $key;
    }
}