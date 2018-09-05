<?php

namespace app\lib\exception;

class CategoryException extends BaseException
{
    public $code = 404;
    public $msg = "目前还没有分类，请先添加";
    public $errorCode = 50000;
}