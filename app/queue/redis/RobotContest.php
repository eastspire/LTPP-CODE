<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-09 08:49:35
 * @FilePath: \LTPP-CODE\app\queue\redis\RobotContest.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
use app\controller\Codehistory;
use app\controller\Contest;
use app\controller\Robot;
use Exception;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Consumer;

class RobotContest implements Consumer
{
    // 要消费的队列名
    public $queue = 'robot_contest';

    static $code_all_code_db = [];
    static $code_all_ac_code_db = [];
    static $code_all_no_ac_code_db = [];

    /**
     * 获取赛题
     * @param int $contest_id
     * @return array $db
     */
    private function getProblemList($contest_id = 0)
    {
        $db = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('problemid')
            ->distinct()
            ->pluck('problemid')
            ->toArray();
        return $db;
    }

    /**
     * 获取参赛的机器人列表
     * @param int $contest_id
     * @return array $res
     */
    private function getPeopleList($contest_id = 0)
    {
        $res = [];
        $robot_email = Base::getRobotEmail();
        $db = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('userid')
            ->distinct()
            ->pluck('userid');
        foreach ($db as &$one_person) {
            $user = Db::table('user')
                ->where('id', $one_person)
                ->where('email', $robot_email)
                ->where('isdel', 0)
                ->exists();
            if ($user) {
                $res[] = $one_person;
            }
        }
        return $res;
    }

    /**
     * 获取CodeHistory代码ID
     * @param int $contest_begin
     * @param int $problem_id
     * @param int $my_id
     */
    private function getCodeFromCodeHistory($contest_begin = null, $problem_id = 0, $my_id = 0)
    {
        if (!$contest_begin || !$problem_id || !$my_id) {
            return;
        }
        if (!isset(RobotContest::$code_all_ac_code_db[$problem_id]) || !is_numeric(RobotContest::$code_all_ac_code_db[$problem_id])) {
            RobotContest::$code_all_ac_code_db[$problem_id] = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('time', '<', $contest_begin)
                ->where('status', 'AC')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->first();
        }
        if (!isset(RobotContest::$code_all_no_ac_code_db[$problem_id]) || !is_numeric(RobotContest::$code_all_no_ac_code_db[$problem_id])) {
            RobotContest::$code_all_no_ac_code_db[$problem_id] = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('time', '<', $contest_begin)
                ->where('status', '!=', Base::$code_up_waiting)
                ->where('status', '!=', Base::$code_up_running)
                ->where('status', '!=', 'AC')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->first();
        }
        if (!isset(RobotContest::$code_all_code_db[$problem_id]) || !is_numeric(RobotContest::$code_all_code_db[$problem_id])) {
            RobotContest::$code_all_code_db[$problem_id] = Db::table('codehistory')
                ->where('status', '!=', Base::$code_up_waiting)
                ->where('status', '!=', Base::$code_up_running)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->first();
        }
        // 没有题目的提交记录，就生成一个错误的答案提交记录
        if (!RobotContest::$code_all_code_db[$problem_id]) {
            $now = date('Y-m-d H:i:s', time());
            $language = rand(0, 1) ? 'C' : 'C++';
            $code_id = Base::insertToDb('codehistory', [
                'userid' => $my_id,
                'problemid' => $problem_id,
                'language' => $language,
                'status' => '答案错误',
                'time' => $now,
                'usetime' => rand(10, 100),
                'usememory' => rand(10, 100),
                'code' => '',
                'contestid' => 0,
            ]);
            RobotContest::$code_all_code_db[$problem_id] = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('status', '!=', Base::$code_up_waiting)
                ->where('status', '!=', Base::$code_up_running)
                ->where('isdel', 0)
                ->first();
        }
        if (!RobotContest::$code_all_code_db[$problem_id]) {
            return;
        }
        if (!RobotContest::$code_all_ac_code_db[$problem_id]) {
            RobotContest::$code_all_ac_code_db[$problem_id] = RobotContest::$code_all_code_db[$problem_id];
        }
        if (!RobotContest::$code_all_no_ac_code_db[$problem_id]) {
            RobotContest::$code_all_no_ac_code_db[$problem_id] = RobotContest::$code_all_code_db[$problem_id];
        }
    }

    /**
     * 从CodeHistory获取的代码提交代码
     * @param int $contest_id
     * @param int $problem_id
     * @param int $my_id
     */
    private function addCodeFromCodeHistory($contest_id = 0, $problem_id = 0, $my_id = 0)
    {
        if (!$contest_id || !$problem_id || !$my_id) {
            return;
        }
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return;
        }
        $is_ac = (rand(0, 100) <= 88);
        $db = RobotContest::$code_all_no_ac_code_db[$problem_id];
        if ($is_ac) {
            $db = RobotContest::$code_all_ac_code_db[$problem_id];
        }
        if (!$db) {
            return;
        }
        $now = date('Y-m-d H:i:s', time());
        if ($db->status == 'AC') {
            Db::table('user')
                ->where('id', $my_id)
                ->where('isdel', 0)
                ->increment('acnum', 1);
            $has = Db::table('solveproblem')
                ->where('userid', $my_id)
                ->where('problemid', $db->problemid)
                ->where('language', $db->language)
                ->where('isdel', 0)
                ->exists();
            if (!$has) {
                Db::table('solveproblem')
                    ->insert([
                        'userid' => $my_id,
                        'problemid' => $db->problemid,
                        'time' => $now,
                        'language' => $db->language,
                        'code' => $db->code,
                    ]);
                $problem_db = Base::getOjData($db->problemid);
                if ($problem_db) {
                    Base::addAcMoney($my_id, $problem_db->problemName, $db->language);
                }
            }
        }
        $now = date('Y-m-d H:i:s', time());
        if ($now >= $contest_db->begin && $now <= $contest_db->end) {
            Db::table('contestrank')
                ->insert([
                    'userid' => $my_id,
                    'problemid' => $db->problemid,
                    'language' => $db->language,
                    'score' => $db->status == 'AC' ? 100 : 0,
                    'submittime' => $now,
                    'code' => $db->code,
                    'contestid' => $contest_id,
                ]);
        }
        Db::table('codehistory')
            ->insert([
                'userid' => $my_id,
                'problemid' => $db->problemid,
                'language' => $db->language,
                'status' => $db->status == '正常运行' ? '答案错误' : $db->status,
                'time' => $now,
                'usetime' => rand(10, 100),
                'usememory' => rand(10, 100),
                'code' => $db->code,
                'contestid' => $contest_id,
            ]);
    }

    /**
     * 判断机器人是否已经提交该竞赛
     */
    static public function judgeHasJudgeContest($redis27 = null, $contest_id = 0)
    {
        $key = Base::$robot_contest_redis_front . $contest_id;
        if ($redis27->exists($key)) {
            return true;
        }
        $has = Db::table('robotcontestfinish')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->exists();
        if ($has) {
            $redis27->setNx($key, 1);
            return true;
        }
        return false;
    }

    /**
     * 判断添加机器人提交该竞赛
     */
    private function addJudgeContest($redis27 = null, $contest_id = 0)
    {
        $key = Base::$robot_contest_redis_front . $contest_id;
        $redis27->setNx($key, 1);
        $has = Db::table('robotcontestfinish')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->exists();
        if ($has) {
            return;
        }
        $has = Db::table('robotcontestfinish')
            ->where('contestid', $contest_id)
            ->orderBy('id', 'desc')
            ->select('id')
            ->first();
        if ($has) {
            Db::table('robotcontestfinish')
                ->where('id', $has->id)
                ->update([
                    'isdel' => 0
                ]);
        } else {
            Db::table('robotcontestfinish')
                ->insert([
                    'contestid' => $contest_id
                ]);
        }
    }

    // 消费
    public function consume($data)
    {
        try {
            // 初始化
            RobotContest::$code_all_code_db = [];
            RobotContest::$code_all_ac_code_db = [];
            RobotContest::$code_all_no_ac_code_db = [];
            $now_time = time();
            $now = date('Y-m-d H:i:s', $now_time);
            $one_contest_id = $data['contest_id'] ?? 0;
            $redis27 = Redis::connection('db27');
            // 判断是否加锁，防止机器人重复执行一场竞赛
            if (\app\queue\redis\RobotContest::judgeHasJudgeContest($redis27, $one_contest_id)) {
                return;
            }
            $contest_db = Base::getContestData($one_contest_id);
            // 竞赛已开始秒数
            $start_seconds = $now_time - strtotime($contest_db->begin);
            if ($start_seconds < 0) {
                // 竞赛未开始
                return;
            }
            // 加锁，防止机器人重复执行一场竞赛
            $this->addJudgeContest($redis27, $one_contest_id);
            // 机器人先等待，不可立马答题
            $after_begin_sleep_seconds = ceil(Base::$robot_contest_start_after_begin_seconds - $start_seconds);
            if ($after_begin_sleep_seconds > 0) {
                sleep($after_begin_sleep_seconds);
            }
            $problem_list = $this->getProblemList($one_contest_id);
            // 题目数目
            $problem_length = sizeof($problem_list);
            if ($problem_length <= 0) {
                return;
            }
            $this_contest_is_end = false;
            // 提交次数
            $submit_times = rand(4, 6);
            // 竞赛距离结束剩余的秒数
            $contest_run_time_seconds = strtotime($contest_db->end) - $now_time;
            if ($contest_run_time_seconds < 0) {
                // 竞赛结束不进行提交
                return;
            }
            // 提交用户数目
            $people_list = $this->getPeopleList($one_contest_id);
            $people_length = sizeof($people_list);
            if ($people_length <= 0) {
                return;
            }
            // 每题最少休眠毫秒数
            $one_sleep_min_time = min((int)Base::getSettingKeyData('default_contest_submit_sleep_time'), ($contest_run_time_seconds * 1000) / ($people_length * $submit_times * $problem_length));
            // 每题休眠毫秒数，呈梯度上升
            $one_sleep_time_list = [];
            for ($i = 1; $i <= $problem_length; ++$i) {
                $one_sleep_time_list[] = $one_sleep_min_time / max(1, $problem_length + 1 - $i);
            }
            for ($i = 0; $i < $submit_times; ++$i) {
                foreach ($problem_list as $one_problem_index => &$one_problem_id) {
                    foreach ($people_list as &$one_person_id) {
                        // 先枚举用户
                        $now = date('Y-m-d H:i:s', $now_time);
                        if ($now < $contest_db->begin || $now > $contest_db->end) {
                            $this_contest_is_end = true;
                            break;
                        }
                        if (rand(0, 100) <= 88) {
                            // 从代码历史查询记录，没有记录会自带生成一个记录
                            $this->getCodeFromCodeHistory($contest_db->begin, $one_problem_id, $one_person_id);
                            $this->addCodeFromCodeHistory($one_contest_id, $one_problem_id, $one_person_id);
                            Contest::sendUpdateRankMQ($one_contest_id);
                        }
                        // 休眠毫秒数
                        usleep(ceil($one_sleep_time_list[$one_problem_index]));
                    }
                    if ($this_contest_is_end) {
                        break;
                    }
                }
                if ($this_contest_is_end) {
                    break;
                }
            }
        } catch (Exception $e) {
            $title = 'RobotContest消息队列异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsg(Base::getRootId(), '#### ' . $title . "\n" . $content);
        }
    }
};
