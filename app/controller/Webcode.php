<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Redis;

class Webcode
{
    /**
     * 代码运行
     */
    public function runCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!$my_aid) {
            return json(['code' => -1, 'result' => '请登录', 'usememory' => 0, 'usetime' => 0]);
        }
        if (!Base::judgeJudgeInstall()) {
            return json(['code' => -1, 'result' => '判题机未安装', 'usememory' => 0, 'usetime' => 0]);
        }
        //判题机限制
        //缓存存在
        $redis6 = Redis::connection('db6');

        if ($redis6->get('OjRunNum')) {
            $sumtime = 0;
            Base::$GLOBmaxjudge = Base::getSettingKeyData('GLOBmaxjudge');
            Base::$GLOBmaxwaittime = Base::getSettingKeyData('GLOBmaxwaittime');
            while ($redis6->get('OjRunNum') >= Base::$GLOBmaxjudge) {
                if ($sumtime > Base::$GLOBmaxwaittime) {
                    return json(['code' => 1, 'result' => '本次提交不记录' . "\n" . '当前提交人数较多，请稍后再次提交哦！', 'usememory' => 0, 'usetime' => 0]);
                }
                sleep(1);
                $sumtime++;
            }
        } else {
            $redis6->setNx('OjRunNum', 0);
        }



        $limittime = (int) Base::getSettingKeyData('idemaxtime');
        $limitmemory = ((int) Base::getSettingKeyData('idemaxmemory') ?? 0) << 20;

        $code = $request->post('code');
        $num = $request->post('num');
        $userlanguage = $request->post('userlanguage');
        //代码检测
        $check_safe_json = Base::judgeCodeSafe($code, $userlanguage);
        if (!isset($check_safe_json['code']) || $check_safe_json['code'] != 1) {
            return json($check_safe_json);
        }

        $md5aid = md5($my_aid);
        $mainfile = '';
        //用户文件夹
        $filepath = '';
        //代码存放路径
        do {
            $mainfile = uniqid() . mt_rand(1, 100000) . time();
            $filepath = Base::$sandbox_path . $md5aid . '/' . $mainfile . '/';
        } while (file_exists($filepath));

        if (!file_exists($filepath)) {
            Base::judgeCreatPath($filepath, 0777);
        }

        $runcodefilepath = $filepath . 'main';
        //输入文件
        $inpath = $runcodefilepath . '.in';
        Base::writeToFile($inpath, $num);
        //输出文件
        $outpath = $runcodefilepath . '.out';
        Base::writeToFile($outpath, '');
        //错误文件
        $errpath = $runcodefilepath . '.err';
        Base::writeToFile($errpath, '');

        $redis6->incr('OjRunNum');
        //编译
        $compiler_res_json = Base::compiler($userlanguage, $code, $filepath, $runcodefilepath);

        if (!isset($compiler_res_json['code']) || $compiler_res_json['code'] != 1) {
            $redis6->decr('OjRunNum');
            return json($compiler_res_json);
        }

        $out = $compiler_res_json['result'];
        if (!empty($out)) {
            Base::deleteAllFile($filepath);
            $code = $code . "\n\n\n报错详情：\n";
            $redis6->decr('OjRunNum');
            $res_data = '编译出错！' . "\n";
            $err_data = '';
            // 去除路径信息
            foreach ($out as &$tem) {
                $err_data .= $tem . "\n";
            }
            $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $md5aid . '/' . $mainfile . '/';
            $err_data = str_replace($tp, '', $err_data);
            $tp = $md5aid . '/' . $mainfile . '/';
            $err_data = str_replace($tp, '', $err_data);
            Base::removeBr($err_data);

            $code .= $err_data;
            $res_data .= $err_data;
            Base::insertToDb('codehistory', [
                'userid' => $my_aid,
                'status' => '编译出错',
                'time' => date('Y-m-d H:i:s', time()),
                'usetime' => 0,
                'usememory' => 0,
                'code' => $code,
                'language' => $userlanguage,
                'contestid' => 0,
                'problemid' => 0
            ]);
            return \json(['code' => -1, 'result' => $res_data, 'usememory' => 0, 'usetime' => 0]);
        }

        $out = [];

        //运行
        $out = Base::run($userlanguage, $filepath, $inpath, $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory);

        if (!$out || empty($out)) {
            $redis6->decr('OjRunNum');
            return \json(['code' => -1, 'result' => '判题机运行异常！', 'usememory' => 0, 'usetime' => 0]);
        }
        $out = $out[0];
        $run_resource_consumption = Base::getCodeTimeMemory($out);
        if (!$run_resource_consumption || !isset($run_resource_consumption['status'])) {
            $redis6->decr('OjRunNum');
            return \json(['code' => -1, 'result' => '判题机运行异常！', 'usememory' => 0, 'usetime' => 0]);
        }

        $status = $run_resource_consumption['status'] ?? 0;
        $time_used = $run_resource_consumption['time_used'] ?? 0;
        $memory_used = $run_resource_consumption['memory_used'] ?? 0;

        if ($status == Base::$judge_server_error) {
            $redis6->decr('OjRunNum');
            $msg = $run_resource_consumption['msg'];
            return \json(['code' => -1, 'result' => '判题机运行异常！' . "\n" . $msg, 'usememory' => 0, 'usetime' => 0]);
        }

        if ($status == Base::$judge_code_error) {
            $redis6->decr('OjRunNum');
            $code .= "\n\n\n报错详情：\n";
            $err_data = '运行出错' . "\n";
            //读取输出
            $resout = Base::getFileText($errpath);
            Base::deleteAllFile($filepath);
            // 去除路径信息
            $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $md5aid . '/' . $mainfile . '/';
            $resout = str_replace($tp, '', $resout);
            $tp = $md5aid . '/' . $mainfile . '/';
            $resout = str_replace($tp, '', $resout);
            Base::removeBr($resout);

            if (strlen($resout) > Base::$code_out_limit) {
                $resout = Base::utfsubstr($resout, 0, Base::$code_out_limit, true) . "\n" . '（仅显示前' . Base::$code_out_limit . '个字符）';
            }
            $code .= $resout . "\n";
            $err_data .= $resout;
            Base::insertToDb('codehistory', [
                'userid' => $my_aid,
                'status' => '运行出错',
                'time' => date('Y-m-d H:i:s', time()),
                'usetime' => $time_used,
                'usememory' => $memory_used,
                'code' => $code,
                'language' => $userlanguage,
                'contestid' => 0,
                'problemid' => 0
            ]);

            return \json([
                'code' => -1,
                'result' => $err_data,
                'usetime' => $time_used,
                'usememory' => $memory_used
            ]);
        }


        //读取输出
        $resout = Base::getFileText($outpath);
        // 去除路径信息
        $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $md5aid . '/' . $mainfile . '/';
        $resout = str_replace($tp, '', $resout);
        $tp = $md5aid . '/' . $mainfile . '/';
        $resout = str_replace($tp, '', $resout);
        Base::removeBr($resout);

        if (strlen($resout) > Base::$code_out_limit) {
            $resout = Base::utfsubstr($resout, 0, Base::$code_out_limit, true) . "\n" . '（仅显示前' . Base::$code_out_limit . '个字符）';
        }
        switch ($status) {
            case Base::$judge_code_tle:
                Base::deleteAllFile($filepath);
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => 'TLE',
                    'time' => date('Y-m-d H:i:s', time()),
                    'usetime' => $time_used,
                    'usememory' => $memory_used,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => 0,
                    'problemid' => 0
                ]);
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => 'TLE！' . '请更改代码后再次尝试哦！' . ($resout ? "\n" . $resout : ''), 'usetime' => $time_used, 'usememory' => $memory_used]);
            case Base::$judge_code_mle:
                Base::deleteAllFile($filepath);
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => 'MLE',
                    'time' => date('Y-m-d H:i:s', time()),
                    'usetime' => $time_used,
                    'usememory' => $memory_used,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => 0,
                    'problemid' => 0
                ]);
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => 'MLE！' . '请更改代码后再次尝试哦！' . ($resout ? "\n" . $resout : ''), 'usetime' => $time_used, 'usememory' => $memory_used]);
            case Base::$judge_code_re:
                Base::deleteAllFile($filepath);
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => 'RE',
                    'time' => date('Y-m-d H:i:s', time()),
                    'usetime' => $time_used,
                    'usememory' => $memory_used,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => 0,
                    'problemid' => 0
                ]);
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => 'RE！' . '请更改代码后再次尝试哦！' . ($resout ? "\n" . $resout : ''), 'usetime' => $time_used, 'usememory' => $memory_used]);
            default:
                break;
        }

        $redis6->decr('OjRunNum');

        Base::deleteAllFile($filepath);

        Base::insertToDb('codehistory', [
            'userid' => $my_aid,
            'status' => '正常运行',
            'time' => date('Y-m-d H:i:s', time()),
            'usetime' => $time_used,
            'usememory' => $memory_used,
            'code' => $code,
            'language' => $userlanguage,
            'contestid' => 0,
            'problemid' => 0
        ]);

        return json(['code' => 1, 'result' => $resout, 'usetime' => $time_used, 'usememory' => $memory_used,]);
    }
}