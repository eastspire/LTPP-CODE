<?php

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

use Webman\Route;
use app\controller\Base;
use app\controller\Robot;
use support\Request;

Route::any(Base::$LTPP_public_static_path . '[/{path:.+}]', function (Request $request) {
    $path = '';
    $file_extion = '';
    try {
        $path = $request->path();
        // 匹配到访问静态资源
        $file_extion = Base::getDbFileExtion($path);
        $iscode = false;
        $language = '';
        foreach (Base::$map_language_file as $key => $val) {
            $iscode = ($key == $file_extion);
            if ($iscode) {
                if ($key == 'html') {
                    $iscode = false;
                    $language = '';
                    break;
                }
                $language = $val;
                break;
            }
        }
        // 文件
        $file_data = Base::getStaticFileData($path);
        if ($file_data === false) {
            return Base::notFoundPage($path, $file_extion);
        }
        if ($iscode && $language) {
            return Base::codeToHTML($file_data, $language, $path, $file_extion);
        }
        // md文件
        if ($file_extion == 'md') {
            return Base::markdownToHTML($file_data, $path, $file_extion);
        }
        $is_open_gzip = Base::judgeIsOpenGzip($file_extion);
        $is_use_cache_control = Base::judgeIsOpenCacheControl($file_extion);
        $response_header = [
            'Content-Type' => Base::getContentType($file_extion),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($file_data),
            'File-Path' => $path,
            'File-Extion' => $file_extion,
            'File-Content-Type' => Base::getContentType($file_extion)
        ];
        if ($is_open_gzip) {
            $response_header['Content-Encoding'] = 'gzip';
            $file_data = gzencode($file_data, Base::$gzip_num);
        }
        if ($is_use_cache_control) {
            $response_header['Cache-Control'] = 'public,max-age=88888888';
        }
        return Response($file_data, 200, $response_header);
    } catch (Exception $e) {
        Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), $e->getMessage());
        return Base::notFoundPage($path, $file_extion);
    }
});
