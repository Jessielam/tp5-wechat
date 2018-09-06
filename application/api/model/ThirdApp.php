<?php

namespace app\api\model;

class ThirdApp extends Base
{
    public static function check($ac, $se)
    {
        $app = self::where('app_id','=',$ac)
            ->where('app_secret', '=', md5($se))
            ->find();

        return $app;
    }
}