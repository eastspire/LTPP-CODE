<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-03-27 09:25:02
 * @FilePath: \LTPP\app\controller\Testupload.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Testupload extends Oj
{
    /**
     * 保存测试用例
     * @param Request $request 请求
     * @return string $res json
     */
    public function saveTest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->first();
        if (!$user) {
            return \json(['code' => -1, 'msg' => '用户不存在！']);
        }

        $file = $request->file('file');
        $problem_uid = \request()->post('id');
        $problem_id = Base::getIdByUid($problem_uid);
        $ismy = Oj::judgeIsMyProblem($problem_id, $my_aid);
        if (!$ismy) {
            return \json(['code' => -1, 'msg' => '权限不足！']);
        }
        $md5_problem_id = Base::doubleMd5($problem_id);
        $newPath = '/home/LTPP/testdata/' . $md5_problem_id . '/'; // 目标文件夹

        $out = '';
        if ($file->getUploadExtension() === 'zip') {
            //先清空
            Base::deleteAllFile($newPath);
            //判断是否新建文件夹
            if (!file_exists($newPath)) {
                @mkdir($newPath, 0666, true);
            } else {
                Base::deleteAllFile("$newPath" . '/*'); //'/*'删除文件夹全部文件此保留文件夹
            }
            exec('unzip -d ' . $newPath . ' ' . $file->getRealPath() . ' 2>&1', $out);
        } else {
            //删除上传的临时文件
            Base::deleteAllFile($file->getRealPath());
            return \json(['code' => -1, 'msg' => '文件类型错误！']);
        }
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return \json(['code' => 1, 'msg' => '测试样例上传成功！']);
    }
}
;