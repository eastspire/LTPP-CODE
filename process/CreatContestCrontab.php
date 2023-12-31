<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 08:26:46
 * @FilePath: \LTPP-CODE\process\CreatContest.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace process;

use app\controller\Robot;
use app\controller\Base;
use app\controller\Contest;
use Workerman\Crontab\Crontab;
use support\Db;
use Exception;

class CreatContestCrontab
{
    /**
     * 获取题目
     * @return array $res
     */
    private function getProblemList()
    {
        $has_ac_pro_list = Db::table('contestrank')
            ->where('isdel', 0)
            ->where('score', 100)
            ->inRandomOrder()
            ->select('problemid')
            ->limit(Base::getSettingKeyData('default_contest_problem_num'))
            ->distinct()
            ->get();
        $res = [];
        foreach ($has_ac_pro_list as &$tem) {
            $res[] = $tem->problemid;
        }
        return $res;
    }

    /**
     * 获取竞赛时间
     * @return array [开始时间,结束时间]
     */
    private function getTimeList()
    {
        $default_contest_begin_time = Base::getSettingKeyData('default_contest_begin_time');
        $default_contest_duration = Base::getSettingKeyData('default_contest_duration');
        $begin = strtotime(date('Y-m-d', time())) + $default_contest_begin_time;
        $end = $begin + $default_contest_duration;
        $begin = date('Y-m-d H:i:s', $begin);
        $end = date('Y-m-d H:i:s', $end);
        return [$begin, $end];
    }

    /**
     * 获取参赛用户
     * @return array $user_list
     */
    private function getUserList()
    {
        $total = Db::table('user')
            ->where('isdel', 0)
            ->count();
        $default_contest_min_people_num = Base::getSettingKeyData('default_contest_min_people_num');
        $default_contest_max_people_num = Base::getSettingKeyData('default_contest_max_people_num');
        $min = min($default_contest_min_people_num, $default_contest_max_people_num);
        $max = max($default_contest_min_people_num, $default_contest_max_people_num);
        $defaultnum = rand($min, $max);
        if ($defaultnum > $total) {
            $defaultnum = $total;
        }
        $user_list = Db::table('user')
            ->where('email', Base::getRobotEmail())
            ->where('isdel', 0)
            ->limit($defaultnum)
            ->pluck('id')
            ->toArray();
        return $user_list;
    }

    /**
     * 获取竞赛内容描述
     * @param string $title
     * @return string $content
     */
    private function getContent($title = '')
    {
        $default_content = Base::getSettingKeyData('default_contest_content');
        $content = '## ' . $title . "\n\n" . $default_content;
        return $content;
    }

    /**
     * 插入竞赛
     * @param string $contest_title
     * @param string $contest_content
     * @param array $time_list
     * @param array $user_list
     * @param string $type
     */
    private function addContest($contest_title, $contest_content, $time_list, $user_list, $type)
    {
        $res_id = Base::insertToDb('contest', [
            'name' => $contest_title,
            'content' => $contest_content,
            'begin' => $time_list[0],
            'end' => $time_list[1],
            'creater' => 'root',
            'allpeople' => sizeof($user_list),
            'type' => $type,
            'createrid' => Base::getRootId(),
        ]);
        // 缓存竞赛
        Base::updateContestDataRedis($res_id);
        return $res_id;
    }

    /**
     * 插入问题
     * @param int $contest_id
     * @param array $pro_list
     */
    private function addProblem($contest_id, $pro_list)
    {
        $insert_pro_list = [];
        $cnt_i = 0;
        foreach ($pro_list as &$problem_id) {
            ++$cnt_i;
            $insert_pro_list[] = [
                'contestid' => $contest_id,
                'problemid' => $problem_id
            ];
            if ($cnt_i % 1000 == 0 && !empty($insert_pro_list)) {
                Db::table('contestproblem')
                    ->insert($insert_pro_list);
                $insert_pro_list = [];
            }
        }
        if (!empty($insert_pro_list)) {
            Db::table('contestproblem')
                ->insert($insert_pro_list);
        }
    }

    /**
     * 插入参赛用户
     * @param int $contest_id
     * @param array $user_list
     */
    private function addUser($contest_id, $user_list)
    {
        $cnt_i = 0;
        foreach ($user_list as &$tem) {
            ++$cnt_i;
            $insert_user[] = [
                'userid' => $tem,
                'contestid' => $contest_id,
                'totaltime' => 0,
            ];
            if ($cnt_i % 1000 == 0 && !empty($insert_user)) {
                Db::table('joincontest')
                    ->insert($insert_user);
                $insert_user = [];
            }
        }
        if (!empty($insert_user)) {
            Db::table('joincontest')
                ->insert($insert_user);
        }
    }

    public function onWorkerStart()
    {
        // 每天的0点执行，注意这里省略了秒位'00 0 * * *'
        new Crontab('00 0 * * *', function () {
            try {
                $pro_list = $this->getProblemList();
                $user_list = $this->getUserList();
                $time_list = $this->getTimeList();
                foreach (Contest::$contest_type_list as &$type) {
                    $contest_title = $type . '赛制竞赛';
                    $contest_content = $this->getContent($contest_title);
                    $res_id = $this->addContest($contest_title, $contest_content, $time_list, $user_list, $type);
                    $this->addProblem($res_id, $pro_list);
                    $this->addUser($res_id, $user_list);
                }
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【CreatContestCrontab】** 运行错误：' . $e->getMessage());
            }
        });
    }
}
