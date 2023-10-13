<?php

namespace app\controller;

use support\Request;

class CloudfileImage
{
    /**
     * 保存云盘里文章的图片
     * @param Request $request
     * @return string $res json
     */
    public function saveImage(Request $request)
    {
        $file = $request->file('image');
        $md5month = md5(date("Y-m", time()));
        $fileextion = $file->getUploadExtension();
        if ($fileextion != 'jpg' && $fileextion != 'png' && $fileextion != 'jpeg' && $fileextion != 'gif') {
            return json(['code' => -1, 'msg' => '图片格式不正确']);
        }
        // 大小限制
        if ($file->getSize() > Base::$image_size_limit) {
            return json(['url' => '图片大小不能大于' . Base::$image_size_limit / Base::$one_mb_size . 'MB']);
        }
        $newPath = Base::$LTPP_public_static_path . '/cloudfileimage/' . $md5month; // 目标文件夹
        Base::judgeCreatPath($newPath);
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            return \json(['url' => Base::$GLOBlinuxurl . '/static/cloudfileimage/' . $md5month . '/' . $newName]);
        }
        return \json(['url' => "error"]);
    }

    /**
     * 删除文章里的图片
     * @param Request $request
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