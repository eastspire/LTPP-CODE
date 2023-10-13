<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 18:43:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-05 21:18:46
 * @FilePath: \LTPP-CODE\app\middleware\StaticFile.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace app\middleware;

use app\controller\Base;
use Exception;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

/**
 * Class StaticFile
 * @package app\middleware
 */
class StaticFile implements MiddlewareInterface
{

    public function process(Request $request, callable $next): Response
    {
        try {
            $path = $request->path();
            // 禁止访问.开头的隐藏文件
            if (strpos($path, '/.') !== false || strpos($path, '../') !== false) {
                return response(Base::notFoundPage(), 404);
            }
            // 匹配到访问静态资源
            if (strpos($path, '/static/') !== false) {
                $str = '';
                $len = strlen($path);
                for ($i = 1; $i < $len; ++$i) {
                    $str .= $path[$i];
                }
                $path = $str;
                $path = Base::$LTPP_public_path . $path;
                // 文件不存在
                if (!file_exists($path)) {
                    return response(Base::notFoundPage(), 404);
                }
                // 目录
                if (is_dir($path)) {
                    return response(Base::notFoundPage(), 404);
                }

                $iscode = false;
                $language = '';
                foreach (Base::$map_language_file as $key => $val) {
                    $iscode = str_ends_with($path, $key);
                    if ($iscode) {
                        if ($key == 'html' && (strpos($path, '/static/contest/') !== false || strpos($path, '/static/contestcode/') !== false)) {
                            // 代码查重中的HTML文件
                            $iscode = false;
                            $language = '';
                            break;
                        }
                        $language = $val;
                        break;
                    }
                }
                $txt = file_get_contents($path);
                // 代码文件
                if ($iscode) {
                    return response(Base::codeToHTML($txt, $language), 200);
                }
                // md文件
                if (str_ends_with($path, 'md')) {
                    return response(Base::markdownToHTML($txt), 200);
                }
                $txt = null;
            }
            $response = $next($request);
            return $response;
        } catch (Exception $e) {
            return response(Base::notFoundPage(), 404);
        }
    }
}