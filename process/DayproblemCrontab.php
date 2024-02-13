<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 08:51:43
 * @FilePath: \LTPP-CODE\process\Dayproblem.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace process;

use app\controller\Robot;
use GatewayWorker\Lib\Gateway;
use Workerman\Crontab\Crontab;
use app\controller\Base;
use support\Db;
use Exception;

class DayproblemCrontab
{
    private function sendNotice()
    {
        try {
            $time = date('Y-m-d', time());
            $robot_data = Base::getUserData(Base::getRobotId());
            $msg = $time . '的每日一题已生成！别忘记打卡哟！';
            Gateway::sendToAll(json_encode([
                'msgtype' => 'notice',
                'name' => $robot_data->name,
                'msg' => '来自' . $robot_data->name . '的提醒：' . "\n" . $msg,
                'time' => date('Y-m-d H:i:s', time())
            ]));
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【DayproblemCrontab】</strong>运行错误：' . $e->getMessage());
        }
    }

    private function addDayProblem()
    {
        $db = Db::table('oj')
            ->where('isdel', 0)
            ->select('id')
            ->inRandomOrder()
            ->pluck('id')
            ->toArray();
        $total = Db::table('oj')
            ->where('isdel', 0)
            ->count();
        $cnt = 0;
        $has = Db::table('dayproblem')
            ->where('time', date('Y-m-d', time()))
            ->exists();
        // 没有记录则插入
        if (!$has) {
            foreach ($db as &$tem) {
                if (!Db::table('dayproblem')->where('problemid', $tem)->exists()) {
                    $res = Db::table('dayproblem')
                        ->insert([
                            'problemid' => $tem,
                            'time' => date('Y-m-d', time())
                        ]);
                    if ($res) {
                        $this->sendNotice();
                        break;
                    }
                }
                ++$cnt;
            }
        }
        if ($cnt >= $total) {
            foreach ($db as &$tem) {
                $has = Db::table('dayproblem')
                    ->where('time', date('Y-m-d', time()))
                    ->exists();
                if (!$has) {
                    $res = Db::table('dayproblem')
                        ->insert([
                            'problemid' => $tem,
                            'time' => date('Y-m-d', time())
                        ]);
                    if ($res) {
                        $this->sendNotice();
                    }
                }
                break;
            }
        }
    }

    public function onWorkerStart()
    {
        // 每天的0点执行，注意这里省略了秒位'00 0 * * *'
        new Crontab('00 0 * * *', function () {
            try {
                $this->addDayProblem();
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【DayproblemCrontab】</strong>运行错误：' . $e->getMessage());
            }
        });

        // 防止上面每天定时插入每日一题不生效，每6小时检查一次
        new Crontab('* */6 * * *', function () {
            try {
                $this->addDayProblem();
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【DayproblemCrontab】</strong>运行错误：' . $e->getMessage());
            }
        });
    }
}
