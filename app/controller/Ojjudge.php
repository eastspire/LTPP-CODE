<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;

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
     * @param object $contestdb 竞赛
     * @return void
     */
    static private function updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time, $contestdb)
    {
        $resscore = (int) $resscore;
        if ($type == 'OI') {
            $lastScore = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->where('submittime', '>=', $contestdb->begin)
                ->where('submittime', '<=', $contestdb->end)
                ->orderBy('id', 'desc')
                ->first();
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
    }

    /**
     * 更新状态
     */
    static private function updateCodeStatus($code_id = 0, $status = '', $time_used = 0, $memory_used = 0)
    {
        if (!$code_id || !$status) {
            return;
        }
        $code_data = [
            'status' => $status,
            'usetime' => $time_used,
            'usememory' => $memory_used,
        ];
        RedisQueue::send(Base::$redis_queue_update_code_name, [
            'code_id' => $code_id,
            'code_data' => $code_data
        ]);
    }

    /**
     * 查询代码
     */
    public function queryCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!$my_aid) {
            return json(['code' => -1, 'result' => '请登录', 'usememory' => 0, 'usetime' => 0]);
        }
        $code_uid = $request->post('code_id');
        $code_id = Base::getIdByUid($code_uid);
        $code_db = Base::getCodeData($code_id);
        if (!$code_db) {
            return json(['code' => -1, 'result' => '代码不存在', 'usememory' => 0, 'usetime' => 0]);
        }
        if ($code_db->userid != $my_aid) {
            return json(['code' => -1, 'result' => '用户身份错误', 'usememory' => 0, 'usetime' => 0]);
        }
        $json = Base::getCodeJson($code_id);
        if ($json) {
            return json($json);
        }
        // 这里code得是0，状态只能从缓存读取信息
        return json(['code' => 0, 'result' => $code_db->status, 'usememory' => $code_db->usememory, 'usetime' => $code_db->usetime]);
    }

    /**
     * 返回错误结果
     */
    static private function error(
        &$code_id,
        &$filepath,
        &$testin,
        $msg,
        $type,
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
        Base::deleteAllFile($filepath);
        //错误
        RedisQueue::send(Base::$redis_queue_update_oj_name, [
            'problem_id' => $problem_id
        ]);
        Ojjudge::updateCodeStatus($code_id, $msg, $usetime, $usememory);
        $isac = Db::table('contestrank')
            ->where('userid', $my_aid)
            ->where('contestid', $contest_id)
            ->where('problemid', $problem_id)
            ->where('score', 100)
            ->where('isdel', 0)
            ->exists();
        if ($contest_id != 0) {
            if ($time < $begintime || $endtime < $time) {
                return [
                    'code' => -1,
                    'result' => $msg . "不在竞赛时间段内！提交不计入排名！\n错误的输入样例：\n" . $testin,
                    'usememory' => $usememory,
                    'usetime' => $usetime
                ];
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
                return [
                    'code' => -1,
                    'result' => $msg . "错误的输入样例：\n" . $testin,
                    'usememory' => $usememory,
                    'usetime' => $usetime
                ];
            } else {
                if ($isac) {
                    if ($type == 'ACM') {
                        return [
                            'code' => -1,
                            'result' => $msg . Base::$code_run_wrong . "！\n您之前在本场竞赛AC该题，本次无罚时！",
                            'usememory' => $usememory,
                            'usetime' => $usetime
                        ];
                    }
                    return [
                        'code' => -1,
                        'result' => $msg . Base::$code_run_wrong . "！\n您之前在本场竞赛AC该题！",
                        'usememory' => $usememory,
                        'usetime' => $usetime
                    ];
                }
                Contest::sendUpdateRankMQ($contest_id);
                return [
                    'code' => -1,
                    'result' => $msg . Base::$code_run_wrong . '！',
                    'usememory' => $usememory,
                    'usetime' => $usetime
                ];
            }
        }
        return [
            'code' => -1,
            'result' => $msg . "错误的输入样例：\n" . $testin,
            'usememory' => $usememory,
            'usetime' => $usetime
        ];
    }

    /**
     * 代码运行
     */
    public function runCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $code = $request->post('code') ?? '';
        $userlanguage = $request->post('userlanguage') ?? 'C++';
        $contest_uid = $request->post('contest_id') ?? '';
        $contest_id = Base::getIdByUid($contest_uid) ?? 0;
        $problem_uid = $request->post('problem_id') ?? '';
        $problem_id = Base::getIdByUid($problem_uid) ?? '';
        if (!$my_aid || !$problem_id || !$userlanguage) {
            return json([
                'code' => -1,
                'code_id' => '',
                'msg' => 'Base::$param_error_msg'
            ]);
        }
        //代码检测
        $check_safe_json = Base::judgeCodeSafe($code, $userlanguage);
        if (!isset($check_safe_json['code']) || $check_safe_json['code'] != 1) {
            return json($check_safe_json);
        }
        $code_id = Base::insertToDb('codehistory', [
            'userid' => $my_aid,
            'status' => Base::$code_up_waiting,
            'time' => date('Y-m-d H:i:s', time()),
            'usetime' => 0,
            'usememory' => 0,
            'code' => $code,
            'language' => $userlanguage,
            'contestid' => $contest_id,
            'problemid' => $problem_id
        ]);
        if ($code_id) {
            // 发送给消息队列
            RedisQueue::send(Base::$redis_queue_judgecode_run_name, [
                'my_aid' => $my_aid,
                'code_id' => $code_id,
                'code' => $code,
                'userlanguage' => $userlanguage,
                'contest_id' => $contest_id,
                'problem_id' => $problem_id
            ]);
            $code_uid = Base::getUidById($code_id);
            return json([
                'code' => 1,
                'code_id' => $code_uid,
                'msg' => Base::$code_up_success_msg
            ]);
        }
        return json([
            'code' => -1,
            'code_id' => '',
            'msg' => Base::$code_up_fail_msg
        ]);
    }

    /**
     * 代码运行
     * @param int $my_aid
     * @param int $code_id
     * @param string $code
     * @param string $userlanguage
     * @param int $problem_id
     * @param int $contest_id
     * @return array $json
     */
    static public function run($my_aid = 0, $code_id = 0, $code = '', $userlanguage = 'C++', $problem_id = 0, $contest_id = 0)
    {
        Ojjudge::updateCodeStatus($code_id, Base::$code_up_running, 0, 0);
        if (!$my_aid || !$code_id || !$code || !$userlanguage || !$problem_id) {
            return [
                'code' => -1,
                'result' => 'Base::$param_error_msg',
                'usememory' => 0,
                'usetime' => 0
            ];
        }

        if (!$contest_id || $contest_id == '' || $contest_id <= 0) {
            $contest_id = 0;
        }

        if (!Base::judgeJudgeInstall()) {
            Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
            return [
                'code' => -1,
                'result' => '判题机未安装',
                'usememory' => 0,
                'usetime' => 0
            ];
        }
        $redis4 = Redis::connection('db4');

        $time = time(); // 根据时间戳区分
        //赛制类型
        $type = "ACM";
        $begintime = $endtime = $time;
        $contestdb = [];

        $db = Base::getOjData($problem_id);
        if (!$db) {
            Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
            return [
                'code' => -1,
                'result' => '题目不存在！',
                'usememory' => 0,
                'usetime' => 0
            ];
        }

        $is_my_pro = Oj::judgeIsMyProblem($problem_id, $my_aid);
        $contestdb = null;

        //竞赛中的题目
        if ($contest_id != 0) {
            $contestdb = Base::getContestData($contest_id);
            if (!$contestdb) {
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                return [
                    'code' => -1,
                    'result' => '竞赛不存在！',
                    'usememory' => 0,
                    'usetime' => 0
                ];
            } else {
                $type = $contestdb->type;
                //判断用户有没有参加竞赛
                $isjoin = Db::table('joincontest')
                    ->where('contestid', $contest_id)
                    ->where('userid', $my_aid)
                    ->where('isdel', 0)
                    ->first();
                if (!$isjoin) {
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                    return [
                        'code' => -1,
                        'result' => '您未报名该竞赛！',
                        'usememory' => 0,
                        'usetime' => 0
                    ];
                }
                //竞赛是否存在判断
                if (!$contestdb) {
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                    return [
                        'code' => -1,
                        'result' => '竞赛不存在！',
                        'usememory' => 0,
                        'usetime' => 0
                    ];
                }
                $endtime = strtotime($contestdb->end);
                $begintime = strtotime($contestdb->begin);
                if ($begintime > $time && !$is_my_pro) {
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                    return [
                        'code' => -1,
                        'result' => '竞赛未开始！权限不足！',
                        'usememory' => 0,
                        'usetime' => 0
                    ];
                }
            }
        }

        $limittime = (int) $db->Time;
        $limitmemory = ((int) $db->Memory) << 20;

        $dir_res = Base::creatCodeRunDirFile($my_aid, '', true);
        $mainfile = $dir_res['mainfile'];
        $filepath = $dir_res['filepath'];
        $runcodefilepath = $dir_res['runcodefilepath'];
        $outpath = $dir_res['outpath'];
        $errpath = $dir_res['errpath'];

        $md5_problem_id = Base::doubleMd5($problem_id);
        $alltestpath = Base::$testdata_path . $md5_problem_id . '/';
        $test_data_list = Base::getOjTestDataList($problem_id);
        if (sizeof($test_data_list) <= 0) {
            Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
            return [
                'code' => -1,
                'result' => '题目测试样例不存在！',
                'usememory' => 0,
                'usetime' => 0
            ];
        }
        Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
        // 获取所有输入输出样例文件名称
        $testfilein = glob($alltestpath . '*.in');
        $alltestnum = sizeof($testfilein);
        if ($alltestnum <= 0) {
            Base::deleteAllFile($alltestpath);
            Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
            $testfilein = glob($alltestpath . '*.in');
            $alltestnum = sizeof($testfilein);
            if ($alltestnum <= 0) {
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                return [
                    'code' => -1,
                    'result' => '题目测试样例不存在！',
                    'usememory' => 0,
                    'usetime' => 0
                ];
            }
        }
        $actestnum = 0;
        $onetestscore = 0;
        if ($alltestnum > 0) {
            $onetestscore = 100 / $alltestnum;
        }

        //代码所在路径+名称main
        $runcodefilepath = $filepath . 'main';
        $out = '';
        // 代码写入文件
        $write_code_res_json = Base::writeCodeToFile($userlanguage, $code, $filepath, $runcodefilepath);

        if (!isset($write_code_res_json['code']) || $write_code_res_json['code'] != 1) {
            Base::deleteAllFile($filepath);
            Ojjudge::updateCodeStatus($code_id, Base::$judge_server_error, 0, 0);
            return json($write_code_res_json);
        }

        $maxtime = '';
        $maxmemory = '';
        // 开始运行，当前判题数目加一
        // 遍历测试样例
        foreach ($test_data_list as &$one_oj_test_data_db) {
            // 清空输出
            Base::writeToFile($outpath, '');
            // 清空错误
            Base::writeToFile($errpath, '');
            //文件前缀名
            $testname = $one_oj_test_data_db->id;
            $out = '';
            //运行
            $out = Base::run($userlanguage, $filepath, $alltestpath . $testname . '.in', $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory);

            $run_resource_consumption = Base::getCodeTimeMemory($out);

            if (!$run_resource_consumption || !isset($run_resource_consumption['status'])) {
                Base::deleteAllFile($filepath);
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                return ['code' => -1, 'result' => Base::$judge_error_msg . '！', 'usetime' => 0, 'usememory' => 0];
            }

            $status = $run_resource_consumption['status'] ?? 0;
            $time_used = $run_resource_consumption['time_used'] ?? 0;
            $memory_used = $run_resource_consumption['memory_used'] ?? 0;
            $msg = $run_resource_consumption['msg'] ?? '';

            $maxtime = max($maxtime, $time_used);
            $maxmemory = max($maxmemory, $memory_used);
            $testout = '';
            // 不是竞赛赛题或者是SQS赛制判题遇到错误就结束
            if ($contest_id == 0 || $type == "SQS") {
                switch ($status) {
                    case Base::$judge_code_compiler_error:
                        Base::deleteAllFile($filepath);
                        // 去除路径信息
                        $msg = Base::removeMsgSandboxPath($mainfile, $msg);
                        Ojjudge::updateCodeStatus($code_id, Base::$code_run_compiler_wrong, $time_used, $memory_used);
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
                                return [
                                    'code' => -1,
                                    'result' => $msg,
                                    'usetime' => $time_used,
                                    'usememory' => $memory_used
                                ];
                            }
                        }
                        return [
                            'code' => -1,
                            'result' => $msg,
                            'usetime' => $time_used,
                            'usememory' => $memory_used
                        ];
                    case Base::$judge_code_error:
                        Base::deleteAllFile($filepath);
                        // 去除路径信息
                        $msg = Base::removeMsgSandboxPath($mainfile, $msg);
                        Ojjudge::updateCodeStatus($code_id, Base::$code_run_running_wrong, $time_used, $memory_used);
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
                                return [
                                    'code' => -1,
                                    'result' => $msg,
                                    'usetime' => $time_used,
                                    'usememory' => $memory_used
                                ];
                            }
                        }
                        return [
                            'code' => -1,
                            'result' => $msg,
                            'usetime' => $time_used,
                            'usememory' => $memory_used
                        ];
                    case Base::$judge_server_error:
                        Base::deleteAllFile($filepath);
                        // 去除路径信息
                        $msg = Base::removeMsgSandboxPath($mainfile, $msg);
                        return [
                            'code' => -1,
                            'result' => $msg,
                            'usetime' => $time_used,
                            'usememory' => $memory_used
                        ];
                    case Base::$judge_code_tle:
                        Base::deleteAllFile($filepath);
                        $testin = Base::getTestinFileData($alltestpath . $testname . '.in');
                        return Ojjudge::error($code_id, $filepath, $testin, Base::$code_run_tle, $type, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    case Base::$judge_code_mle:
                        Base::deleteAllFile($filepath);
                        $testin = Base::getTestinFileData($alltestpath . $testname . '.in');
                        return Ojjudge::error($code_id, $filepath, $testin, Base::$code_run_mle, $type, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    case Base::$judge_code_re:
                        Base::deleteAllFile($filepath);
                        $testin = Base::getTestinFileData($alltestpath . $testname . '.in');
                        return Ojjudge::error($code_id, $filepath, $testin, Base::$code_run_re, $type, $my_aid, $problem_id, $time, $maxtime, $maxmemory, $code, $userlanguage, $contest_id, $begintime, $endtime);
                    default:
                        break;
                }
            }
            //读取输出
            $testout = Base::textToSafeText($one_oj_test_data_db->test_out);
            //处理空格和换行错误
            $testout = str_replace([' ', "\n", "\r", "\r\n"], '', $testout);
            $resout = str_replace([' ', "\n", "\r", "\r\n"], '', $msg);

            //答案错误
            if ($testout != $resout) {
                //不是竞赛或者是SQS赛制，允许查看错误样例
                if ($contest_id == 0 || $type == "SQS") {
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_wrong, $time_used, $memory_used);
                    //错误
                    RedisQueue::send(Base::$redis_queue_update_oj_name, [
                        'problem_id' => $problem_id,
                    ]);
                    Base::deleteAllFile($filepath);
                    //读取出错测试样例输入
                    $testin = Base::getTestinFileData($alltestpath . $testname . '.in');
                    return [
                        'code' => -1,
                        'result' => '错误的输入样例：' . "\n" . $testin,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory
                    ];
                }
            } else {
                ++$actestnum;
            }
            //删除新建文件下当前输出
            Base::deleteAllFile($outpath);
        }

        //最后删除文件夹
        Base::deleteAllFile($filepath);

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
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_ac, $maxtime, $maxmemory);
                    RedisQueue::send(Base::$redis_queue_update_oj_name, [
                        'problem_id' => $problem_id,
                        'is_ac' => true
                    ]);
                    return [
                        'code' => 1,
                        'result' => '恭喜您AC本题！不在竞赛时间段内！提交不计入排名！',
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory
                    ];
                } else {
                    // WA
                    // 代码记录
                    Ojjudge::updateCodeStatus($code_id, Base::$code_run_wrong, $maxtime, $maxmemory);
                    RedisQueue::send(Base::$redis_queue_update_oj_name, [
                        'problem_id' => $problem_id,
                    ]);
                }
                return [
                    'code' => -1,
                    'result' => Base::$code_run_wrong . '！不在竞赛时间段内！提交不计入排名！' . "\n" . '本次得分：' . (int) $resscore . '分',
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ];
            }
            // 在竞赛时间内
            // OI 赛制
            if ($type == 'OI') {
                // 代码记录
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_success, $maxtime, $maxmemory);
                // 更新用户总得分，最后一次提交为准
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time, $contestdb);
                return [
                    'code' => -1,
                    'result' => 'OI赛制！无反馈！仅以最后一次正常运行为准！',
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ];
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
                $pronum = 0;
                foreach ($prolist as &$tem) {
                    $problem_db = Base::getOjData($tem->problemid);
                    if (!$problem_db) {
                        continue;
                    }
                    ++$pronum;
                }
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
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_ac, $maxtime, $maxmemory);

                RedisQueue::send(Base::$redis_queue_update_oj_name, [
                    'problem_id' => $problem_id,
                    'is_ac' => true
                ]);
                //清空竞赛题目缓存（用户通过题目高亮，缓存会有影响，所以要清除）
                $redis4->del('Contest' . $contest_id . 'problemdata' . $my_aid);
                // 更新用户总得分
                // 填100避免99.99等情况造成的分数不准
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, 100, $my_aid, $type, $userlanguage, $code, $time, $contestdb);
                //一场竞赛该用户首次AK停止记录时间
                if ($acnum == $pronum && $ac_list_len < $pronum) {
                    Base::addAkMoney($my_aid, $contestdb);
                    return [
                        'code' => 1,
                        'result' => Base::$ak_msg,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                    ];
                }
                if ($acnum > $pronum) {
                    return [
                        'code' => 1,
                        'result' => Base::$ak_msg,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                    ];
                }

                return [
                    'code' => 1,
                    'result' => Base::$ac_msg,
                    'usetime' => $maxtime,
                    'usememory' => $maxmemory,
                ];
            } else {
                //竞赛过程中没有通过全部测试点
                // 更新用户总得分
                RedisQueue::send(Base::$redis_queue_update_oj_name, [
                    'problem_id' => $problem_id,
                ]);
                Ojjudge::updateUserTotalScore($contest_id, $problem_id, $resscore, $my_aid, $type, $userlanguage, $code, $time, $contestdb);
                Ojjudge::updateCodeStatus($code_id, Base::$code_run_wrong, $maxtime, $maxmemory);
                if ($type == 'ACM') {
                    $msg = Base::$code_run_wrong . '！';
                } else {
                    $msg = Base::$code_run_wrong . '！' . "\n" . '本次得分：' . (int) $resscore . '分';
                }
            }
            return [
                'code' => -1,
                'result' => $msg,
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
            ];
        }
        // 不是竞赛
        if ($actestnum >= $alltestnum) {
            Ojjudge::updateCodeStatus($code_id, Base::$code_run_ac, $maxtime, $maxmemory);
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
                Db::table('solveproblem')
                    ->insert([
                        'userid' => $my_aid,
                        'problemid' => $problem_id,
                        'time' => date('Y-m-d H:i:s', $time),
                        'code' => $code,
                        'language' => $userlanguage
                    ]);
                Base::addAcMoney($my_aid, $db->problemName, $userlanguage);
            }
            //题目AC数目总量加一            
            RedisQueue::send(Base::$redis_queue_update_oj_name, [
                'problem_id' => $problem_id,
                'is_ac' => true
            ]);
            return [
                'code' => 1,
                'result' => Base::$ac_msg,
                'usetime' => $maxtime,
                'usememory' => $maxmemory,
            ];
        }
        Ojjudge::updateCodeStatus($code_id, Base::$code_run_wrong, $maxtime, $maxmemory);
        RedisQueue::send(Base::$redis_queue_update_oj_name, [
            'problem_id' => $problem_id,
        ]);
        return [
            'code' => -1,
            'result' => Base::$code_run_wrong,
            'usetime' => $maxtime,
            'usememory' => $maxmemory,
        ];
    }
}
