<?php

namespace app\api\service;

use think\Exception;
use think\Cache;
use think\Request;
use app\lib\enum\ScopeEnum;
use app\lib\exception\{TokenException, ForbiddenException};

class Token
{
    public static function generateToken()
    {
        // 32个字符组成一组随机字符串
        $randChars = getRandChars(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $tokenSalt = config('wx.token_salt');
        
        return md5($randChars.$timestamp.$tokenSalt);
    }

    // 获取缓存某个值通用的方法
    public static function getCurrentTokenVal($field)
    {
        // 从http的header头获取
        $token = Request::instance()->header('token');
        // 从缓存读取对应的值
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            // 默认 存进去的是json格式的字符串形式
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($field, $vars)) {
                return $vars[$field];
            }
            throw new Exception('尝试获取的token变量不存在');
        }
    }

    public static function getCurrentUid()
    {
        // 根据令牌
        $uid = self::getCurrentTokenVal('uid');
        return $uid;
    }

    // NOTE: 用户和管理员都可以访问的权限
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenVal('scope');
        if ($scope) {
            if ($scope >= ScopeEnum::USER ) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // NOTE:: 只有用户可以访问的权限
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenVal('scope');
        if ($scope) {
            if ($scope == ScopeEnum::USER ) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // NOTE： 只有管理员可以访问的权限
    public static function needSuperScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope){
            if ($scope == ScopeEnum::SUPER) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // 检测当前的操作是否合法
    public static function isValidateOperate($currentUid)
    {
        if (!$currentUid) {
            throw new Exception('检查UID时必须传入一个被检查的UID');
        }

        $currentOperateUid = self::getCurrentUid();
        if ($currentOperateUid == $currentUid) {
            return true;
        }
        
        return false;
    }
}
