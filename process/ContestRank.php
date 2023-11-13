<?php
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

class ContestRank
{
    static $lock = false;

    static $times = 0;

    static $limit = 60;

    public function onWorkerStart()
    {
        // 每一秒钟执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
                if (ContestRank::$lock) {
                    return;
                }
                if (ContestRank::$times > ContestRank::$limit) {
                    ContestRank::$times = 0;
                }
                ContestRank::$lock = true;
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
                    RedisQueue::send(Base::$redis_queue_contest_rank, ['contest_id' => $key]);
                }
                $obj = null;
                if (ContestRank::$times % ContestRank::$limit == 0) {
                    $contest_list = Db::table('contest')
                        ->where('isdel', 0)
                        ->pluck('id')
                        ->toArray();
                    foreach ($contest_list as &$contest_id) {
                        // 发布任务
                        RedisQueue::send(Base::$redis_queue_contest_rank, ['contest_id' => $contest_id]);
                    }
                }
                ContestRank::$times++;
                ContestRank::$lock = false;
            } catch (Exception $e) {
                ContestRank::$lock = false;
                // 发送通知
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【ContestRank】** 运行错误：' . $e->getMessage());
            }
        });
    }
}