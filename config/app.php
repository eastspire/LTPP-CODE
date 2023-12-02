<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-04-18 15:41:17
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-29 22:27:15
 * @FilePath: \LTPP-CODE\config\app.php
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
 * 'public_path' => base_path() . DIRECTORY_SEPARATOR . 'public',
 * 'runtime_path' => base_path(false) . DIRECTORY_SEPARATOR . 'runtime',
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

use support\Request;

return [
    'debug' => false,
    'default_timezone' => 'Asia/Shanghai',
    'request_class' => Request::class,
    'public_path' => app\controller\Base::$LTPP_public_path,
    'runtime_path' => app\controller\Base::$LTPP_runtime_path,
    'controller_suffix' => '',
];
