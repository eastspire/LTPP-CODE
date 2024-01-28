<?php

declare(strict_types=1);

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * 全局跨域请求处理
 * Class CrossDomain
 * @package app\middleware
 */

class CrossDomain implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 88888888');
        $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        $response->withHeaders([
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => $request->header('Origin', '*'),
            'Access-Control-Allow-Methods' => 'GET, POST, PATCH',
            'Access-Control-Allow-Headers' => 'Authorization,Requestid,Key,Content-Type,If-Match,If-Modified-Since,If-None-Match,If-Unmodified-Since,X-CSRF-TOKEN,X-Requested-With',
            'Access-Control-Max-Age' => '88888888'
        ]);
        return $response;
    }
}
