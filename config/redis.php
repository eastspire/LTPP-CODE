<?php

use \app\controller\Base;

$data = [
    'host' => Base::$redis_domain_name,
    'password' => Base::$redis_password,
    'port' => Base::$redis_port,
    'database' => 0,
    'timeout' => 3600
];

$list['default'] = $data;

for ($i = 0; $i < Base::$redis_db_num; $i++) {
    $data['database'] = $i;
    $list['db' . $i] = $data;
}

return $list;
