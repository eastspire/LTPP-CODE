<?php

use \app\controller\Base;

$list = [];

for ($i = 0; $i < Base::$redis_db_num; $i++) {
    $list['db' . $i] = [
        'host' => '127.0.0.1',
        'password' => Base::$redis_password,
        'port' => 6379,
        'database' => 0,
        'timeout' => 3600
    ];
}

return $list;
