<?php
/*
 * @Author: root@ltpp.vip
 * @Date: 2023-10-09 00:01:51
 * @LastEditors: eastspire root@ltpp.vip
 * @LastEditTime: 2023-11-13 18:36:49
 * @FilePath: \LTPP-CODE\process\RobotContest.php
 * @Description: Email:root@ltpp.vip
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */

namespace process;

use app\controller\Base;
use Exception;
use support\Db;
use Workerman\Crontab\Crontab;
use support\Redis;

class CreatFileTable
{
    public function onWorkerStart()
    {
        // 每分钟执行一次
        new Crontab('0 */1 * * * *', function () {
            try {
                $index = Db::table('file_table_index')->count();
                if ($index === 0) {
                    $index = Base::insertToDb('file_table_index', [
                        'md5' => ''
                    ]);
                    $md5 = md5($index);
                    Db::table('file_table_index')
                        ->where('id', $index)
                        ->update([
                            'md5' => $md5
                        ]);
                    $redis35 = Redis::connection('db35');
                    $key = 'md5' . $md5;
                    $redis35->setEx($key, Base::$redis_code_run_res_timeout, $index);
                }
                $file_path_name = $index . Base::$db_file_path_same_name;
                $file_data_name = $index .  Base::$db_file_data_same_name;
                $cnt_file_path_name = Db::table($file_path_name)
                    ->count();
                $cnt_file_data_name = Db::table($file_data_name)
                    ->count();
                if ($cnt_file_path_name >= Base::$one_table_length_limit || $cnt_file_data_name >= Base::$one_table_length_limit) {
                    // 先创建好表
                    Base::creatFilePathDataTable($index + 1);
                    // 再添加数据到索引表
                    $index = Base::insertToDb('file_table_index', [
                        'md5' => ''
                    ]);
                    $md5 = md5($index);
                    Db::table('file_table_index')
                        ->where('id', $index)
                        ->update([
                            'md5' => $md5
                        ]);
                    $redis35 = Redis::connection('db35');
                    $key = 'md5' . $md5;
                    $redis35->setEx($key, Base::$redis_code_run_res_timeout, $index);
                }
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【CreatFileTable】</strong>运行出错：' . $e->getMessage());
            }
        });
    }
}
