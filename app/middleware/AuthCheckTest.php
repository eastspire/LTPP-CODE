<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use app\controller\Base;

class AuthCheckTest implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        return Base::judgeAuthCheckTestSafe($request, $handler);
    }
}
