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
            return response(Base::notFoundPage(), 404, [
                'File-Path' => $path,
                'File-Extion' => $file_extion,
                'File-Content-Type' => Base::getContentType($file_extion),
            ]);
        }
        if ($iscode && $language) {
            return response(Base::codeToHTML($file_data, $language), 200, [
                'File-Path' => $path,
                'File-Extion' => $file_extion,
                'File-Content-Type' => Base::getContentType($file_extion),
            ]);
        }
        // md文件
        if ($file_extion == 'md') {
            return response(Base::markdownToHTML($file_data), 200, [
                'File-Path' => $path,
                'File-Extion' => $file_extion,
                'File-Content-Type' => Base::getContentType($file_extion),
            ]);
        }
        return Response($file_data, 200, [
            'Content-Type' => Base::getContentType($file_extion),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($file_data),
            'File-Path' => $path,
            'File-Extion' => $file_extion,
            'File-Content-Type' => Base::getContentType($file_extion),
        ]);
    } catch (Exception $e) {
        Robot::sendChatToOneUserMsg(Base::getRootId(), '文件服务 **【StaticFile】** 运行错误：' . $e->getMessage());
        return response(Base::notFoundPage(), 404);
    }
});
