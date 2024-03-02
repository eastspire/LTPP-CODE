<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-24 22:05:13
 * @FilePath: \LTPP-CODE\config\plugin\webman\gateway-worker\process.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

use Webman\GatewayWorker\Gateway;
use Webman\GatewayWorker\BusinessWorker;
use Webman\GatewayWorker\Register;

return [
    'gateway' => [
        'handler' => Gateway::class,
        'listen' => 'websocket://0.0.0.0:47272',
        'count' => (cpu_count() > 6 ? 6 : cpu_count()) * 4,
        'constructor' => [
            'config' => [
                'lanIp' => '127.0.0.1',
                'startPort' => 2300,
                'pingInterval' => 16,
                'pingData' => '{"type":"ping"}',
                'registerAddress' => '127.0.0.1:1236',
                'onConnect' => function () {
                },
            ]
        ]
    ],
    'worker' => [
        'handler' => BusinessWorker::class,
        'count' => (cpu_count() > 6 ? 6 : cpu_count()) * 2,
        'constructor' => [
            'config' => [
                'eventHandler' => plugin\webman\gateway\Events::class,
                'name' => 'ChatBusinessWorker',
                'registerAddress' => '127.0.0.1:1236',
            ]
        ]
    ],
    'register' => [
        'handler' => Register::class,
        'listen' => 'text://0.0.0.0:1236',
        'count' => 1,
        // Must be 1
        'constructor' => []
    ],
];
