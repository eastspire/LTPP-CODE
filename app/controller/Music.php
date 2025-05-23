<?php
/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 1491579574@qq.com
 * @LastEditTime: 2023-04-20 08:45:03
 * @FilePath: \LTPP\app\controller\Music.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Music
{
    /**
     * 获取网易云音乐uid
     * @return string $res json 
     */
    public function getMusicUid()
    {
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
            $data = Base::getUserData($my_aid);
            if (!$data || empty($data) || !isset($data->musicuid)) {
                return json([
                    'code' => 1,
                    'musicuid' => '',
                    'msg' => '用户未设置网易云音乐UID'
                ]);
            }
            return json([
                'code' => 1,
                'musicuid' => $data->musicuid,
                'msg' => '用户网易云音乐UID加载完成'
            ]);
        } catch (Exception) {
        }
        return json([
            'code' => 1,
            'musicuid' => '',
            'msg' => '用户未设置网易云音乐UID'
        ]);
    }

    /**
     * 获取喜欢的音乐歌单ID
     * @param Request $request 请求
     * @return string $res json
     */
    public function getLoveid()
    {
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
            $data = Base::getUserData($my_aid);
            if (!$data || empty($data) || !isset($data->musiclovelistid)) {
                return json([
                    'code' => 1,
                    'musiclovelistid' => '',
                    'msg' => '用户未设置网易云音乐歌单ID'
                ]);
            }
            return json([
                'code' => 1,
                'musiclovelistid' => $data->musiclovelistid,
                'msg' => '用户网易云音乐歌单ID加载完成'
            ]);
        } catch (Exception) {
        }
        return json([
            'code' => 1,
            'musiclovelistid' => '',
            'msg' => '用户未设置网易云音乐歌单ID'
        ]);
    }
};
