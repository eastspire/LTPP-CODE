<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-27 17:09:26
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-16 15:57:22
 * @FilePath: \LTPP-CODE\app\controller\Version.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use support\Request;

class Version
{
    /**
     * 软件版本
     * @var string $version 软件版本
     */
    static $version = '1.4.1';

    /**
     * 安装包路径
     * @var string $install_root_path tauri安装包路径
     */
    static $install_root_path = 'static/version/';

    /**
     * 文件名称
     */
    static $file_name = 'LTPP(InstallFile).exe';

    /**
     * 获取版本
     * @return $res 结果
     */
    public function getVersion(Request $request)
    {
        try {
            $url = Base::getGLOBlinuxurl();
            return json([
                'code' => 1,
                'version' => Version::$version,
                'url' => $url . '/' . Version::$install_root_path . Version::$file_name
            ]);
        } catch (Exception $e) {
            return json([
                'code' => 0,
                'version' => '',
                'url' => ''
            ]);
        }
    }
}