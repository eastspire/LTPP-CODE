<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-01 09:11:34
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-15 15:48:27
 * @FilePath: \LTPP-CODE\config\plugin\tinywan\storage\app.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

/**
 * @desc app.php 描述信息
 *
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/3/10 19:46
 */

return [
    'enable' => false,
    'storage' => [
        'default' => 'local',
        // local：本地 oss：阿里云 cos：腾讯云 qos：七牛云
        'single_limit' => 1024 * 1024 * 200,
        // 单个文件的大小限制，默认200M 1024 * 1024 * 200
        'total_limit' => 1024 * 1024 * 200,
        // 所有文件的大小限制，默认200M 1024 * 1024 * 200
        'nums' => 100,
        // 文件数量限制，默认10
        'include' => [],
        // 被允许的文件类型列表
        'exclude' => [],
        // 不被允许的文件类型列表
        // 本地对象存储
        'local' => [
            'adapter' => \Tinywan\Storage\Adapter\LocalAdapter::class,
            'root' => runtime_path() . '/storage',
            'dirname' => function () {
                return date('Ymd');
            },
            'domain' => 'http://127.0.0.1:48787',
            'uri' => '/runtime',
            // 如果 domain + uri 不在 public 目录下，请做好软链接，否则生成的url无法访问
            'algo' => 'sha1',
        ],
    ],
];
