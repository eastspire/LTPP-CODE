<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-10-09 00:01:51
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-11-13 18:36:49
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

class RobotContestCrontab
{
    /**
     * 获取进行中的比赛列表
     * @return {*} $db
     */
    private function getRunningContestList($now = '')
    {
        try {
            if (!$now) {
                $now = time();
            }
            $now = date('Y-m-d H:i:s', $now);
            $db = Db::table('contest')
                ->where('begin', '<=', $now)
                ->where('end', '>=', $now)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->select('id', 'begin', 'end')
                ->get();
            return $db;
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【RobotContestCrontab】</strong>运行错误：' . $e->getMessage());
        }
        return [];
    }

    public function onWorkerStart()
    {
        // 每一秒钟执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
                $now = time();
                $contest_list = $this->getRunningContestList($now);
                $redis27 = \support\Redis::connection('db27');
                foreach ($contest_list as &$one_contest) {
                    if (\app\queue\redis\RobotContest::judgeHasJudgeContest($redis27, $one_contest->id)) {
                        continue;
                    }
                    // 竞赛已开始秒数
                    $start_seconds = $now - strtotime($one_contest->begin);
                    if ($start_seconds < 0) {
                        // 竞赛未开始
                        continue;
                    }
                    $contest_run_time_seconds = strtotime($one_contest->end) - $now;
                    if ($contest_run_time_seconds < 0) {
                        // 竞赛结束不进行提交
                        continue;
                    }
                    RedisQueue::send(Base::$redis_queue_robot_contest_name, [
                        'contest_id' => $one_contest->id,
                    ]);
                }
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【RobotContestCrontab】</strong>运行错误：' . $e->getMessage());
            }
        });
    }
}
