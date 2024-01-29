<?php

use \app\controller\Base;

$data = [
    'host' => '127.0.0.1',
    'password' => Base::$redis_password,
    'port' => 6379,
    'database' => 0,
    'timeout' => 3600
];

$list['default'] = $data;

for ($i = 0; $i < Base::$redis_db_num; $i++) {
    if ($i == Base::$redis_mq_db) {
        continue;
    }
    $data['database'] = $i;
    $list['db' . $i] = $data;
}

return $list;
