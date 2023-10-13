<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-17 16:10:24
 * @FilePath: \LTPP-CODE\app\controller\AnswerImage.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;

class AnswerImage
{
    /**
     * 保存评论里的图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function saveImage(Request $request)
    {
        $file = $request->file('image');
        $fileextion = $file->getUploadExtension();
        if ($fileextion != 'jpg' && $fileextion != 'png' && $fileextion != 'jpeg' && $fileextion != 'gif') {
            return json(['code' => -1, 'msg' => '图片格式不正确']);
        }
        // 大小限制
        if ($file->getSize() > Base::$image_size_limit) {
            return json(['url' => '图片大小不能大于' . Base::$image_size_limit / Base::$one_mb_size . 'MB']);
        }
        $md5month = md5(date("Y-m", time()));
        $md5answer_id = md5($request->header('id'));
        $newPath = Base::$LTPP_public_static_path . '/answerimage/' . $md5month . '/' . $md5answer_id; // 目标文件夹
        Base::judgeCreatPath($newPath);
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            return \json(['url' => Base::$GLOBlinuxurl . '/static/answerimage/' . $md5month . '/' . $md5answer_id . '/' . $newName]);
        }
        return \json(['url' => "error"]);
    }

    /**
     * 删除评论里的图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteImage(Request $request)
    {
        $path = $request->post('path');
        $len = strlen($path);
        $name = '';
        for ($i = 1; $i < $len - 6; ++$i) {
            if (
                $path[$i - 1] == '/' &&
                $path[$i] == 's' &&
                $path[$i + 1] == 't' &&
                $path[$i + 2] == 'a' &&
                $path[$i + 3] == 't' &&
                $path[$i + 4] == 'i' &&
                $path[$i + 5] == 'c' &&
                $path[$i + 6] == '/'
            ) {
                for ($j = $i; $j < $len; ++$j) {
                    $name = $name . $path[$j];
                }
                break;
            }
        }

        if ($name == '') {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }
        if (strripos($name, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $path = '/home/LTPP/public/' . $name;

        if (!file_exists($path)) {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }
        unlink($path);
        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
;