<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-12 07:44:22
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-22 11:58:52
 * @FilePath: \LTPP-CODE\process\Copy.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */

namespace process;

use app\controller\Robot;
use Exception;
use Workerman\Crontab\Crontab;
use app\controller\Base;

class Copy extends Robot
{
    static $copy_path = '/home/LTPPBACKUPS';
    static $lock = false;

    public function onWorkerStart()
    {
        // 每天凌晨两点执行一次
        new Crontab('00 2 * * *', function () {
            if (Copy::$lock) {
                return;
            }
            Copy::$lock = true;
            try {
                Base::judgeCreatPath(Copy::$copy_path);
                Base::judgeCreatPath(Base::$LTPP_path);
                Base::copyDirectory(Base::$LTPP_path, Copy::$copy_path);
                $msg = 'LTPP文件夹已备份！';
                $root_id = Base::getRootId();
                // 发送通知
                Robot::sendChatToOneUserMsg($root_id, $msg);
                Copy::$lock = false;
            } catch (Exception $e) {
                Copy::$lock = false;
            }
        });
    }
}