<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-05-22 18:56:10
 * @FilePath: \LTPP\app\controller\Url.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;

class Url
{
    /**
     * 获取音乐后端地址
     * @param Request $request 请求
     * @return string $res json
     */
    public function getMusicBkUrl()
    {
        $musicbkurl = Base::getSettingKeyData('musicbkurl');
        return json(['code' => 1, 'data' => $musicbkurl]);
    }

    /**
     * 前端服务器地址
     * @return string $res json 
     */
    public function getFrontUrl()
    {
        $fronturl = Base::getSettingKeyData('GLOBfronturl');
        return json(['code' => 1, 'data' => $fronturl]);
    }

    /**
     * 后端服务器地址
     * @return string $res json 
     */
    public function getBackUrl()
    {
        $linuxurl = Base::getSettingKeyData('GLOBlinuxurl');
        return json(['code' => 1, 'data' => $linuxurl]);
    }

    /**
     * 即时通讯服务器地址
     * @return string $res json 
     */
    public function getSocketUrl()
    {
        $linuxurl = Base::getSettingKeyData('socketurl');
        return json(['code' => 1, 'data' => $linuxurl]);
    }

    /**
     * 返回课堂直播地址
     * @param Request $request 请求
     * @return string $res json
     */
    public function getClassUrl(Request $request)
    {
        $linuxurl = Base::getSettingKeyData('classurl');
        return json(['code' => 1, 'data' => $linuxurl]);
    }
};
