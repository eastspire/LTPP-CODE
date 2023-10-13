<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;


class Ojjudge
{
    /**
     * 更新用户总分
     * @param int $contest_id 竞赛id
     * @param int $problem_id 问题id
     * @param int $resscore 总分
     * @param int $my_aid 用户id
     * @param string $type 竞赛类型
     * @param string $userlanguage 用户编程语言
     * @param string $code 代码
     * @param string $time 时间
     * @return void
     */
    static private function updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time)
    {
        $lastScore = null;
        $resscore = (int) $resscore;
        if ($type == 'OI') {
            $lastScore = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->first();
        } else {
            $lastScore = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->orderBy('score', 'desc')
                ->first();
        }
        if ($lastScore && $type == "OI") {
            // 更新一道题目的分数
            Db::table('contestrank')
                ->where('id', $lastScore->id)
                ->where('isdel', 0)
                ->update([
                    'score' => $resscore,
                    'code' => $code,
                    'submittime' => date('Y-m-d H:i:s', $time),
                    'language' => $userlanguage
                ]);
            Contest::sendUpdateRankMQ($contest_id);
            return;
        }
        //插入记录
        Db::table('contestrank')
            ->insert([
                'userid' => $my_aid,
                'contestid' => $contest_id,
                'problemid' => $problem_id,
                'score' => $resscore,
                'code' => $code,
                'submittime' => date('Y-m-d H:i:s', $time),
                'language' => $userlanguage
            ]);
        Contest::sendUpdateRankMQ($contest_id);
        return;
    }

    /**
     * 返回错误结果
     */
    static private function error(
        &$filepath,
        &$testin,
        $msg,
        &$db,
        $type,
        &$redis4,
        &$redis6,
        &$my_aid,
        &$problem_id,
        &$time,
        &$usetime,
        &$usememory,
        &$code,
        &$userlanguage,
        &$contest_id,
        &$begintime,
        &$endtime
    ) {
        if (!$msg) {
            $msg = '';
        } else {
            $msg .= "\n";
        }
        $redis6->decr('OjRunNum');
        Base::deleteAllFile($filepath);
        //错误AC百分比,保留两位小数
        $ACpoint = round((float) $db->ACNum / ((float) $db->ALLSubmitNum + 1), 2);
        Db::table('oj')
            ->where('id', $problem_id)
            ->where('isdel', 0)
            ->update(['ACpoint' => $ACpoint]);
        Db::table('codehistory')->insert([
            'userid' => $my_aid,
            'status' => $msg,
            'problemid' => $problem_id,
            'time' => date('Y-m-d H:i:s', $time),
            'usetime' => $usetime,
            'usememory' => $usememory,
            'code' => $code,
            'language' => $userlanguage,
            'contestid' => $contest_id
        ]);
        $isac = Db::table('contestrank')
            ->where('userid', $my_aid)
            ->where('contestid', $contest_id)
            ->where('problemid', $problem_id)
            ->where('score', 100)
            ->where('isdel', 0)
            ->exists();
        if ($contest_id != 0) {
            if ($time < $begintime || $endtime < $time) {
                return \json(['code' => -1, 'result' => $msg . "不在竞赛时间段内！提交不计入排名！\n错误的输入样例：\n" . $testin, 'usememory' => $usememory, 'usetime' => $usetime]);
            }
            //插入记录
            Db::table('contestrank')
                ->insert([
                    'userid' => $my_aid,
                    'contestid' => $contest_id,
                    'problemid' => $problem_id,
                    'score' => 0,
                    'code' => $code,
                    'submittime' => date('Y-m-d H:i:s', $time),
                    'language' => $userlanguage
                ]);
            if ($type == 'SQS') {
                return \json(['code' => -1, 'result' => $msg . "错误的输入样例：\n" . $testin, 'usememory' => $usememory, 'usetime' => $usetime]);
            } else {
                if ($isac) {
                    if ($type == 'ACM') {
                        return \json(['code' => -1, 'result' => $msg . "答案错误！\n您之前在本场竞赛AC该题，本次无罚时！", 'usememory' => $usememory, 'usetime' => $usetime]);
                    }
                    return \json(['code' => -1, 'result' => $msg . "答案错误！\n您之前在本场竞赛AC该题！", 'usememory' => $usememory, 'usetime' => $usetime]);
                }
                Contest::sendUpdateRankMQ($contest_id);
                return \json(['code' => -1, 'result' => $msg . '答案错误！', 'usememory' => $usememory, 'usetime' => $usetime]);
            }
        }
        return \json(['code' => -1, 'result' => $msg . "错误的输入样例：\n" . $testin, 'usememory' => $usememory, 'usetime' => $usetime]);
    }

    /**
     * 代码运行
     * @param Request $request 请求
     * @return string $res json
     */
    public function runCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeJudgeInstall()) {
            return json([
                'code' => -1,
                'result' => '判题机未安装',
                'usememory' => 0,
                'usetime' => 0
            ]);
        }
        $redis4 = Redis::connection('db4');
        $redis6 = Redis::connection('db6');
        $time = time(); // 根据时间戳区分
        //判题机限制
        //缓存存在
        if ($redis6->get('OjRunNum')) {
            $sumtime = 0;
            //设置最大同时判题数目
            Base::$GLOBmaxjudge = Base::getSettingKeyData('GLOBmaxjudge');
            Base::$GLOBmaxwaittime = Base::getSettingKeyData('GLOBmaxwaittime');
            while ($redis6->get('OjRunNum') >= Base::$GLOBmaxjudge) {
                if ($sumtime > Base::$GLOBmaxwaittime) {
                    return json([
                        'code' => -1,
                        'result' => '当前提交人数较多！请稍后再次提交哦！本次提交无罚时！',
                        'usememory' => 0,
                        'usetime' => 0
                    ]);
                }
                sleep(1);
                $sumtime++;
            }
        } else {
            $redis6->setNx('OjRunNum', 1);
        }
        $code = $request->post('code');
        $userlanguage = $request->post('userlanguage');
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        if (!$contest_id || $contest_id == '' || $contest_id < 0) {
            $contest_id = 0;
        }
        //赛制类型
        $type = "ACM";
        $begintime = $endtime = $time;
        $contestdb = [];
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $db = Db::table('oj')
            ->where('id', $problem_id)
            ->where('isdel', 0)
            ->select('Time', 'Memory')
            ->first();
        if (!$db) {
            return json([
                'code' => -1,
                'result' => '题目不存在！',
                'usememory' => 0,
                'usetime' => 0
            ]);
        }

        $is_my_pro = Oj::judgeIsMyProblem($problem_id, $my_aid);
        $contestdb = null;

        //竞赛中的题目
        if ($contest_id != 0) {
            $contestdb = Db::table('contest')
                ->where('id', $contest_id)
                ->where('isdel', 0)
                ->first();
            if (!$contestdb) {
                return json([
                    'code' => -1,
                    'result' => '竞赛不存在！',
                    'usememory' => 0,
                    'usetime' => 0
                ]);
            } else {
                $type = $contestdb->type;
                //判断用户有没有参加竞赛
                $isjoin = Db::table('joincontest')
                    ->where('contestid', $contest_id)
                    ->where('userid', $my_aid)
                    ->where('isdel', 0)
                    ->first();
                if (!$isjoin) {
                    return \json([
                        'code' => -1,
                        'result' => '您未报名该竞赛！',
                        'usememory' => 0,
                        'usetime' => 0
                    ]);
                }
                //竞赛是否存在判断
                if (!$contestdb) {
                    return \json([
                        'code' => -1,
                        'result' => '竞赛不存在！',
                        'usememory' => 0,
                        'usetime' => 0
                    ]);
                }
                $endtime = strtotime($contestdb->end);
                $begintime = strtotime($contestdb->begin);
                if ($begintime > $time && !$is_my_pro) {
                    return \json([
                        'code' => -1,
                        'result' => '竞赛未开始！权限不足！',
                        'usememory' => 0,
                        'usetime' => 0
                    ]);
                }
            }
        }

        $limittime = (int) $db->Time;
        $limitmemory = ((int) $db->Memory) << 20;

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
            $mainfile = uniqid() . mt_rand(1, 100000) . time() . $problem_id;
            $filepath = Base::$sandbox_path . $md5aid . '/' . $mainfile . '/';
        } while (file_exists($filepath));

        if (!file_exists($filepath)) {
            Base::judgeCreatPath($filepath, 0777);
        }

        $md5_problem_id = Base::doubleMd5($problem_id);
        $alltestpath = '/home/LTPP/testdata/' . $md5_problem_id . '/';
        //获取所有输入输出样例文件名称
        $testfilein = glob($alltestpath . '*.in');
        $testfileout = glob($alltestpath . '*.out');
        if (sizeof($testfileout) == 0) {
            // 兼容ICPC测试样例
            $testfileout = glob($alltestpath . '*.ans');
        }
        if (sizeof($testfilein) <= 0 || sizeof($testfileout) <= 0) {
            return json([
                'code' => -1,
                'result' => '题目测试样例不存在！',
                'usememory' => 0,
                'usetime' => 0
            ]);
        }
        $alltestnum = sizeof($testfilein);
        $actestnum = 0;
        $onetestscore = 0;
        if ($alltestnum > 0) {
            $onetestscore = 100 / $alltestnum;
        }

        //提交数加一
        $db = Db::table('oj')
            ->where('id', $problem_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            return \json([
                'code' => -1,
                'result' => '题目不存在！',
                'usememory' => 0,
                'usetime' => 0
            ]);
        } else {
            Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->increment('ALLSubmitNum', 1);
        }

        //代码所在路径+名称main
        $runcodefilepath = $filepath . 'main';

        $redis6->incr('OjRunNum');
        $out = [];
        //编译
        $compiler_res_json = Base::compiler($userlanguage, $code, $filepath, $runcodefilepath);

        if (!isset($compiler_res_json['code']) || $compiler_res_json['code'] != 1) {
            $redis6->decr('OjRunNum');
            return json($compiler_res_json);
        }

        $out = $compiler_res_json['result'];

        if (!empty($out)) {
            $redis6->decr('OjRunNum');
            Base::deleteallfile($filepath);
            $code .= "\n\n\n报错详情：\n";
            // 去除路径信息
            $res_data = '编译出错！' . "\n";
            $err_data = '';
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
                'time' => date('Y-m-d H:i:s', $time),
                'usetime' => 0,
                'usememory' => 0,
                'problemid' => $problem_id,
                'code' => $code,
                'language' => $userlanguage,
                'contestid' => $contest_id
            ]);

            if ($contest_id != 0) {
                if ($begintime <= $time && $time <= $endtime) {
                    //插入记录
                    Db::table('contestrank')->insert([
                        'userid' => $my_aid,
                        'contestid' => $contest_id,
                        'problemid' => $problem_id,
                        'score' => 0,
                        'code' => $code,
                        'submittime' => date('Y-m-d H:i:s', $time),
                        'language' => $userlanguage
                    ]);
                    Contest::sendUpdateRankMQ($contest_id);
                    return \json([
                        'code' => -1,
                        'result' => $res_data,
                        'usememory' => 0,
                        'usetime' => 0
                    ]);
                }
            }
            return \json([
                'code' => -1,
                'result' => $res_data,
                'usememory' => 0,
                'usetime' => 0
            ]);
        }

        //输出文件
        $outpath = $filepath . 'main.out';
        Base::writeToFile($outpath, '');
        //错误文件
        $errpath = $filepath . 'main.err';
        Base::writeToFile($errpath, '');

        $maxtime = '';
        $maxmemory = '';
        // 开始运行，当前判题数目加一

        // 遍历测试样例
        foreach ($testfileout as $temout) {
            Base::writeToFile($outpath, '');
            $path_parts = pathinfo($temout);
            //文件全名
            $fullname = $path_parts['basename'];
            //文件前缀名
            $testname = pathinfo($fullname, PATHINFO_FILENAME);
            $out = [];
            //运行
            $out = Base::run($userlanguage, $filepath, $alltestpath . $testname . '.in ', $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory);

            if (!$out || empty($out)) {
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => '判题机运行异常！', 'usememory' => 0, 'usetime' => 0]);
            }
            $out = $out[0];
            $run_resource_consumption = Base::getCodeTimeMemory($out);

            if (!$run_resource_consumption || !isset($run_resource_consumption['status'])) {
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => '判题机运行异常！', 'usetime' => 0, 'usememory' => 0]);
            }

            $status = $run_resource_consumption['status'] ?? 0;
            $time_used = $run_resource_consumption['time_used'] ?? 0;
            $memory_used = $run_resource_consumption['memory_used'] ?? 0;

            if ($status == Base::$judge_server_error) {
                $msg = $run_resource_consumption['msg'];
                $redis6->decr('OjRunNum');
                return \json(['code' => -1, 'result' => '判题机运行异常！' . "\n" . $msg, 'usetime' => $time_used, 'usememory' => $memory_used]);
            }

            //运行错误
            if ($status == Base::$judge_code_error) {
                $redis6->decr('OjRunNum');
                $code .= "\n\n\n报错详情：\n";
                $err_data = '运行出错' . "\n";
                //读取输出
                $resout = Base::getFileText($errpath);
                Base::deleteallfile($filepath);
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
                    'problemid' => $problem_id,
                    'time' => date('Y-m-d H:i:s', $time),
                    'usetime' => $time_used,
                    'usememory' => $memory_used,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => $contest_id
                ]);
                if ($contest_id != 0) {
                    if ($begintime <= $time || $time <= $endtime) {
                        //插入记录
                        Db::table('contestrank')->insert([
                            'userid' => $my_aid,
                            'contestid' => $contest_id,
                            'problemid' => $problem_id,
                            'score' => 0,
                            'code' => $code,
                            'submittime' => date('Y-m-d H:i:s', $time),
                            'language' => $userlanguage
                        ]);
                        Contest::sendUpdateRankMQ($contest_id);
                        return \json([
                            'code' => -1,
                            'result' => $err_data,
                            'usetime' => $time_used,
                            'usememory' => $memory_used
                        ]);
                    }
                }
                return \json([
                    'code' => -1,
                    'result' => $err_data,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ]);
            }

            $maxtime = max($maxtime, $time_used);
            $maxmemory = max($maxmemory, $memory_used);
            $testout = '';
            $testoutpath = $alltestpath . $fullname;
            // 不是竞赛赛题或者是SQS赛制判题遇到错误就结束
            if ($contest_id == 0 || $type == "SQS") {
                $testin = Base::getFileText($alltestpath . $testname . '.in');
                if (!$testin) {
                    $testin = '';
                }
                if (strlen($testin) > Base::$code_out_limit) {
                    $testin = Base::utfsubstr($testin, 0, Base::$code_out_limit, true) . "\n" . '（仅显示前' . Base::$code_out_limit . '个字符）';
                }
                switch ($status) {
                    case Base::$judge_code_tle:
                        return Ojjudge::error($filepath, $testin, 'TLE', $db, $type, $redis4, $redis6, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    case Base::$judge_code_mle:
                        return Ojjudge::error($filepath, $testin, 'MLE', $db, $type, $redis4, $redis6, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    case Base::$judge_code_re:
                        return Ojjudge::error($filepath, $testin, 'RE', $db, $type, $redis4, $redis6, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    default:
                        break;
                }
            }
            //读取输出
            $resout = Base::getFileText($outpath);
            $testout = Base::getFileText($testoutpath);
            //处理空格和换行错误
            $testout = str_replace([' ', "\n", "\r", "\r\n"], '', $testout);
            $resout = str_replace([' ', "\n", "\r", "\r\n"], '', $resout);

            //答案错误
            if ($testout != $resout) {
                //不是竞赛或者是SQS赛制，允许查看错误样例
                if ($contest_id == 0 || $type == "SQS") {
                    $redis6->decr('OjRunNum');
                    Base::insertToDb('codehistory', [
                        'userid' => $my_aid,
                        'status' => '答案错误',
                        'problemid' => $problem_id,
                        'time' => date('Y-m-d H:i:s', $time),
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                    //错误AC百分比,保留两位小数
                    $ACpoint = round((float) $db->ACNum / ((float) $db->ALLSubmitNum + 1), 2);
                    Db::table('oj')
                        ->where('id', $problem_id)
                        ->update(['ACpoint' => $ACpoint]);

                    Base::deleteallfile($filepath);
                    //读取出错测试样例输入
                    $testin = Base::getFileText($alltestpath . $testname . '.in');

                    if (strlen($testin) > Base::$code_out_limit) {
                        $testin = Base::utfsubstr($testin, 0, Base::$code_out_limit, true) . "\n" . '（仅显示前' . Base::$code_out_limit . '个字符）';
                    }
                    return \json([
                        'code' => -1,
                        'result' => '错误的输入样例：' . "\n" . $testin,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory
                    ]);
                }
            } else {
                ++$actestnum;
            }
            //删除新建文件下当前输出
            Base::deleteallfile($outpath);
        }
        $redis6->decr('OjRunNum');
        //最后删除文件夹
        Base::deleteallfile($filepath);
        //正确AC百分比,保留两位小数
        $ACpoint = round(((float) $db->ACNum + 1) / ((float) $db->ALLSubmitNum + 1), 2);
        Db::table('oj')
            ->where('id', $problem_id)
            ->update(['ACpoint' => $ACpoint]);

        if ($maxtime == '') {
            $maxtime = '0';
        }
        if ($maxmemory == '') {
            $maxmemory = '0';
        }

        if ($contest_id != 0) {
            $resscore = (int) ($actestnum * $onetestscore);
            if ($actestnum >= $alltestnum) {
                $resscore = 100;
            }
            // 不在竞赛时间内
            if ($time < $begintime || $endtime < $time) {
                if ($actestnum >= $alltestnum) {
                    // AC
                    // 代码记录
                    Base::insertToDb('codehistory', [
                        'userid' => $my_aid,
                        'status' => 'AC',
                        'problemid' => $problem_id,
                        'time' => date('Y-m-d H:i:s', $time),
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                    return \json([
                        'code' => 1,
                        'result' => '恭喜您AC本题！不在竞赛时间段内！提交不计入排名！',
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory
                    ]);
                } else {
                    // WA
                    // 代码记录
                    Base::insertToDb('codehistory', [
                        'userid' => $my_aid,
                        'status' => '答案错误',
                        'problemid' => $problem_id,
                        'time' => date('Y-m-d H:i:s', $time),
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                }
                return \json([
                    'code' => -1,
                    'result' => '答案错误！不在竞赛时间段内！提交不计入排名！' . "\n" . '本次得分：' . (int) $resscore . '分',
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ]);
            }
            // 在竞赛时间内
            // OI 赛制
            if ($type == 'OI') {
                // 代码记录
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => '正常运行',
                    'problemid' => $problem_id,
                    'time' => date('Y-m-d H:i:s', $time),
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => $contest_id
                ]);

                // 更新用户总得分，最后一次提交为准
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time);

                return \json([
                    'code' => -1,
                    'result' => 'OI赛制！无反馈！仅以最后一次正常运行为准！',
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ]);
            }
            // ACM,IOI,SQS赛制
            //竞赛过程中AC（满分）
            if ($actestnum >= $alltestnum && $begintime <= $time && $time <= $endtime) {
                //竞赛题目数目
                $prolist = Db::table('contestproblem')
                    ->where('contestid', $contest_id)
                    ->where('isdel', 0)
                    ->select('problemid')
                    ->get()
                    ->toArray();
                $pronum = sizeof($prolist);
                // AC数目
                $acnum = 0;

                $ac_list = Db::table('contestrank')
                    ->where('contestid', $contest_id)
                    ->where('userid', $my_aid)
                    ->where('score', 100)
                    ->where('submittime', '>=', $contestdb->begin)
                    ->where('submittime', '<=', $contestdb->end)
                    ->where('isdel', 0)
                    ->select('problemid')
                    ->distinct()
                    ->pluck('problemid')
                    ->toArray();
                $one_has_ac = false;
                $ac_list_len = sizeof($ac_list);
                $acnum = $ac_list_len;
                foreach ($ac_list as &$t_ac) {
                    if ($t_ac == $problem_id) {
                        $one_has_ac = true;
                        break;
                    }
                }
                if (!$one_has_ac) {
                    ++$acnum;
                }
                //更新用户解决的题目
                $issolve = Db::table('solveproblem')
                    ->where('userid', $my_aid)
                    ->where('problemid', $problem_id)
                    ->where('language', $userlanguage)
                    ->where('isdel', 0)
                    ->exists();
                if ($issolve) {
                    Db::table('solveproblem')
                        ->where('userid', $my_aid)
                        ->where('problemid', $problem_id)
                        ->update([
                            'userid' => $my_aid,
                            'problemid' => $problem_id,
                            'time' => date('Y-m-d H:i:s', $time),
                            'code' => $code,
                            'language' => $userlanguage
                        ]);
                } else {
                    Db::table('solveproblem')->insert([
                        'userid' => $my_aid,
                        'problemid' => $problem_id,
                        'time' => date('Y-m-d H:i:s', $time),
                        'code' => $code,
                        'language' => $userlanguage
                    ]);
                    Base::addAcMoney($my_aid, $db->problemName, $userlanguage);
                }
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => 'AC',
                    'problemid' => $problem_id,
                    'time' => date('Y-m-d H:i:s', $time),
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => $contest_id
                ]);

                //题目AC数目总量加一
                Db::table('oj')
                    ->where('id', $problem_id)
                    ->increment('ACNum', 1);

                //清空竞赛题目缓存（用户通过题目高亮，缓存会有影响，所以要清除）
                $redis4->del('Contest' . $contest_id . 'problemdata' . $my_aid);
                // 更新用户总得分
                // 填100避免99.99等情况造成的分数不准
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, 100, $my_aid, $type, $userlanguage, $code, $time);
                //一场竞赛该用户首次AK停止记录时间
                if ($acnum == $pronum && $ac_list_len < $pronum) {
                    Base::addAkMoney($my_aid, $contestdb);
                    Db::table('joincontest')
                        ->where('userid', $my_aid)
                        ->where('contestid', $contest_id)
                        ->where('isdel', 0)
                        ->update(['totaltime' => time() - $begintime]);
                    return \json([
                        'code' => 1,
                        'result' => '恭喜您AK了',
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                    ]);
                }
                if ($acnum > $pronum) {
                    return \json([
                        'code' => 1,
                        'result' => '恭喜您AK了',
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                    ]);
                }

                return \json([
                    'code' => 1,
                    'result' => '恭喜您AC了！',
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ]);
            } else {
                //竞赛过程中没有通过全部测试点
                // 更新用户总得分
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time);
                Base::insertToDb('codehistory', [
                    'userid' => $my_aid,
                    'status' => '答案错误',
                    'problemid' => $problem_id,
                    'time' => date('Y-m-d H:i:s', $time),
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                    'code' => $code,
                    'language' => $userlanguage,
                    'contestid' => $contest_id
                ]);
                if ($type == 'ACM') {
                    $msg = '答案错误！';
                } else {
                    $msg = '答案错误！' . "\n" . '本次得分：' . (int) $resscore . '分';
                }

            }
            return \json([
                'code' => -1,
                'result' => $msg,
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
            ]);
        }

        // 不是竞赛
        if ($actestnum >= $alltestnum) {
            Base::insertToDb('codehistory', [
                'userid' => $my_aid,
                'status' => 'AC',
                'problemid' => $problem_id,
                'time' => date('Y-m-d H:i:s', $time),
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
                'code' => $code,
                'language' => $userlanguage,
                'contestid' => $contest_id
            ]);
            //题目AC数目总量加一
            $hasac = Db::table('solveproblem')
                ->where('problemid', $problem_id)
                ->where('userid', $my_aid)
                ->where('language', $userlanguage)
                ->where('isdel', 0)
                ->select('id')
                ->orderBy('id', 'desc')
                ->first();
            if ($hasac) {
                Db::table('solveproblem')
                    ->where('id', $hasac->id)
                    ->update([
                        'code' => $code,
                        'time' => date('Y-m-d H:i:s', $time)
                    ]);
            } else {
                Db::table('solveproblem')->insert([
                    'userid' => $my_aid,
                    'problemid' => $problem_id,
                    'time' => date('Y-m-d H:i:s', $time),
                    'code' => $code,
                    'language' => $userlanguage
                ]);
                Base::addAcMoney($my_aid, $db->problemName, $userlanguage);
            }
            //题目AC数目总量加一
            Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->increment('ACNum', 1);
            return \json([
                'code' => 1,
                'result' => '恭喜您AC了',
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
            ]);
        } else {
            Base::insertToDb('codehistory', [
                'userid' => $my_aid,
                'status' => '答案错误',
                'problemid' => $problem_id,
                'time' => date('Y-m-d H:i:s', $time),
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
                'code' => $code,
                'language' => $userlanguage,
                'contestid' => $contest_id
            ]);
        }
        return \json([
            'code' => -1,
            'result' => '答案错误',
            'usetime' => $maxtime,
            'usememory' => $maxmemory,
        ]);
    }
}