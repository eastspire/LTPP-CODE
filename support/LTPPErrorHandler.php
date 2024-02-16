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
            Base::sendErrorNotice(nl2br((string)$exception), json_encode($exception->getMessage(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            parent::report($exception);
        } catch (Throwable $e) {
            Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), $e->getMessage());
        }
    }

    public function render(Request $request, Throwable $exception): Response
    {
        try {
            if (($exception instanceof BusinessException) && ($response = $exception->render($request))) {
                return $response;
            }
            $err_code = $exception->getCode();
            if ($request->expectsJson()) {
                $json = [
                    'code' => -1,
                    'err_code' => $err_code,
                    'msg' => $this->debug ? $exception->getMessage() : Base::$server_error_msg,
                    'data' => [],
                ];
                $this->debug && $json['traces'] = (string)$exception;
                return new Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            }
            $error = $this->debug ? nl2br((string)$exception) : Base::notFoundPage();
            return new Response(500, [], $error);
        } catch (Throwable $e) {
            Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), $e->getMessage());
        }
        return Base::notFoundPage();
    }
}
