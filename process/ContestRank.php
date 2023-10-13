<?php
namespace process;

use app\controller\Base;
use app\controller\Contest;
use app\controller\Robot;
use Exception;
use stdClass;
use support\Redis;
use Workerman\Crontab\Crontab;
use Webman\RedisQueue\Redis as RedisQueue;

class ContestRank
{
    static $lock = false;

    public function onWorkerStart()
    {
        // 每一秒钟执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
                if (ContestRank::$lock) {
                    return;
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
                ContestRank::$lock = false;
            } catch (Exception $e) {
                ContestRank::$lock = false;
                // 发送通知
                $msg = '排名缓冲区异常：' . $e->getMessage();
                Robot::sendChatToOneUserMsg(Base::getRootId(), $msg);
            }
        });
    }
}