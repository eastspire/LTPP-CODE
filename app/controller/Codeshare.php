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
use Exception;
use Tinywan\Jwt\JwtToken;

class Codeshare
{
    /**
     * 添加代码分享
     */
    public function addShareCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $code = $request->post('code');
        if (!$code) {
            return json(['code' => -1, 'msg' => '分享失败！代码不能为空！']);
        }
        $language = $request->post('language');
        if (!$language) {
            return json(['code' => -1, 'msg' => '分享失败！语言不能为空！']);
        }
        $id = Base::insertToDb('codeshare', [
            'userid' => $my_aid,
            'language' => $language,
            'code' => $code,
            'time' => date('Y-m-d H:i:s', time()),
        ]);
        $uid = Base::getUidById($id);
        $url = Base::getGLOBlinuxurl() . '/Codeshare/shareCode?path=' . $uid;
        return json(['code' => 1, 'msg' => '分享成功', 'url' => $url]);
    }

    /**
     * 代码分享页面
     */
    public function shareCode(Request $request)
    {
        try {
            $code_uid = $request->get('path');
            if (!$code_uid) {
                return Base::notFoundPage();
            }
            $code_id = Base::getIdByUid($code_uid);
            if (!$code_id) {
                return Base::notFoundPage();
            }
            $code_share_db = Base::getShareCodeData($code_id);
            return Base::codeShareToHTML($code_share_db);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return Base::notFoundPage();
        }
    }
};
