<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-04-07 19:48:54
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-02 23:42:40
 * @FilePath: \LTPP-CODE\config\server.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
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
    'listen' => 'http://0.0.0.0:48787',
    'transport' => 'tcp',
    'context' => [],
    'name' => 'LTPP',
    'count' => (cpu_count() > 6 ? 6 : cpu_count()) * 4,
    'user' => '',
    'group' => '',
    'reusePort' => false,
    'event_loop' => '',
    'stop_timeout' => 1,
    'pid_file' => runtime_path() . '/LTPP.pid',
    'status_file' => runtime_path() . '/LTPP.status',
    'stdout_file' => runtime_path() . '/logs/stdout.log',
    'log_file' => runtime_path() . '/logs/LTPP.log',
    'max_package_size' => 10240000 * 1024 * 1024
];
