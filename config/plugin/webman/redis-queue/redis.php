<?php

use \app\controller\Base;

return [
    'default' => [
        'host' => 'redis://127.0.0.1:6379',
        'options' => [
            // 密码，字符串类型，可选参数
            'auth' => Base::$redis_password,
            // 数据库
            'db' => 19,
            // key 前缀
            'prefix' => '',
            // 消费失败后，重试次数
            'max_attempts' => 36,
            // 重试间隔，单位秒
            'retry_seconds' => 1,
        ]
    ],
];
