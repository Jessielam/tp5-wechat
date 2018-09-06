<?php

namespace app\api\service;

use think\Exception;

class AccessToken 
{
    private $tokenUrl;
    const TOKEN_CACHE_KEY = 'access';
    const CACHE_LIFETIME = 7000; // 设置值比微信值稍微小一点
    public function __construct()
    {
        $url = config('wx.access_token_url');
        $url = sprintf($url, config('wx.app_id'), config('wx.app_secret'));
        $this->tokenUrl = $url;
    }

    public function get()
    {
        $token = $this->getFromCache();
        if (!$token) {
            return $this->getFromServer();
        } 

        return $token['access_token'];
    }

    private function getFromCache()
    {
        $token = cache(self::TOKEN_CACHE_KEY);
        if ($token) {
            return $token;
        }
        return null;
    }

    private function getFromServer()
    {
        $result = curl_get($this->tokenUrl);
        $token = json_decode($result, true);

        if (!$token) {
            throw new Exception('获取AccesToken 异常'); 
        }
        if (!empty($token['errorcode'])) {
            throw new Exception($token['errmsg']);
        }
        
        $this->saveToCache($token);
        return $token;
    }

    private function saveToCache()
    {
        return cache(self::TOKEN_CACHE_KEY, $token, self::CACHE_LIFETIME);
    }
}   