<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-04-20 08:45:03
 * @FilePath: \LTPP\app\controller\Music.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

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
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = Base::getUserData($my_aid);
        if (!$data || empty($data) || !isset($data->musicuid)) {
            return json([
                'code' => 1,
                'musicuid' => '',
                'msg' => 'error！'
            ]);
        }
        return json([
            'code' => 1,
            'musicuid' => $data->musicuid,
            'msg' => 'error！'
        ]);
    }

    /**
     * 获取喜欢的音乐歌单ID
     * @param Request $request 请求
     * @return string $res json
     */
    public function getLoveid()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = Base::getUserData($my_aid);
        if (!$data || empty($data) || !isset($data->musiclovelistid)) {
            return json([
                'code' => 1,
                'musiclovelistid' => '',
                'msg' => 'error！'
            ]);
        }
        return json([
            'code' => 1,
            'musiclovelistid' => $data->musiclovelistid,
            'msg' => 'error！'
        ]);
    }
}
;