<?php

use \app\controller\Base;

return [
    // 默认数据库
    'default' => 'mysql',
    // 各种数据库配置
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Base::$mysql_domain_name,
            'port' => Base::$mysql_port,
            'database' => 'ltpp',
            'username' => Base::$mysql_username,
            'password' => Base::$mysql_password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
        ],
    ],
];
