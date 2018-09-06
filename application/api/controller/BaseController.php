<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\api\service\Token;

class BaseController extends Controller
{
    protected function checkPrimaryScope()
    {
        Token::needPrimaryScope();
    }

    // 用户权限
    protected function checkExclusiveScope()
    {
        Token::needExclusiveScope();
    }

    // 管理员权限
    protected function checkSuperScope()
    {
        Token::needSuperScope();
    }
}
