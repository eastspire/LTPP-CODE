<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 12:20:24
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-21 14:54:09
 * @FilePath: \LTPP-CODE\app\controller\Chatfile.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;

class Chatfile
{
    /**
     * 文件上传
     * @param Request $request 请求
     * @return string $res json
     */
    public function chatUpFile(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$my_aid || $user_id == '') {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $file = $request->file('file');
        $text = Base::uploadChatFileToDb($my_aid, $user_id, $file);
        return json(['code' => 1, 'msg' => '上传成功', 'filename' => $text]);
    }

    /**
     * 加载文件夹和文件
     * @param Request $request 请求
     * @param bool $is_private = true 是否是私聊
     * @return string $res json
     */
    public function loadList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$my_aid || $user_id == '') {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $chat_path_list = Base::loadChatFileList($my_aid, $user_id);
        $res = [];
        foreach ($chat_path_list as &$one_data) {
            $temarray = [];
            $temarray[] = Base::Base64Encode($one_data->name);
            $path = Base::Base64Encode($one_data->path);
            $file_extion = Base::getDbFileExtion($one_data->path);
            $temarray[] = Base::fileExtionToNumberType($file_extion);
            $size = $one_data->size;
            Base::getChineseSize($size);
            $temarray[] = $size;
            $temarray[] = $one_data->time;
            $temarray[] = $path;
            $res[] = $temarray;
        }
        if (empty($res)) {
            return json(['code' => -1, 'msg' => '文件为空']);
        }
        return json(['code' => 1, 'msg' => '文件获取成功', 'data' => $res]);
    }

    /**
     * 文件下载
     * @param Request $request 请求
     * @return string $res json
     */
    public function downloadFile(Request $request)
    {
        $path = $request->post('path');
        $path = Base::Base64Decode($path);
        if (!$path) {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $file_data = Base::getStaticFileData($path);
        $file_extion = Base::getDbFileExtion($path);
        return Response($file_data, 200, [
            'Content-Type' => Base::getContentType($file_extion),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($file_data),
            'File-Path' => $path,
            'File-Extion' => $file_extion,
            'File-Content-Type' => Base::getContentType($file_extion),
        ]);
    }
};
