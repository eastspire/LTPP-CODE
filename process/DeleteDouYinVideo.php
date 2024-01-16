<?php

namespace process;

use app\controller\Robot;
use Workerman\Crontab\Crontab;
use app\controller\Base;
use Exception;
use support\Db;

class DeleteDouYinVideo
{
    public function onWorkerStart()
    {
        // 每30分钟执行一次
        new Crontab('0 */30 * * * *', function () {
            try {
                $noupdate_limit_seconds = Base::getSettingKeyData('douyin_noupdate_limit_seconds');
                // 删除过期的抖音视频
                Db::table('video')
                    ->where('isdouyin', 1)
                    ->where('isdel', 0)
                    ->where('time', '<', date('Y-m-d H:i:s', time() - $noupdate_limit_seconds))
                    ->update(['isdel' => 1]);
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【DeleteDouYinVideo】** 运行错误：' . $e->getMessage());
            }
        });
    }
}
