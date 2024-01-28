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

use Illuminate\Support\Facades\Redis;
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
        $file = $request->file('file');
        $problem_uid = \request()->post('id');
        $problem_id = Base::getIdByUid($problem_uid);
        $ismy = Oj::judgeIsMyProblem($problem_id, $my_aid);
        if (!$ismy) {
            return \json(['code' => -1, 'msg' => '权限不足！']);
        }
        $md5_problem_id = Base::doubleMd5($problem_id);
        $alltestpath = Base::$tmp_path . 'testdata/' . $md5_problem_id . '/'; // 目标文件夹
        Base::deleteAllFile($alltestpath);
        Base::creatFilePath($alltestpath);
        $out = '';
        if ($file->getUploadExtension() === 'zip') {
            exec('unzip -d ' . $alltestpath . ' ' . $file->getRealPath() . ' 2>&1', $out);
            //获取所有输入输出样例文件名称
            $testfilein = glob($alltestpath . '*.in');
            if (sizeof($testfilein) == 0) {
                return \json(['code' => -1, 'msg' => '样例压缩包不能为空！']);
            }
            Db::table('oj_test_data')
                ->where('problem_id', $problem_id)
                ->where('isdel', 0)
                ->update([
                    'isdel' => 1
                ]);
            foreach ($testfilein as &$temin) {
                $path_parts = pathinfo($temin);
                //文件全名
                $fullname = $path_parts['basename'];
                //文件前缀名
                $testname = pathinfo($fullname, PATHINFO_FILENAME);
                $in = '';
                $out = '';
                if (file_exists($alltestpath . $testname . '.in')) {
                    $in = Base::getFileText($alltestpath . $testname . '.in');
                }
                if (file_exists($alltestpath . $testname . '.out')) {
                    $out = Base::getFileText($alltestpath . $testname . '.out');
                } else if (file_exists($alltestpath . $testname . '.ans')) {
                    // 兼容ICPC样例
                    $out = Base::getFileText($alltestpath . $testname . '.ans');
                }
                Base::insertToDb('oj_test_data', [
                    'problem_id' => $problem_id,
                    'test_in' => $in,
                    'test_out' => $out,
                ]);
            }
            // 删除解压的文件
            Base::deleteAllFile($alltestpath);
            // 重新写入输入样例
            Base::writeOjDataInToFile($problem_id, $alltestpath);
            // 更新样例缓存
            Base::updateOjTestDataListRedis($problem_id);
        } else {
            //删除上传的临时文件
            Base::deleteAllFile($file->getRealPath());
            return \json(['code' => -1, 'msg' => '文件类型错误！']);
        }
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return \json(['code' => 1, 'msg' => '测试样例上传成功！']);
    }
};
