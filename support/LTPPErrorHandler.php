<?php

namespace support;

use Throwable;
use Webman\Exception\ExceptionHandler;
use Webman\Http\Request;
use Webman\Http\Response;
use app\controller\Base;
use support\exception\BusinessException;

/**
 * Class Handler
 * @package support\exception
 */
class LTPPErrorHandler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    public function report(Throwable $exception)
    {
        try {
            // 通知
            Base::sendErrorNotice($exception->getTraceAsString(), '系统未捕获的异常：' . $exception->getMessage());
        } catch (Throwable $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '系统未捕获的异常：' . $e->getMessage());
        }
    }

    public function render(Request $request, Throwable $exception): Response
    {
        try {
            if (($exception instanceof BusinessException) && ($response = $exception->render($request))) {
                return $response;
            }
            $err_code = $exception->getCode();
            $json = [
                'code' => -1,
                'err_code' => $err_code,
                'msg' => $this->debug ? '系统未捕获的异常：' . $exception->getMessage() . "\n" . (string)$exception : Base::$server_error_msg,
                'data' => [],
            ];
            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        } catch (Throwable $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return Base::notFoundPage();
    }
}
