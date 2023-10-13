<?php

namespace process;

use app\controller\Robot;
use Workerman\Crontab\Crontab;
use app\controller\Base;

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
            if (!file_exists('/LTPP')) {
                mkdir('/LTPP', 0666, true);
            }
            if (!file_exists('/home/LTPP/public')) {
                mkdir('/home/LTPP/public', 0666, true);
            }
            if (!file_exists(Base::$LTPP_public_static_path . '')) {
                mkdir(Base::$LTPP_public_static_path . '', 0666, true);
            }
            $this->dfs(Base::$LTPP_public_static_path . '');

            $msg = 'static文件夹下全部文件的权限已更新！';
            $root_id = Base::getRootId();
            // 发送通知
            Robot::sendChatToOneUserMsg($root_id, $msg);
        });
    }
}