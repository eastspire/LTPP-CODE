<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;

class Photo
{
    /**
     * 删除全部首页图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteAll(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot)
            return json(['code' => -1, 'msg' => '权限不足']);
        $path = Base::$LTPP_public_static_path . '/homephoto/';
        Base::deleteAllFile($path);
        return json(['code' => 1, 'msg' => '首页侧边栏图片清空完成！']);
    }

    /**
     * 添加首页图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function addPhoto(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $file = $request->file('file');

        $newPath = Base::$LTPP_public_static_path . '/homephoto'; // 目标文件夹

        Base::judgeCreatPath($newPath);
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            return \json(['url' => Base::$GLOBlinuxurl . '/static/homephoto/' . $newName]);
        }
    }

    /**
     * 添加群聊头像图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function addGroupPhoto(Request $request)
    {
        $file = $request->file('file');
        $newPath = Base::$LTPP_public_static_path . '/groupphoto'; // 目标文件夹

        Base::judgeCreatPath($newPath);
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            return \json(['url' => Base::$GLOBlinuxurl . '/static/groupphoto/' . $newName]);
        }
    }

    /**
     * 删除首页图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function deletePhoto(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $name = $request->post('name');
        $path = Base::$LTPP_public_static_path . '/homephoto/' . $name;
        if (unlink($path)) {
            return json(['code' => 1, 'msg' => '图片删除成功']);
        } else {
            return json(['code' => -1, 'msg' => '图片删除失败']);
        }
    }

    /**
     * 加载首页图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadPhoto(Request $request)
    {
        $path = Base::$LTPP_public_static_path . '/homephoto/';
        $photo = $this->getfiles($path, '#\.(jpe?g|png)$#', 0);
        $allnum = sizeof($photo);
        return json(['code' => 1, 'data' => $photo, 'allnum' => $allnum, 'msg' => '成功获取到 ' . $allnum . ' 张图片']);
    }

    /**
     * 递归获取所有文件
     *
     * @param string $path             文件路径
     * @param string $allowFiles     匹配文件名的正则表达式 (默认为空 全部文件)
     * @param number $depth         递归深度， 默认 1
     * @return array                  所有文件
     **/
    function getfiles($path, $allowFiles = '', $depth = 1, $substart = 0, &$files = array())
    {
        $depth--;
        $path = realpath($path) . '/';
        $substart = $substart ? $substart : strlen($path);
        if (!is_dir($path)) {
            return [];
        }
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $path2 = $path . $file;
                    if (is_dir($path2) && $depth > 0) {
                        $this->getfiles($path2, $allowFiles, $depth, $substart, $files);
                    } elseif (empty($allowFiles) || preg_match($allowFiles, $file)) {
                        $files[] = substr($path2, $substart);
                    }
                }
            }
        }
        sort($files);
        return $files;
    }
}