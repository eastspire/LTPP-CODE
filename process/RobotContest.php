<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-10-09 00:01:51
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-10 12:30:05
 * @FilePath: \LTPP-CODE\process\RobotContest.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */
namespace process;

use app\controller\Base;
use app\controller\Robot;
use Exception;
use support\Db;
use Workerman\Crontab\Crontab;
use Webman\RedisQueue\Redis as RedisQueue;

class RobotContest
{

    /**
     * 锁
     */
    static $lock = false;

    /**
     * 获取进行中的比赛列表
     * @return \Illuminate\Support\Collection $db
     */
    private function getRunningContestList()
    {
        $now = date('Y-m-d H:i:s', time());
        $db = Db::table('contest')
            ->where('begin', '<=', $now)
            ->where('end', '>=', $now)
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->select('id', 'begin', 'end')
            ->get();
        return $db;
    }

    public function onWorkerStart()
    {
        // 每一分钟执行一次
        new Crontab('0 */1 * * * *', function () {
            try {
                if (RobotContest::$lock) {
                    return;
                }
                RobotContest::$lock = true;
                $contest_list = $this->getRunningContestList();
                $redis27 = \support\Redis::connection('db27');
                foreach ($contest_list as &$one_contest) {
                    if (\app\queue\redis\RobotContest::judgeHasJudgeContest($redis27, $one_contest->id)) {
                        continue;
                    }
                    $contest_run_time_seconds = strtotime($one_contest->end) - time();
                    if ($contest_run_time_seconds < 0 || $contest_run_time_seconds > Base::$robot_contest_can_join_limit_contest_time) {
                        // 竞赛结束 或者 距离竞赛结束时间过长 不进行提交
                        continue;
                    }
                    RedisQueue::send(Base::$redis_queue_robot_contest_name, [
                        'contest_id' => $one_contest->id,
                    ]);
                }
                RobotContest::$lock = false;
            } catch (Exception $e) {
                RobotContest::$lock = false;
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程【RobotContest】运行错误：' . $e->getMessage());
            }
        });
    }
}