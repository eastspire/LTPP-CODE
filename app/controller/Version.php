<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-27 17:09:26
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-06 20:12:56
 * @FilePath: \LTPP-CODE\app\controller\Version.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;

class Version
{
    /**
     * 软件版本
     * @var string $version 软件版本
     */
    static $version = '2.4.3';

    /**
     * 获取版本
     * @return $res 结果
     */
    public function getVersion()
    {
        try {
            $ltpp_win_download_url = Base::getSettingKeyData('ltpp_win_download_url');
            $ltpp_mac_download_url = Base::getSettingKeyData('ltpp_mac_download_url');
            $ltpp_apk_download_url = Base::getSettingKeyData('ltpp_apk_download_url');
            return json([
                'code' => 1,
                'version' => Version::$version,
                'ltpp_win_download_url' => $ltpp_win_download_url,
                'ltpp_mac_download_url' => $ltpp_mac_download_url,
                'ltpp_apk_download_url' => $ltpp_apk_download_url,
            ]);
        } catch (Exception $e) {
            return json([
                'code' => 0,
                'version' => '',
                'ltpp_win_download_url' => '',
                'ltpp_mac_download_url' => '',
                'ltpp_apk_download_url' => '',
            ]);
        }
    }
}
