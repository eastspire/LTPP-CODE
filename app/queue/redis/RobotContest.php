<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-11-14 07:25:10
 * @FilePath: \LTPP-CODE\app\queue\redis\RobotContest.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
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

    static $lock = false;

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
     * 获取参赛的机器人
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
                ->select('email')
                ->first();
            if ($user) {
                $res[] = $one_person;
            }
        }
        return $res;
    }

    /**
     * 获取ContestRank代码ID
     * @param int $problem_id
     * @param int $page
     * @return int $contestrank_id
     */
    private function getCodeFromContestRank($problem_id = 0, $page = 1)
    {
        $can_total_score = (rand(0, 100) % 10 <= 2);
        if (!$can_total_score) {
            return 0;
        }
        $order_by = rand(0, 1) ? 'asc' : 'desc';
        $all = Db::table('contestrank')
            ->where('score', 100)
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->count();
        if ($all) {
            if ($page > $all) {
                $page = ($page % $all) + 1;
            }
            $db = Db::table('contestrank')
                ->where('score', 100)
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->select('id')
                ->orderBy('id', $order_by)
                ->paginate(1, '*', 'page', $page)
                ->items();
            if ($db) {
                return $db[0]->id;
            }
        }
        // 没有竞赛的题目的AC提交记录，后续走代码历史查询
        return 0;
    }

    /**
     * 获取CodeHistory代码ID
     * @param int $problem_id
     * @param int $page
     * @return int $contestrank_id
     */
    private function getCodeFromCodeHistory($problem_id = 0, $page = 1, $my_id = 0)
    {
        $can_total_score = (rand(0, 100) % 10 <= 2);
        $order_by = rand(0, 1) ? 'asc' : 'desc';
        if ($can_total_score) {
            $all = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('status', 'AC')
                ->where('isdel', 0)
                ->count();
            if ($all) {
                if ($page > $all) {
                    $page = ($page % $all) + 1;
                }
                $db = Db::table('codehistory')
                    ->where('problemid', $problem_id)
                    ->where('status', 'AC')
                    ->where('isdel', 0)
                    ->select('id')
                    ->orderBy('id', $order_by)
                    ->paginate(1, '*', 'page', $page)
                    ->items();
                if ($db) {
                    return $db[0]->id;
                }
            }
        }
        $all = Db::table('codehistory')
            ->where('problemid', $problem_id)
            ->where('status', '!=', 'AC')
            ->where('isdel', 0)
            ->count();
        if ($all) {
            if ($page > $all) {
                $page = ($page % $all) + 1;
            }
            $db = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('status', '!=', 'AC')
                ->where('isdel', 0)
                ->select('id')
                ->orderBy('id', $order_by)
                ->paginate(1, '*', 'page', $page)
                ->items();
            if ($db) {
                return $db[0]->id;
            }
        }

        // 没有题目的提交记录，就生成一个错误的答案提交记录
        $all = Db::table('codehistory')
            ->where('isdel', 0)
            ->count();
        $now = date('Y-m-d H:i:s', time());
        $language = rand(0, 1) ? 'C' : 'C++';
        $contestrank_db = null;
        if ($all) {
            if ($page > $all) {
                $page = ($page % $all) + 1;
            }
            $contestrank_db = Db::table('codehistory')
                ->where('isdel', 0)
                ->orderBy('id', $order_by)
                ->select('code', 'contestid', 'language')
                ->paginate(1, '*', 'page', $page)
                ->items();
            if ($contestrank_db) {
                $contestrank_db = $contestrank_db[0];
                $language = $contestrank_db->language;
            }
        }
        $id = Db::table('codehistory')
            ->insert([
                'userid' => $my_id,
                'problemid' => $problem_id,
                'language' => $language,
                'status' => '答案错误',
                'time' => $now,
                'usetime' => rand(10, 100),
                'usememory' => rand(10, 100),
                'code' => $contestrank_db ? $contestrank_db->code : '',
                'contestid' => $contestrank_db ? $contestrank_db->contestid : 0,
            ]);
        return $id;
    }

    /**
     * 从ContestRank获取的代码提交代码
     * @param int $contest_id
     * @param int $code_id
     * @param int $my_id
     */
    private function addCodeFromContestRank($contest_id = 0, $code_id = 0, $my_id = 0)
    {
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return;
        }
        $db = Db::table('contestrank')
            ->where('id', $code_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            return;
        }
        $now = date('Y-m-d H:i:s', time());
        if ($db->score == 100) {
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
                    'score' => $db->score,
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
                'status' => $db->score == 100 ? 'AC' : '答案错误',
                'time' => $now,
                'usetime' => rand(10, 100),
                'usememory' => rand(10, 100),
                'code' => $db->code,
                'contestid' => $contest_id,
            ]);
    }

    /**
     * 从CodeHistory获取的代码提交代码
     * @param int $contest_id
     * @param int $code_id
     * @param int $my_id
     */
    private function addCodeFromCodeHistory($contest_id = 0, $code_id = 0, $my_id = 0)
    {
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return;
        }
        $db = Db::table('codehistory')
            ->where('id', $code_id)
            ->where('isdel', 0)
            ->where('status', '!=', Base::$code_up_waiting)
            ->where('status', '!=', Base::$code_up_running)
            ->first();
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
            if (RobotContest::$lock) {
                return;
            }
            RobotContest::$lock = true;
            $now = date('Y-m-d H:i:s', time());
            $one_contest_id = $data['contest_id'] ?? 0;
            $redis27 = Redis::connection('db27');
            if (\app\queue\redis\RobotContest::judgeHasJudgeContest($redis27, $one_contest_id)) {
                return;
            }
            $this->addJudgeContest($redis27, $one_contest_id);
            $contest_db = Base::getContestData($one_contest_id);
            $people_list = $this->getPeopleList($one_contest_id);
            $problem_list = $this->getProblemList($one_contest_id);
            $this_contest_is_end = false;
            $submit_times = rand(4, 8);
            // 竞赛持续秒数
            $contest_run_time_seconds = strtotime($contest_db->end) - time();
            if ($contest_run_time_seconds < 0 || $contest_run_time_seconds > Base::$robot_contest_can_join_limit_contest_time) {
                // 竞赛结束 或者 距离竞赛结束时间过长 不进行提交
                return;
            }
            // 题目数目
            $problem_length = max(1, sizeof($problem_list));
            // 提交用户数目
            $people_length = max(1, sizeof($people_list));
            // 每题最少休眠秒数，注意向上取整
            $one_sleep_min_time = (int) ceil($contest_run_time_seconds / ($people_length * $problem_length * $submit_times * $problem_length));
            // 每题休眠秒数，呈梯度上升
            $one_sleep_time_list = [];
            for ($i = 1; $i <= $problem_length; ++$i) {
                $one_sleep_time_list[] = $one_sleep_min_time * $i;
            }
            for ($i = 0; $i < $submit_times; ++$i) {
                foreach ($problem_list as $one_problem_index => &$one_problem_id) {
                    foreach ($people_list as $one_person_index => &$one_person_id) {
                        // 先枚举用户
                        $now = date('Y-m-d H:i:s', time());
                        if ($now < $contest_db->begin || $now > $contest_db->end) {
                            $this_contest_is_end = true;
                            break;
                        }
                        if (rand(0, 1)) {
                            // 随机选择是否提交运行
                            $code_id = $this->getCodeFromContestRank($one_problem_id, $one_person_index + 1);
                            if ($code_id) {
                                // 竞赛中有AC提交记录
                                $this->addCodeFromContestRank($one_contest_id, $code_id, $one_person_id);
                            } else {
                                // 从代码历史查询记录，没有记录会自带生成一个记录
                                $code_id = $this->getCodeFromCodeHistory($one_problem_id, $one_person_index + 1, $one_person_id);
                                $this->addCodeFromCodeHistory($one_contest_id, $code_id, $one_person_id);
                            }
                            Contest::sendUpdateRankMQ($one_contest_id);
                        }
                        sleep($one_sleep_time_list[$one_problem_index]);
                    }
                    if ($this_contest_is_end) {
                        break;
                    }
                }
                if ($this_contest_is_end) {
                    break;
                }
            }
            RobotContest::$lock = false;
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '消息队列【RobotContest】运行出错：' . $e->getMessage());
            RobotContest::$lock = false;
        }
    }
}
