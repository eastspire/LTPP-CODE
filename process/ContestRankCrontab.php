<?php
/*
 * @Author: wmzn-ltpp 1491579574@qq.com
 * @Date: 2023-08-07 18:43:59
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 10:33:30
 * @FilePath: \LTPP-CODE\process\ContestRankCrontab.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */

namespace process;

use app\controller\Base;
use app\controller\Contest;
use app\controller\Robot;
use Exception;
use stdClass;
use support\Db;
use support\Redis;
use Workerman\Crontab\Crontab;
use Webman\RedisQueue\Redis as RedisQueue;

class ContestRankCrontab
{
    static $times = 0;

    static $limit = 60;

    public function onWorkerStart()
    {
        // 每一秒钟执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
                if (ContestRankCrontab::$times > ContestRankCrontab::$limit) {
                    ContestRankCrontab::$times = 0;
                }
                $redis24 = Redis::connection('db24');
                $arr = $redis24->lrange(Contest::$redis_array_name, 0, -1);
                $redis24->del(Contest::$redis_array_name);
                $obj = new stdClass();
                foreach ($arr as &$key) {
                    if (isset($obj->$key) && $obj->$key) {
                        continue;
                    }
                    $obj->$key = true;
                    // 发布任务
                    RedisQueue::send(Base::$redis_queue_contest_rank_name, ['contest_id' => $key]);
                }
                $obj = null;
                if (ContestRankCrontab::$times % ContestRankCrontab::$limit == 0) {
                    $contest_list = Db::table('contest')
                        ->where('isdel', 0)
                        ->pluck('id')
                        ->toArray();
                    foreach ($contest_list as &$contest_id) {
                        // 发布任务
                        RedisQueue::send(Base::$redis_queue_contest_rank_name, ['contest_id' => $contest_id]);
                    }
                    ContestRankCrontab::$times = 1;
                }
                ContestRankCrontab::$times++;
            } catch (Exception $e) {
                // 发送通知
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【ContestRankCrontab】** 运行错误：' . $e->getMessage());
            }
        });
    }
}
