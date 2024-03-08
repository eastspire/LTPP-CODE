<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-03 19:34:57
 * @FilePath: \LTPP-CODE\plugin\webman\gateway\PrivateRobot.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use Exception;
use app\controller\Ssh;
use GatewayWorker\Lib\Gateway;
use support\Db;
use app\controller\Base;
use app\controller\Robot;
use app\controller\Image;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;

class PrivateRobot extends ChatBase
{
    /**
     * GPT 异常消息
     */
    static $gpt_err_msg = '机器人头疼！需要休息！';

    /**
     * GPT API地址
     */
    static $gpt_api_url = 'http://127.0.0.1:28787';

    /**
     * GPT 每个消息价格
     */
    static $one_msg_cost = 0.09;

    /**
     * GPT上下文环境
     */
    static $gpt_chat_history_limit = 8;

    static $robot_root_default =
    '<h4>您可以输入以下单个序号来进行操作</h4>' . "\n\n" .
        '|序号|操作|示例|' . "\n" .
        '|-|-|-|' . "\n" .
        '|1|获取用户总数|1|' . "\n" .
        '|2|获取文章总数|2|' . "\n" .
        '|3|获取题库总数|3|' . "\n" .
        '|4|获取竞赛总数|4|' . "\n" .
        '|5|获取代码运行记录总数|5|' . "\n" .
        '|6|获取public文件夹大小|6|' . "\n" .
        '|7|获取私聊+群聊的消息总数|7|' . "\n" .
        '|8|获取当前服务器负载和进程运行情况|8|' . "\n" .
        '|9|获取管理员用户名单|9|' . "\n" .
        '|10|开启全站用户音乐功能|10|' . "\n" .
        '|11|清空服务器日志|11|' . "\n" .
        '|12|清空tmp目录|12|' . "\n" .
        '|13|通过机器人群发消息（此消息将保存到数据库）|13 你好（PS：序号后第一个空格后为发送的内容）|' . "\n" .
        '|14|重置全站用户头像为默认头像|14|' . "\n" .
        '|15|关闭全站用户音乐功能|15|' . "\n" .
        '|16|设置全站用户图片背景|16 http://xxx.com/x.png（PS：序号后第一个空格后为图片URL）|' . "\n" .
        '|17|设置全站用户视频背景|17 http://xxx.com/x.mp4（PS：序号后第一个空格后为视频URL）|' . "\n" .
        '|18|重判题目在竞赛中的代码|18 A+B（PS：序号后第一个空格后为重判的题目完整标题）|' . "\n" .
        '|19|购买私人轻量云服务器，系统自带多种编程语言的运行环境和在线VSCODE|19 LTPP（PS：序号后第一个空格后为SSH登录密码，若不设置系统会自动生成随机密码，若已购买可查看登录命令和密码）|' . "\n" .
        '|20|查询当前在线客户端总数|20|' . "\n" .
        '|21|重置所有机器人账号的密码|21|' . "\n" .
        '|22|查看机器人用户个数和非机器人用户个数|22|';

    static $robot_admin_default =
    '<h4>您可以输入以下单个序号来进行操作</h4>' . "\n\n" .
        '|序号|操作|示例|' . "\n" .
        '|-|-|-|' . "\n" .
        '|1|重判题目在竞赛中的代码|1 A+B（PS：序号后第一个空格后为重判的题目完整标题）|' . "\n" .
        '|2|购买私人轻量云服务器，系统自带多种编程语言的运行环境和在线VSCODE|2 LTPP（PS：序号后第一个空格后为SSH登录密码，若不设置系统会自动生成随机密码，若已购买可查看登录命令和密码）|';

    static $robot_user_default =
    '<h4>您可以输入以下单个序号来进行操作</h4>' . "\n\n" .
        '|序号|操作|示例|' . "\n" .
        '|-|-|-|' . "\n" .
        '|1|购买私人轻量云服务器，系统自带多种编程语言的运行环境和在线VSCODE|1 LTPP（PS：序号后第一个空格后为SSH登录密码，若不设置系统会自动生成随机密码，若已购买可查看登录命令和密码）|';

    /**
     * 判断用户是否发给机器人消息
     * @param string $user_id 接收消息的用户的id
     * @param object $db_my 用户的数据库个人数据
     * @param string $client_id 客户端id
     * @param string $msg 用户发给机器人的消息
     */
    static protected function isSendToRobot(&$user_id, &$db_my, &$client_id, &$msg)
    {
        $robot_id = Base::getRobotId();
        if (!$robot_id) {
            // 机器人账号不存在，立即发送root邮件通知
            $root_id = Base::getRootId();
            $user_db = Base::getUserData($root_id);
            $now = date('Y-m-d H:i:s', time());
            $email = Base::getRobotEmail();
            $new_password = md5(uniqid() . mt_rand(1, 100000) . time());
            $data = [
                'name' => '机器人',
                'password' => Base::passwordEncryption($new_password),
                'sex' => '男',
                'registertime' => $now,
                'headimage' => Base::getEmailImageToLtppUrl($email),
                'fans' => 0,
                'follow' => 0,
                'grade' => 1,
                'email' => $email,
                'lastlogin' => $now
            ];
            $id = Base::insertToDb('user', $data);
            if (!$user_db) {
                return;
            }
            $title = '紧急通知';
            $content = '系统机器人的账号不存在，系统已自动重新生成！机器人账号最新id:' . $id . '【新密码：' . $new_password . '】';
            RedisQueue::send(Base::$redis_queue_send_mail_name, [
                'to' => $user_db->email,
                'title' => $title,
                'content' => $content
            ]);
            return;
        }
        if ($user_id != $robot_id) {
            return;
        }
        $robot_db = Base::getUserData($robot_id);
        $isroot = Base::judgeIsRoot($db_my->id);
        $isadmin = Base::judgeIsAdmin($db_my->id);
        if ($isroot) {
            PrivateRobot::rootSendToRobot($robot_db, $db_my, $client_id, $msg);
        } else if ($isadmin) {
            PrivateRobot::adminSendToRobot($robot_db, $db_my, $client_id, $msg);
        } else {
            PrivateRobot::userSendToRobot($robot_db, $db_my, $client_id, $msg);
        }
        return;
    }

    /**
     * 清空服务器日志
     * @param string $reply 消息
     */
    static private function deleteLogMore(&$reply)
    {
        Base::deleteAllFile(Base::$LTPP_logs_path);
        Base::judgeCreatPath(Base::$LTPP_logs_path);
        $reply = '清空服务器日志成功';
    }

    /**
     * 加载服务器配置
     * @param string $reply 消息
     */
    static private function loadLinuxMore(&$reply)
    {
        $res = [];
        $out = [];
        $name = '服务器信息：';
        $res[] = array($name);
        exec('lsb_release -a 2>&1', $out);
        $res[] = $out;
        $out = [];
        exec('uname -a 2>&1', $out);
        $res[] = $out;
        $out = [];
        $name = 'CPU型号：';
        $res[] = array($name);
        exec("grep 'model name' /proc/cpuinfo |uniq", $out);
        $res[] = $out;
        $out = [];
        $name = 'CPU物理个数 ：';
        $res[] = array($name);
        exec("grep 'physical id' /proc/cpuinfo |sort |uniq |wc -l", $out);
        $res[] = $out;
        $out = [];
        $name = 'CPU核心数 ：';
        $res[] = array($name);
        exec("grep 'cpu cores' /proc/cpuinfo |uniq", $out);
        $res[] = $out;
        $name = 'CPU使用情况：';
        $res[] = array($name);
        exec("mpstat", $out);
        $res[] = $out;
        $name = '内存信息：';
        $res[] = array($name);
        $out = [];
        exec('free -h 2>&1', $out);
        $res[] = $out;
        $out = [];
        exec('free -m 2>&1', $out);
        $res[] = $out;
        $out = [];
        exec('vmstat 2>&1', $out);
        $res[] = $out;
        $out = [];
        exec('cat /proc/meminfo 2>&1', $out);
        $res[] = $out;
        $out = [];
        $name = '磁盘信息：';
        $res[] = array($name);
        $out = [];
        exec('df -h 2>&1', $out);
        $res[] = $out;
        $out = [];
        $name = '当前进程：';
        $res[] = array($name);
        $out = [];
        exec('ps -ef 2>&1', $out);
        $res[] = $out;
        $out = [];
        foreach ($res as &$t) {
            foreach ($t as &$tt) {
                $reply .= $tt . "\n";
            }
        }
    }

    /**
     * 竞赛赛题重判
     * @param string $client_id
     * @param string $my_aid
     * @param string $problem_id
     * @param object $robot_db
     */
    static private function rejudgeOneProblem($client_id, $my_aid, $problem_name, $robot_db)
    {
        $is_admin = Base::judgeIsAdmin($my_aid);
        if (!$is_admin) {
            return '权限不足！';
        }
        $problem_db = Db::table('oj')
            ->where('problemName', $problem_name)
            ->where('isdel', 0)
            ->select('id', 'problemName', 'Time', 'Memory', 'ACNum', 'ALLSubmitNum')
            ->first();
        if (!$problem_db) {
            return '题目<strong>【' . $problem_name . '】</strong>不存在！';
        }
        $problem_id = $problem_db->id;
        if (!$problem_id) {
            return '题目<strong>【' . $problem_name . '】</strong>不存在！';
        }
        $redis4 = Redis::connection('db4');
        $contest_list = Db::table('contestrank')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->select('contestid')
            ->orderBy('contestid', 'desc')
            ->distinct()
            ->pluck('contestid')
            ->toArray();
        $limittime = (int) $problem_db->Time;
        $limitmemory = ((int) $problem_db->Memory) << 20;
        $md5_problem_id = Base::doubleMd5($problem_id);
        $alltestpath =  Base::$testdata_path . $md5_problem_id . '/';
        $test_data_list = Base::getOjTestDataList($problem_id);
        Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
        if (sizeof($test_data_list) <= 0) {
            return '题目<strong>【' . $problem_name . '】</strong>无测试样例！';
        }
        // 获取所有输入输出样例文件名称
        $testfilein = glob($alltestpath . '*.in');
        $alltestnum = sizeof($testfilein);
        if ($alltestnum <= 0) {
            Base::deleteAllFile($alltestpath);
            Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
            $testfilein = glob($alltestpath . '*.in');
            $alltestnum = sizeof($testfilein);
            if ($alltestnum <= 0) {
                return '题目<strong>【' . $problem_name . '】</strong>无测试样例！';
            }
        }
        foreach ($contest_list as &$contest_id) {
            $contest_db = Db::table('contest')
                ->where('id', $contest_id)
                ->where('isdel', 0)
                ->select('id', 'name')
                ->first();
            if (!$contest_db) {
                continue;
            }
            $list = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->select('id', 'userid', 'language', 'code')
                ->get();

            foreach ($list as &$tem) {
                $userlanguage = $tem->language;
                if (!$userlanguage) {
                    $userlanguage = '';
                }
                $code = $tem->code;
                $user_id = $tem->userid;
                if ($code == '') {
                    continue;
                }
                //代码检测
                $check_safe_json = Base::judgeCodeSafe($code, $userlanguage);
                if (!isset($check_safe_json['code']) || $check_safe_json['code'] != 1) {
                    continue;
                }
                $userlanguage = strtolower($userlanguage);
                if (!isset(Base::$language_map[$userlanguage])) {
                    continue;
                }
                $userlanguage = Base::$language_map[$userlanguage];
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

                $alltestnum = sizeof($testfilein);
                $actestnum = 0;
                $onetestscore = 0;
                if ($alltestnum > 0) {
                    $onetestscore = 100 / $alltestnum;
                }

                Base::updateOjDataRedis($problem_id);
                //代码所在路径+前缀名称main
                $runcodefilepath = $filepath . 'main';
                //编译
                $out = [];
                $compiler_res_json = Base::compiler($userlanguage, $code, $filepath, $runcodefilepath);
                if (!isset($compiler_res_json['code']) || $compiler_res_json['code'] != 1) {
                    continue;
                }
                $out = $compiler_res_json['result'];
                $now = date('Y-m-d H:i:s', time());
                if (!empty($out)) {
                    Base::deleteAllFile($filepath);
                    $code = $code . "\n\n\n报错详情：\n";
                    // 去除路径信息
                    $err_data = '';
                    foreach ($out as &$err_tem) {
                        $err_data .= $err_tem . "\n";
                    }
                    $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $md5aid . '/' . $mainfile . '/';
                    $err_data = str_replace($tp, '', $err_data);
                    $tp = $md5aid . '/' . $mainfile . '/';
                    $err_data = str_replace($tp, '', $err_data);
                    Base::removeBr($err_data);

                    $code .= $err_data;

                    Db::table('contestrank')
                        ->where('id', $tem->id)
                        ->where('isdel', 0)
                        ->update(['score' => 0]);
                    Base::insertToDb('codehistory', [
                        'userid' => $user_id,
                        'status' => '编译出错',
                        'problemid' => $problem_id,
                        'time' => $now,
                        'usetime' => 0,
                        'usememory' => 0,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                    continue;
                }

                //输出文件
                $outpath = $filepath . 'main.out';
                Base::writeToFile($outpath, '');
                //错误文件
                $errpath = $filepath . 'main.err';
                Base::writeToFile($errpath, '');
                $maxtime = '';
                $maxmemory = '';
                // 遍历测试样例
                foreach ($test_data_list as &$one_oj_test_data_db) {
                    Base::writeToFile($outpath, '');
                    //文件前缀名
                    $testname = $one_oj_test_data_db->id;
                    //运行
                    $out = [];
                    $out = Base::run($userlanguage, $filepath, $alltestpath . $testname . '.in', $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory);
                    if (!$out || empty($out)) {
                        RedisQueue::send(Base::$redis_queue_update_oj_name, [
                            'problem_id' => $problem_id,
                            'is_ac' => false
                        ]);
                        continue;
                    }
                    $out = $out[0];
                    $run_resource_consumption = Base::getCodeTimeMemory($out);
                    if (!$run_resource_consumption || !isset($run_resource_consumption['status'])) {
                        RedisQueue::send(Base::$redis_queue_update_oj_name, [
                            'problem_id' => $problem_id,
                            'is_ac' => false
                        ]);
                        continue;
                    }

                    $status = $run_resource_consumption['status'] ?? 0;
                    $time_used = $run_resource_consumption['time_used'] ?? 0;
                    $memory_used = $run_resource_consumption['memory_used'] ?? 0;

                    if ($status == Base::$judge_server_error) {
                        RedisQueue::send(Base::$redis_queue_update_oj_name, [
                            'problem_id' => $problem_id,
                            'is_ac' => false
                        ]);
                        continue;
                    }

                    //运行错误
                    if ($status == Base::$judge_code_error) {
                        //读取输出
                        $code .= "\n\n\n报错详情：\n";
                        $resout = Base::getFileText($errpath);
                        Base::deleteallfile($filepath);
                        // 去除路径信息                    
                        $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $md5aid . '/' . $mainfile . '/';
                        $resout = str_replace($tp, '', $resout);
                        $tp = $md5aid . '/' . $mainfile . '/';
                        $resout = str_replace($tp, '', $resout);
                        Base::removeBr($resout);

                        if (strlen($resout) > Base::$code_out_limit) {
                            $code = Base::utfsubstr($code, 0, Base::$code_out_limit, true) . "\n" . '（仅显示前' . Base::$code_out_limit . '个字符）';
                        }
                        $code .= $resout . "\n";

                        Base::insertToDb('codehistory', [
                            'userid' => $user_id,
                            'status' => '运行出错',
                            'problemid' => $problem_id,
                            'time' => $now,
                            'usetime' => $time_used,
                            'usememory' => $memory_used,
                            'code' => $code,
                            'language' => $userlanguage,
                            'contestid' => $contest_id
                        ]);
                        RedisQueue::send(Base::$redis_queue_update_oj_name, [
                            'problem_id' => $problem_id,
                            'is_ac' => false
                        ]);
                        continue;
                    }

                    $maxtime = max($maxtime, $time_used);
                    $maxmemory = max($maxmemory, $memory_used);

                    $testout = '';

                    if ($status != Base::$judge_code_finish) {
                        Base::deleteallfile($filepath);
                        $tips = '';
                        switch ($status) {
                            case Base::$judge_code_tle:
                                $tips = 'TLE';
                                break;
                            case Base::$judge_code_mle:
                                $tips = 'MLE';
                                break;
                            case Base::$judge_code_re:
                                $tips = 'RE';
                                break;
                            default:
                                break;
                        }
                        Base::insertToDb('codehistory', [
                            'userid' => $user_id,
                            'status' => $tips,
                            'problemid' => $problem_id,
                            'time' => $now,
                            'usetime' => $maxtime,
                            'usememory' => $maxmemory,
                            'code' => $code,
                            'language' => $userlanguage,
                            'contestid' => $contest_id
                        ]);
                        RedisQueue::send(Base::$redis_queue_update_oj_name, [
                            'problem_id' => $problem_id,
                            'is_ac' => false
                        ]);
                        continue;
                    }
                    //读取输出
                    $resout = Base::getFileText($outpath);
                    $testout = Base::textToSafeText($one_oj_test_data_db->test_out);
                    //处理空格和换行错误
                    $testout = str_replace([' ', "\n", "\r", "\r\n"], '', $testout);
                    $resout = str_replace([' ', "\n", "\r", "\r\n"], '', $resout);

                    //答案错误
                    if ($testout == $resout) {
                        ++$actestnum;
                    }
                    //删除新建文件下当前输出
                    Base::deleteallfile($outpath);
                }
                //最后删除文件夹
                Base::deleteallfile($filepath);
                // AC               
                RedisQueue::send(Base::$redis_queue_update_oj_name, [
                    'problem_id' => $problem_id,
                    'is_ac' => true
                ]);
                if ($maxtime == '') {
                    $maxtime = '0';
                }
                if ($maxmemory == '') {
                    $maxmemory = '0';
                }
                $resscore = (int) ($actestnum * $onetestscore);
                if ($actestnum >= $alltestnum) {
                    $resscore = 100;
                }
                Db::table('contestrank')
                    ->where('id', $tem->id)
                    ->where('isdel', 0)
                    ->update(['score' => $resscore]);

                $hasac = Db::table('solveproblem')
                    ->where('problemid', $problem_id)
                    ->where('userid', $user_id)
                    ->where('language', $userlanguage)
                    ->where('isdel', 0)
                    ->exists();
                if (!$hasac && $resscore == 100) {
                    Db::table('solveproblem')->insert([
                        'userid' => $user_id,
                        'problemid' => $problem_id,
                        'time' => $now,
                        'code' => $code,
                        'language' => $userlanguage
                    ]);
                    Base::addAcMoney($user_id, $problem_name, $userlanguage);
                }
                if ($resscore == 100) {
                    Base::insertToDb('codehistory', [
                        'userid' => $user_id,
                        'status' => 'AC',
                        'problemid' => $problem_id,
                        'time' => $now,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                } else {
                    Base::insertToDb('codehistory', [
                        'userid' => $user_id,
                        'status' => '答案错误',
                        'problemid' => $problem_id,
                        'time' => $now,
                        'usetime' => $maxtime,
                        'usememory' => $maxmemory,
                        'code' => $code,
                        'language' => $userlanguage,
                        'contestid' => $contest_id
                    ]);
                }
                $user_db = Base::getUserData($user_id);
                $msg = '用户<strong>【' . ($user_db->name ?? '') . '】</strong>在竞赛<strong>【' . $contest_db->name . '】</strong>中的赛题<strong>【' . $problem_db->problemName . '】</strong>中得 </strong>' . $resscore . '<strong> 分';
                $msg_id = Base::insertToDb(
                    'privatechat',
                    [
                        'post_user_id' => $robot_db->id,
                        'get_user_id' => $my_aid,
                        'msg' => $msg,
                        'time' => $now
                    ]
                );
                Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
                    'id' => Base::getChatUserUidById($msg_id),
                    'type' => ChatBase::$type_private_chat_name,
                    'msgtype' => 'private_chat',
                    'post_user_id' => Base::getChatUserUidById($robot_db->id),
                    'get_user_id' => Base::getChatUserUidById($my_aid),
                    'name' => $robot_db->name,
                    'headimage' => $robot_db->headimage,
                    'msg' => $msg,
                    'time' => $now
                ]));
            }

            // 更新排名
            \app\controller\Contest::sendUpdateRankMQ($contest_id);

            $user = Db::table('joincontest')
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->pluck('userid');
            foreach ($user as &$tem) {
                $redis4->del('Contest' . $contest_id . 'problemdata' . $tem);
            }

            $msg = '提醒：竞赛<strong>【' . $contest_db->name . '】</strong>重新计算排名任务已下达！';

            $msg_id = Base::insertToDb(
                'privatechat',
                [
                    'post_user_id' => $robot_db->id,
                    'get_user_id' => $my_aid,
                    'msg' => $msg,
                    'time' => $now
                ]
            );
            Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_private_chat_name,
                'msgtype' => 'private_chat',
                'post_user_id' => Base::getChatUserUidById($robot_db->id),
                'get_user_id' => Base::getChatUserUidById($my_aid),
                'name' => $robot_db->name,
                'headimage' => $robot_db->headimage,
                'msg' => $msg,
                'time' => $now
            ]));
            ChatBase::updateNoLookNum(ChatBase::$type_private_chat_name, $robot_db->id, $my_aid);
        }
        return '题目<strong>【' . $problem_name . '】</strong>所在竞赛均重判结束！';
    }

    /**
     * 获取管理员用户名单
     * @param string $reply 消息
     */
    static private function loadAdminRootUser(&$reply)
    {
        $user_db = Db::table('user')
            ->where('isdel', 0)
            ->where('grade', '>', 1)
            ->orderBy('grade', 'desc')
            ->orderBy('id', 'asc')
            ->select('email', 'name', 'grade')
            ->get();
        foreach ($user_db as &$t) {
            $reply .= '用户：' . $t->name . '（邮箱：' . $t->email . '）的权限为：' . ($t->grade == 3 ? '超级管理员' : ($t->grade == 2 ? '管理员' : '用户')) . "\n";
        }
    }

    /**
     * 重置全站用户头像
     * @param string $reply 消息
     */
    static private function resetUserHeadimage(&$reply)
    {
        $image_list = Image::getImageList();
        $image_count = sizeof($image_list);
        if (!$image_count) {
            $reply = '暂无图片已跳过更新!';
            return;
        }
        $robot_email = Base::getRobotEmail();
        for ($i = 0; $i < $image_count; ++$i) {
            Db::table('user')
                ->where('email', $robot_email)
                ->whereRaw('id % ? = ?', [$image_count, $i])
                ->update([
                    'headimage' => $image_list[rand(0, $image_count - 1)]
                ]);
        }
        $user_db = Db::table('user')
            ->where('email', '!=', $robot_email)
            ->orderBy('id', 'desc')
            ->select('id', 'email')
            ->get();
        foreach ($user_db as &$tem) {
            $email_image = Base::getEmailImageToLtppUrl($tem->email);
            Db::table('user')
                ->where('id', $tem->id)
                ->update([
                    'headimage' => $email_image
                ]);
        }
        Base::clearAllUserDataRedis();
        $reply = '全站用户头像已设置成默认头像';
    }

    /**
     * 设置全站用户图片背景
     * @param string $reply 消息
     * @param string $url 图片地址
     */
    static private function resetUserBkImage(&$reply, &$url = '')
    {
        if (!$url) {
            $url = '';
        }
        Db::table('user')->update(['bkimage' => $url]);
        Base::clearAllUserDataRedis();
        $reply = '全站用户图片背景设置完成';
    }

    /**
     * 设置全站用户视频背景
     * @param string $reply 消息
     * @param string $url 视频地址
     */
    static private function resetUserBkVideo(&$reply, &$url = '')
    {
        if (!$url) {
            $url = '';
        }
        Db::table('user')->update(['bkvideo' => $url]);
        Base::clearAllUserDataRedis();
        $reply = '全站用户视频背景设置完成';
    }

    /**
     * ChatGPT回答
     * @param string $msg 用户的消息
     * @param string $reply 消息
     */
    static private function gptAnswer($userid, $msg, &$reply)
    {
        $is_root = Base::judgeIsRoot($userid);
        $user_data = Base::getUserData($userid);
        $money = $user_data->money;
        $res_msg = '';
        if ($money < PrivateRobot::$one_msg_cost && !$is_root) {
            $reply = '机器人通知：余额不足,请充值！';
            $res_msg = '(每次成功提问并得到回答花费' . PrivateRobot::$one_msg_cost . '学虫币)';
            return $res_msg;
        }
        $msg = '请使用中文回答，' . $msg;
        $reply = PrivateChat::gptSend($userid, $msg);

        if (!$reply) {
            $res_msg = PrivateChat::$gpt_err_msg;
        } else if (!$is_root) {
            Db::table('user')
                ->where('id', $userid)
                ->decrement('money', PrivateRobot::$one_msg_cost);
            Base::updateUserDataRedis($userid);
            $user_data = Base::getUserData($userid);
            $money = rtrim(rtrim($user_data->money, '0'), '.');
            $res_msg = "\n\n" . '(本次提问花费：' . PrivateRobot::$one_msg_cost . '学虫币，当前账户余额：' . $money . '学虫币)';
        }
        return $res_msg;
    }

    /**
     * 调用GPT
     */
    static public function gptSend($userid, $msg)
    {
        try {
            if (!$userid || !$msg) {
                return '';
            }
            $user_data = Base::getUserData($userid);
            if (!$user_data) {
                return '';
            }
            $user_name = $user_data->name;
            $robot = Base::getRobotId();
            $time = date('Y-m-d H:i:s', time());
            $history = Db::table('privatechat')
                ->orWhere(function ($query) use ($userid, $robot) {
                    $query
                        ->where('post_user_id', $userid)
                        ->where('get_user_id', $robot)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($userid, $robot) {
                    $query
                        ->where('post_user_id', $robot)
                        ->where('get_user_id', $userid)
                        ->where('isdel', 0);
                })
                ->select('id', 'post_user_id', 'get_user_id', 'msg')
                ->orderBy('id', 'desc')
                ->limit(PrivateChat::$gpt_chat_history_limit)
                ->get();
            $msg_list = [];
            foreach ($history as &$t) {
                if ($t->msg == PrivateChat::$gpt_err_msg) {
                    continue;
                }
                $msg_list[] = [
                    'role' => $t->post_user_id == $userid && $t->get_user_id == $robot ? 'user' : 'system',
                    'content' => $t->msg
                ];
            }
            $msg_list = array_reverse($msg_list);
            $msg_list[] = [
                'role' => 'user',
                'content' => $msg
            ];

            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => $msg_list,
                'temperature' => 0.9,
                'stream' => false,
            ];

            /**
             * 官方API接口调用
             */
            $gpt_api_url = Base::getChatGptUrl();
            $key_list = Base::getChatGptKeyList();
            foreach ($key_list as &$api_key) {
                $headers = [
                    'Content-Type:application/json',
                    'Authorization:Bearer ' . $api_key
                ];
                $result = Base::postRequest($gpt_api_url, $headers, $data, true);
                Robot::sendChatToOneUserMsgAndEmail(
                    Base::getRobotId(),
                    '<strong>' . $time . ' ' . $user_name
                        . ' 调用GPT详情</strong><br><strong>用户问题</strong><br>'
                        . $msg . '<br><strong>调用GPT回答</strong><br><pre style="white-space:pre-wrap;word-wrap:break-word;">'
                        . ($result ? json_encode(json_decode($result, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '调用GPT失败！')
                        . '</pre>'
                );
                $result = json_decode($result, true);
                if (isset($result['choices']) && sizeof($result['choices']) > 0 && isset($result['choices'][0]['message']) && isset($result['choices'][0]['message']['content'])) {
                    return $result['choices'][0]['message']['content'];
                }
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '调用GPT出错：' .  $e->getMessage());
        }
        return '';
    }

    /**
     * 机器人自动消息回复root用户
     * @param object $user_db 机器人的数据库数据
     * @param object $db_my 用户的数据库个人数据
     * @param string $client_id 客户端id
     * @param string $msg 用户发给机器人的消息
     */
    static protected function rootSendToRobot(&$user_db, &$db_my, &$client_id, &$msg)
    {
        $now = date('Y-m-d H:i:s', time());
        $reply = '';
        $do = '';
        for ($i = 0; $i < strlen($msg); ++$i) {
            if ($msg[$i] == ' ') {
                for ($j = $i + 1; $j < strlen($msg); ++$j) {
                    $reply .= $msg[$j];
                }
                break;
            }
            $do .= $msg[$i];
        }
        $res_msg = '';
        switch ($do) {
            case '1':
                $reply = '用户总数为：' . Db::table('user')->where('isdel', 0)->count();
                break;
            case '2':
                $reply = '文章总数为：' . Db::table('article')->where('isdel', 0)->count();
                break;
            case '3':
                $reply = '题库总数为：' . Db::table('oj')->where('isdel', 0)->count();
                break;
            case '4':
                $reply = '竞赛总数为：' . Db::table('contest')->where('isdel', 0)->count();
                break;
            case '5':
                $reply = '代码运行记录总数为：' . Db::table('codehistory')->where('isdel', 0)->count();
                break;
            case '6':
                $reply = 'public文件夹大小为：' . Base::getFileSize('/home/LTPP/public/') / Base::$one_mb_size . 'MB';
                break;
            case '7':
                $reply = '私聊的消息总数为：' . Db::table('privatechat')->where('isdel', 0)->count() . "\n" . '群聊的消息总数为：' . Db::table('groupchat')->where('isdel', 0)->count();
                break;
            case '8':
                PrivateRobot::loadLinuxMore($reply);
                break;
            case '9':
                PrivateRobot::loadAdminRootUser($reply);
                break;
            case '10':
                Db::table('user')
                    ->orderBy('id', 'asc')
                    ->update([
                        'isusemusic' => 1
                    ]);
                Base::clearAllUserDataRedis();
                $reply = '开启成功';
                break;
            case '11':
                PrivateRobot::deleteLogMore($reply);
                break;
            case '12':
                Base::deleteAllFile('/tmp');
                Base::judgeCreatPath('/tmp');
                Base::chmodFile('/tmp', 0777);
                $reply = '清空tmp目录成功';
                break;
            case '13':
                $all_user = Db::table('user')
                    ->where('isdel', 0)
                    ->select('id', 'email')
                    ->get();
                $insert_data = [];
                $i = 0;
                $reply = '来自' . $user_db->name . '的提醒：' . "\n" . $reply;
                Gateway::sendToAll(json_encode([
                    'msgtype' => 'notice',
                    'name' => $user_db->name,
                    'msg' => $reply,
                    'time' => $now
                ]));
                foreach ($all_user as &$tem) {
                    ++$i;
                    if ($i % 888 == 0) {
                        Db::table('privatechat')->insert($insert_data);
                        $insert_data = [];
                    }
                    $insert_data[] = [
                        'post_user_id' => $user_db->id,
                        'get_user_id' => $tem->id,
                        'msg' => $reply,
                        'time' => $now
                    ];
                    ChatBase::updateNoLookNum(ChatBase::$type_private_chat_name, $user_db->id, $tem->id);
                    $has = Db::table('privateuser')
                        ->where('get_user_id', $tem->id)
                        ->where('post_user_id', $user_db->id)
                        ->where('isdel', 0)
                        ->exists();
                    if (!$has) {
                        Base::insertToDb('privateuser', [
                            'post_user_id' => $user_db->id,
                            'get_user_id' => $tem->id,
                            'isdel' => 0,
                            'time' => $now
                        ]);
                    }
                }
                if (!empty($insert_data)) {
                    Db::table('privatechat')->insert($insert_data);
                }
                $insert_data = [];
                return;
            case '14':
                PrivateRobot::resetUserHeadimage($reply);
                break;
            case '15':
                Db::table('user')
                    ->orderBy('id', 'asc')
                    ->update([
                        'isusemusic' => 0
                    ]);
                Base::clearAllUserDataRedis();
                $reply = '关闭成功';
                break;
            case '16':
                $url = $reply;
                PrivateRobot::resetUserBkImage($reply, $url);
                break;
            case '17':
                $url = $reply;
                PrivateRobot::resetUserBkVideo($reply, $url);
                break;
            case '18':
                $reply = PrivateRobot::rejudgeOneProblem($client_id, $db_my->id, $reply, $user_db);
                break;
            case '19':
                $reply = Ssh::buy($db_my->id, $reply);
                break;
            case '20':
                $reply = '【' . $now . '】在线客户端总数：' . Gateway::getAllUidCount();
                break;
            case '21':
                $new_password = md5(uniqid() . mt_rand(1, 100000) . time());
                Db::table('user')
                    ->where('email', Base::getRobotEmail())
                    ->update([
                        'password' => Base::passwordEncryption($new_password)
                    ]);
                Base::clearAllUserDataRedis();
                $reply = '重置所有机器人账号的密码完成【新密码：' . $new_password . '】';
                break;
            case '22':
                $robot_email = Base::getRobotEmail();
                $robot_num = Db::table('user')
                    ->where('email', $robot_email)
                    ->where('isdel', 0)
                    ->count();
                $people_num = Db::table('user')
                    ->where('email', '!=', $robot_email)
                    ->where('isdel', 0)
                    ->count();
                $reply = '机器人用户个数【' . $robot_num . '】<br>非机器人用户个数【' . $people_num . '】';
                break;
            case '帮助':
                $reply = PrivateChat::$robot_root_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '？':
                $reply = PrivateChat::$robot_root_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case 'help':
                $reply = PrivateChat::$robot_root_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '?':
                $reply = PrivateChat::$robot_root_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            default:
                $res_msg = PrivateRobot::gptAnswer($db_my->id, $msg, $reply);
                break;
        }
        $msg_id = Base::insertToDb(
            'privatechat',
            [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'msg' => $reply,
                'time' => $now
            ]
        );
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'id' => Base::getChatUserUidById($msg_id),
            'type' => ChatBase::$type_private_chat_name,
            'msgtype' => 'private_chat',
            'post_user_id' => Base::getChatUserUidById($user_db->id),
            'get_user_id' => Base::getChatUserUidById($db_my->id),
            'name' => $user_db->name,
            'headimage' => $user_db->headimage,
            'msg' => $reply . $res_msg,
            'time' => $now
        ]));
        $has = Db::table('privateuser')
            ->where('get_user_id', $db_my->id)
            ->where('post_user_id', $user_db->id)
            ->where('isdel', 0)
            ->exists();
        if (!$has) {
            Base::insertToDb('privateuser', [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'isdel' => 0,
                'time' => $now
            ]);
        }
    }

    /**
     * 机器人自动消息回复admin用户
     * @param object $user_db 机器人的数据库数据
     * @param object $db_my 用户的数据库个人数据
     * @param string $client_id 客户端id
     * @param string $msg 用户发给机器人的消息
     */
    static protected function adminSendToRobot(&$user_db, &$db_my, &$client_id, &$msg)
    {
        $now = date('Y-m-d H:i:s', time());
        $reply = '';
        $do = '';
        for ($i = 0; $i < strlen($msg); ++$i) {
            if ($msg[$i] == ' ') {
                for ($j = $i + 1; $j < strlen($msg); ++$j) {
                    $reply .= $msg[$j];
                }
                break;
            }
            $do .= $msg[$i];
        }

        $res_msg = '';
        switch ($do) {
            case '1':
                $reply = PrivateRobot::rejudgeOneProblem($client_id, $db_my->id, $reply, $user_db);
                break;
            case '2':
                $reply = Ssh::buy($db_my->id, $reply);
                break;
            case '帮助':
                $reply = PrivateChat::$robot_admin_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '？':
                $reply = PrivateChat::$robot_admin_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case 'help':
                $reply = PrivateChat::$robot_admin_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '?':
                $reply = PrivateChat::$robot_admin_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            default:
                $res_msg = PrivateRobot::gptAnswer($db_my->id, $msg, $reply);
                break;
        }
        $msg_id = Base::insertToDb(
            'privatechat',
            [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'msg' => $reply,
                'time' => $now
            ]
        );
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'id' => Base::getChatUserUidById($msg_id),
            'type' => ChatBase::$type_private_chat_name,
            'msgtype' => 'private_chat',
            'post_user_id' => Base::getChatUserUidById($user_db->id),
            'get_user_id' => Base::getChatUserUidById($db_my->id),
            'name' => $user_db->name,
            'headimage' => $user_db->headimage,
            'msg' => $reply . $res_msg,
            'time' => $now
        ]));
        $has = Db::table('privateuser')
            ->where('get_user_id', $db_my->id)
            ->where('post_user_id', $user_db->id)
            ->where('isdel', 0)
            ->exists();
        if (!$has) {
            Base::insertToDb('privateuser', [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'isdel' => 0,
                'time' => $now
            ]);
        }
    }

    /**
     * 机器人自动消息回复普通用户
     * @param object $user_db 机器人的数据库数据
     * @param object $db_my 用户的数据库个人数据
     * @param string $client_id 客户端id
     * @param string $msg 用户发给机器人的消息
     */
    static protected function userSendToRobot(&$user_db, &$db_my, &$client_id, &$msg)
    {
        $now = date('Y-m-d H:i:s', time());
        $reply = '';
        $do = '';
        for ($i = 0; $i < strlen($msg); ++$i) {
            if ($msg[$i] == ' ') {
                for ($j = $i + 1; $j < strlen($msg); ++$j) {
                    $reply .= $msg[$j];
                }
                break;
            }
            $do .= $msg[$i];
        }
        $res_msg = '';
        switch ($do) {
            case '1':
                $reply = Ssh::buy($db_my->id, $reply);
                break;
            case '帮助':
                $reply = PrivateChat::$robot_user_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '？':
                $reply = PrivateChat::$robot_user_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case 'help':
                $reply = PrivateChat::$robot_user_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            case '?':
                $reply = PrivateChat::$robot_user_default . '（仅限购一个，价格' . Ssh::$price . '学虫币）';
                break;
            default:
                $res_msg = PrivateRobot::gptAnswer($db_my->id, $msg, $reply);
                break;
        }

        $msg_id = Base::insertToDb(
            'privatechat',
            [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'msg' => $reply,
                'time' => $now
            ]
        );

        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'id' => Base::getChatUserUidById($msg_id),
            'type' => ChatBase::$type_private_chat_name,
            'msgtype' => 'private_chat',
            'post_user_id' => Base::getChatUserUidById($user_db->id),
            'get_user_id' => Base::getChatUserUidById($db_my->id),
            'name' => $user_db->name,
            'headimage' => $user_db->headimage,
            'msg' => $reply . $res_msg,
            'time' => $now
        ]));

        $has = Db::table('privateuser')
            ->where('get_user_id', $db_my->id)
            ->where('post_user_id', $user_db->id)
            ->where('isdel', 0)
            ->exists();
        if (!$has) {
            Base::insertToDb('privateuser', [
                'post_user_id' => $user_db->id,
                'get_user_id' => $db_my->id,
                'isdel' => 0,
                'time' => $now
            ]);
        }
    }
}
