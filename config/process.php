<?php
/*
 * @Author: wmzn-ltpp 1491579574@qq.com
 * @Date: 2023-08-07 18:43:59
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-31 10:40:40
 * @FilePath: \LTPP-CODE\config\process.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
 */

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    'WebcodeCrontab' => [
        'Webcode' => process\WebcodeCrontab::class,
    ],
    'CreatContestCrontab' => [
        'CreatContest' => process\CreatContestCrontab::class,
    ],
    'ChatfileCrontab' => [
        'handler' => process\ChatfileCrontab::class
    ],
    'DayproblemCrontab' => [
        'handler' => process\DayproblemCrontab::class
    ],
    'GitcodeCrontab' => [
        'handler' => process\GitcodeCrontab::class
    ],
    'RobotContestCrontab' => [
        'handler' => process\RobotContestCrontab::class
    ],
    'ContestRankCrontab' => [
        'handler' => process\ContestRankCrontab::class
    ],
    // File update detection and automatic reload
    // 'monitor' => [
    //     'handler' => process\Monitor::class,
    //     'reloadable' => false,
    //     'constructor' => [
    //         // Monitor these directories
    //         'monitor_dir' => [
    //             app_path(),
    //             config_path(),
    //             base_path() . '/process',
    //             base_path() . '/support',
    //             base_path() . '/resource',
    //             base_path() . '/.env',
    //         ],
    //         // Files with these suffixes will be monitored
    //         'monitor_extensions' => [
    //             'php',
    //             'html',
    //             'htm',
    //             'env'
    //         ]
    //     ]
    // ],
    // 'Register' => [
    //     'handler' => \Webman\App::class,
    //     'listen' => 'http://0.0.0.0:48800',
    //     'count' => cpu_count() > 8 ? 8 : cpu_count(),
    //     // 进程数
    //     'constructor' => [
    //         'request_class' => \support\Request::class,
    //         // request类设置
    //         'logger' => \support\Log::channel('default'),
    //         // 日志实例
    //         'app_path' => app_path(),
    //         // app目录位置
    //         'public_path' => public_path() // public目录位置
    //     ]
    // ],
];
