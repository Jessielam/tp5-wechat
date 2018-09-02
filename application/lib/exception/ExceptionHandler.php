<?php

namespace app\lib\exception;

use think\Request;
use think\Log;
use think\exception\Handle;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;

    public function render(\Exception $e)
    {
        // 如果是自定义的异常，则控制HTTP状态码，不需要记录日志
        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            // 如果是服务器未处理的异常，将http状态码设置为500，并且需要记录日志
            if (config('app_debug')) {
                return parent::render($e);
            }

            $this->code = 500;
            $this->msg = "sorry，we make a mistake. (^o^)Y";
            $this->errorCode = 999;

            // 记录日志
            $this->recordLog($e);
        }
        $request_url = Request::instance()->url();
        $result = [
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request_url
        ];

        return json($result, $this->code);
    }

    // 记录自定义日志
    private function recordLog(\Exception $e)
    {
        Log::init([
            'type' => 'file',
            'path' => LOG_PATH,
            'level' => ['error']
        ]);

        Log::error($e->getMessage());
    }
}