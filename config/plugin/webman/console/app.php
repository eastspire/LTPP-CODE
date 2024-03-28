<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-07-05 09:12:06
 * @FilePath: \LTPP-CODE\config\plugin\webman\console\app.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

/**
 * 有些 ini 设置不能通过 ini_set 临时改变，原因是 PHP 在执行脚本之前就需要这些值。
 * 当上传发生时，目标脚本在上传完成后执行，因此 PHP 需要事先知道最大大小。
 * 如果你确实想打包为 bin ，可以事先定义一些 ini 设置，
 * 修改 config/plugin/webman/console/app.php 里面的 custom_ini 配置项，
 * 一行一行配置，和 php.ini 文件的格式一样。
 * 如果没有这个配置项请更新 webman/console 。
 * https://www.workerman.net/q/11438
 */

return [
    'enable' => true,
    'custom_ini' => '
        memory_limit=-1
        upload_max_filesize=1024G
        post_max_size=1024G
        max_execution_time=0
    ',
    'phar_file_output_dir' => BASE_PATH . DIRECTORY_SEPARATOR . 'build',
    'phar_filename' => 'LTPP.phar',
    'bin_filename' => 'LTPP',
    'signature_algorithm' => Phar::SHA256,
    //set the signature algorithm for a phar and apply it. The signature algorithm must be one of Phar::MD5, Phar::SHA1, Phar::SHA256, Phar::SHA512, or Phar::OPENSSL.
    'private_key_file' => '',
    // The file path for certificate or OpenSSL private key file.
    'exclude_pattern' => '#^(?!.*(composer.json|/.github/|/.idea/|/.git/|/.setting/|/runtime/|/vendor-bin/|/build/|/Music/|/Frontend/|/InstallMust/|/sh/|/public/|/.vscode|/.gitignore|/Dockerfile|/README.md))(.*)$#',
    'exclude_files' => [
        '.env',
        'LICENSE',
        'composer.json',
        'composer.lock',
        'start.php'
    ]
];
