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
    public function onWorkerStart()
    {
        // 每一秒钟执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
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
            } catch (Exception $e) {
                // 发送通知
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【ContestRankCrontab】</strong>运行错误：' . $e->getMessage());
            }
        });

        // 每天凌晨2点执行一次
        new Crontab('00 2 * * *', function () {
            try {
                $redis4 = Redis::connection('db4');
                $redis24 = Redis::connection('db24');
                $redis24->del(Contest::$redis_array_name);
                $contest_list = Db::table('contest')
                    ->where('isdel', 0)
                    ->pluck('id')
                    ->toArray();
                // 已有缓存
                $has_id = Db::table('contestrankcache')
                    ->where('isdel', 0)
                    ->pluck('contestid')
                    ->toArray();
                $has_map = [];
                foreach ($has_id as &$id) {
                    $has_map[$id] = true;
                }
                foreach ($contest_list as &$contest_id) {
                    if ($has_map[$contest_id]) {
                        continue;
                    }
                    $lockonerank = 'contestranklock' . $contest_id;
                    $redis4->del($lockonerank);
                    $lockoneecharts = 'contestranklockecharts' . $contest_id;
                    $redis4->del($lockoneecharts);
                    // 发布任务
                    RedisQueue::send(Base::$redis_queue_contest_rank_name, ['contest_id' => $contest_id]);
                }
            } catch (Exception $e) {
                // 发送通知
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '定时任务进程<strong>【ContestRankCrontab】</strong>运行错误：' . $e->getMessage());
            }
        });
    }
}
