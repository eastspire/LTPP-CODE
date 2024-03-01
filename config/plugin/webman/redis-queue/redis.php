<?php

use \app\controller\Base;

return [
    'default' => [
        'host' => 'redis://' . Base::$redis_domain_name . ':' . Base::$redis_port,
        'options' => [
            // 密码，字符串类型，可选参数
            'auth' => Base::$redis_password,
            // 数据库
            'db' => Base::$redis_mq_db,
            // key 前缀
            'prefix' => '',
            // 消费失败后，重试次数
            'max_attempts' => 888,
            // 重试间隔，单位秒
            'retry_seconds' => 1,
        ]
    ],
];
