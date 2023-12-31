<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 15:44:00
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 09:36:39
 * @FilePath: \LTPP-CODE\process\Chatfile.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace process;

use Workerman\Crontab\Crontab;
use app\controller\Robot;
use app\controller\Base;
use Exception;

class Chatfile
{
    /**
     * @var $time_out_delete 文件存储最长时间（单位：天）
     */
    static $time_out_delete = 3600 * 24 * 30;
    static $delete_arr = [];

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
                $last_time = filemtime($tempath);
                $time = time();

                if ($time - $last_time > Chatfile::$time_out_delete) {
                    @unlink($tempath);
                    $name = '';
                    for ($i = strlen($tempath) - 1; $i >= 0; --$i) {
                        if ($tempath[$i] == '/') {
                            break;
                        }
                        $name .= $tempath[$i];
                    }
                    $name = strrev($name);
                    Chatfile::$delete_arr[] = [
                        'name' => $name,
                        'time' => $last_time
                    ];
                }
            }
        }
    }

    public function onWorkerStart()
    {
        // 每天凌晨一点执行一次
        new Crontab('00 1 * * *', function () {
            try {
                Base::judgeCreatPath(Base::$LTPP_public_static_path . '/chatfile', 0666);
                Chatfile::$delete_arr = [];
                // 开始更新文件
                $this->dfs(Base::$LTPP_public_static_path . '/chatfile');
                $now = date('Y-m-d H:i:s', time());
                $msg = '';
                if (empty(Chatfile::$delete_arr)) {
                    $msg = '系统于北京时间' . $now . '删除' . sizeof(Chatfile::$delete_arr) . '个过期的群聊文件' . "\n";
                } else {
                    $msg = '系统于北京时间' . $now . '删除' . sizeof(Chatfile::$delete_arr) . '个过期的群聊文件，详情如下：' . "\n";
                    foreach (Chatfile::$delete_arr as &$tem) {
                        $msg .= '文件名：' . $tem['name'] . "\n";
                        $msg .= '该文件上传时间：' . $tem['time'] . "\n\n";
                    }
                }
                $root_id = Base::getRootId();
                // 发送通知
                Robot::sendChatToOneUserMsg($root_id, $msg);
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【Chatfile】** 运行错误：' . $e->getMessage());
            }
        });
    }
}
