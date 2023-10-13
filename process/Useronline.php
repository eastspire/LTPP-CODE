<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-07 12:15:48
 * @FilePath: \LTPP-CODE\process\Useronline.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace process;

use app\controller\Base;
use Workerman\Crontab\Crontab;
use support\Db;

class Useronline
{
    public function onWorkerStart()
    {
        // 每15分钟执行一次，注意这里省略了秒位
        new Crontab('*/15 * * * *', function () {
            $limittime = 900;
            $db = Db::table('user')
                ->pluck('id')
                ->toArray();
            foreach ($db as &$tem) {
                if ($tem) {
                    $last = Db::table('user')->where('id', $tem)->select('lastlogin')->first();
                    if ($last && time() - strtotime($last->lastlogin) >= $limittime) {
                        Db::table('user')->where('id', $tem)->update(['online' => 0]);
                        Base::updateUserDataRedis($tem);
                    }
                }
            }
        });
    }
}