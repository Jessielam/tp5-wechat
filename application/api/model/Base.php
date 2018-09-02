<?php

namespace app\api\model;

use think\Model;

// 模型基类
class Base extends Model
{
    protected function prefixImgUrl(string $value = '',array $data) : string
    {
        if ($value) {
            $finalUrl = $value;
            if (isset($data['from']) && $data['from'] == 1) {
                $finalUrl = config('setting.img_prefix') . $value;
            }
        } 
        return $finalUrl ?? '';
    }
}
