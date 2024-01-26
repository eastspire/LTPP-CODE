<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

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
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $list_path = Db::table('home_photo')
            ->where('isdel', 0)
            ->pluck('path')
            ->toArray();
        $redis35 = Redis::connection('db35');
        $redis35->del($list_path);
        foreach ($list_path as &$path) {
            Db::table('file_path')
                ->where('path', $path)
                ->where('isdel', 0)
                ->update([
                    'isdel' => 1
                ]);
        }
        Db::table('home_photo')
            ->where('isdel', 0)
            ->update([
                'isdel' => 1
            ]);
        return json(['code' => 1, 'msg' => '首页图片清空完成！']);
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
        $file_extion = $file->getUploadExtension();
        Base::uploadFileToDb('home_photo', $my_aid, $file, $file_extion);
        return json(['code' => 1, 'msg' => '上传成功']);
    }

    /**
     * 添加群聊头像图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function addGroupPhoto(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $file = $request->file('image');
        $file_extion = $file->getUploadExtension();
        if ($file_extion != 'jpg' && $file_extion != 'png' && $file_extion != 'jpeg' && $file_extion != 'gif') {
            return json(['code' => -1, 'msg' => '图片格式不正确', 'url' => '']);
        }
        // 大小限制
        if ($file->getSize() > Base::$image_size_limit) {
            return json(['code' => -1, 'msg' => '图片大小不能大于' . Base::$image_size_limit / Base::$one_mb_size . 'MB', 'url' => '']);
        }
        $url = Base::uploadFileToDb('file_path', $my_aid, $file, $file_extion);
        return \json(['url' => $url]);
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
        $path = $request->post('path');
        Db::table('file_path')
            ->where('path', $path)
            ->where('isdel', 0)
            ->update([
                'isdel' => 1
            ]);
        Db::table('home_photo')
            ->where('path', $path)
            ->where('isdel', 0)
            ->update([
                'isdel' => 1
            ]);
        $redis35 = Redis::connection('db35');
        $redis35->del($path);
        return json(['code' => 1, 'msg' => '图片删除成功']);
    }

    /**
     * 加载首页图片
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadPhoto(Request $request)
    {
        $photo = Db::table('home_photo')
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->pluck('path')
            ->toArray();
        $allnum = sizeof($photo);
        return json(['code' => 1, 'data' => $photo, 'allnum' => $allnum, 'msg' => '成功获取到 ' . $allnum . ' 张图片']);
    }
};
