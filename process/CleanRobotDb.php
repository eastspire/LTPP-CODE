<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 12:55:19
 * @FilePath: \LTPP-CODE\process\CodeStatusCorrectCrontab.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace process;

use Workerman\Crontab\Crontab;
use app\controller\Base;
use support\Db;
use Exception;

class CleanRobotDb
{
    public function onWorkerStart()
    {
        // 每天凌晨三点执行一次
        new Crontab('00 3 * * *', function () {
            try {
                $robot_email = Base::$robot_email;
                $time = date('Y-m-d', time());

                Db::table('articlecomment')
                    ->where('isdel', 0)
                    ->where('time', '<', $time)
                    ->whereExists(function ($query) use ($robot_email) {
                        $query->select(Db::raw(1))
                            ->from('user')
                            ->where('user.email', $robot_email)
                            ->whereColumn('articlecomment.userid', 'user.id');
                    })
                    ->update([
                        'isdel' => 1
                    ]);

                Db::table('codehistory')
                    ->where('isdel', 0)
                    ->where('time', '<', $time)
                    ->whereExists(function ($query) use ($robot_email) {
                        $query->select(Db::raw(1))
                            ->from('user')
                            ->where('user.email', $robot_email)
                            ->whereColumn('codehistory.userid', 'user.id');
                    })
                    ->update([
                        'isdel' => 1
                    ]);

                Db::table('privatechat')
                    ->where('isdel', 0)
                    ->where('time', '<', $time)
                    ->whereExists(function ($query) use ($robot_email) {
                        $query->select(Db::raw(1))
                            ->from('user')
                            ->where('user.email', $robot_email)
                            ->whereColumn('privatechat.post_user_id', 'user.id');
                    })
                    ->update([
                        'isdel' => 1
                    ]);

                Db::table('solveproblem')
                    ->where('isdel', 0)
                    ->where('time', '<', $time)
                    ->whereExists(function ($query) use ($robot_email) {
                        $query->select(Db::raw(1))
                            ->from('user')
                            ->where('user.email', $robot_email)
                            ->whereColumn('solveproblem.userid', 'user.id');
                    })
                    ->update([
                        'isdel' => 1
                    ]);

                Db::table('usernotice')
                    ->where('isdel', 0)
                    ->where('time', '<', $time)
                    ->whereExists(function ($query) use ($robot_email, $time) {
                        $query->select(Db::raw(1))
                            ->from('user')
                            ->where('user.email', $robot_email)
                            ->whereColumn('usernotice.userid', 'user.id');
                    })
                    ->update([
                        'isdel' => 1
                    ]);
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【CleanRobotDb】</strong>运行错误：' . $e->getMessage());
            }
        });
    }
}
