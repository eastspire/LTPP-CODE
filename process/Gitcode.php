<?php

namespace process;

use app\controller\Robot;
use Workerman\Crontab\Crontab;
use app\controller\Base;
use Exception;

class Gitcode extends Robot
{
    protected function dfs($path)
    {
        if (!is_dir($path)) {
            return;
        }
        $dirs = scandir($path);
        foreach ($dirs as &$tem) {
            if ($tem == '.' || $tem == '..') {
                continue;
            }
            $tempath = $path . '/' . $tem;
            if (is_dir($tempath)) {
                $this->dfs($tempath);
            } else {
                chmod($tempath, 0666);
            }
        }
    }

    public function onWorkerStart()
    {
        // 每天凌晨一点执行一次
        new Crontab('00 1 * * *', function () {
            try {
                Base::judgeCreatPath(Base::$LTPP_public_static_path, 0666);
                $this->dfs(Base::$LTPP_public_static_path . '');
                $msg = 'static文件夹下全部文件的权限已更新！';
                $root_id = Base::getRootId();
                // 发送通知
                Robot::sendChatToOneUserMsg($root_id, $msg);
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【Gitcode】** 运行错误：' . $e->getMessage());
            }
        });
    }
}