<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:37:43
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-09 07:45:36
 * @FilePath: \LTPP-CODE\config\plugin\webman\redis-queue\process.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

return [
    app\controller\Base::$redis_queue_send_mail_name => [
        'handler' => Webman\RedisQueue\Process\Consumer::class,
        // 可以设置多进程同时消费
        'count' => (cpu_count() > 4 ? 4 : cpu_count()) * 2,
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis'
        ]
    ],
    app\controller\Base::$redis_queue_robot_contest_name => [
        'handler' => Webman\RedisQueue\Process\Consumer::class,
        // 可以设置多进程同时消费
        'count' => (cpu_count() > 4 ? 4 : cpu_count()) * 2,
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis'
        ]
    ],
    app\controller\Base::$redis_queue_contest_rank => [
        'handler' => Webman\RedisQueue\Process\Consumer::class,
        // 可以设置多进程同时消费
        'count' => (cpu_count() > 4 ? 4 : cpu_count()) * 2,
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis'
        ]
    ],
    app\controller\Base::$redis_queue_buy_ssh_name => [
        'handler' => Webman\RedisQueue\Process\Consumer::class,
        // 可以设置多进程同时消费
        'count' => (cpu_count() > 4 ? 4 : cpu_count()) * 2,
        'constructor' => [
            // 消费者类目录
            'consumer_dir' => app_path() . '/queue/redis'
        ]
    ],
];