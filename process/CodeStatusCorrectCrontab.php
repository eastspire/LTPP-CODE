<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: ltpp-universe 1491579574@qq.com
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

// 进程停止导致一些程序状态一直等待中或者运行中
// 此进程负责重新运行这些代码，更新代码状态

class CodeStatusCorrectCrontab
{
    public function onWorkerStart()
    {
        // 每天凌晨一点执行一次
        new Crontab('00 1 * * *', function () {
            try {
                $time = max(0, time() - max(Base::$redis_timeout, (int) Base::getSettingKeyData('idemaxtime')));
                $db = Db::table('codehistory')
                    ->orWhere(function ($query) use ($time) {
                        $query
                            ->where('status', Base::$code_up_waiting)
                            ->where('time', '<=', date('Y-m-d H:i:s', $time))
                            ->where('isdel', 0);
                    })
                    ->orWhere(function ($query) use ($time) {
                        $query
                            ->where('status', Base::$code_up_running)
                            ->where('time', '<=', date('Y-m-d H:i:s', $time))
                            ->where('isdel', 0);
                    })
                    ->select('id')
                    ->get();
                foreach ($db as &$tem) {
                    // 使用单条记录更新，防止锁表
                    Db::table('codehistory')
                        ->where('id', $tem->id)
                        ->update([
                            'status' => Base::$code_run_running_wrong
                        ]);
                }
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【CodeStatusCorrectCrontab】</strong>运行出错：' . $e->getMessage());
            }
        });
    }
}
