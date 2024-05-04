<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-06-19 18:37:08
 * @FilePath: \LTPP-CODE\app\controller\Mynotice.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Db;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Mynotice
{
    /**
     * 数据库用户消息展示字段
     * @var array $usernotice_key 数据库用户消息展示字段
     */
    static $usernotice_key = [
        'id',
        'userid',
        'articleid',
        'videoid',
        'questionid',
        'fanuserid',
        'notice',
        'time'
    ];

    /**
     * 通知长度限制
     */
    static $notice_len_limit = 535;

    /**
     * 加载我的消息
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadMyNotice(
        Request $request
    ) {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('usernotice')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select(Mynotice::$usernotice_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('usernotice')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json([
            'code' => 1,
            'data' => $info,
            'allnum' => $allnum,
            'msg' => '获取到 ' . $allnum . '条消息'
        ]);
    }

    /**
     * 获取我的消息个数
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadMyNoticeNum(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $allnum = Db::table('usernotice')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->count();
        return json([
            'code' => 1,
            'time' => date('Y-m-d H:i:s', time()),
            'msg' => '您有 ' . $allnum . ' 条后台消息通知',
            'allnum' => $allnum
        ]);
    }

    /**
     * 删除我的某条消息
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteMyNotice(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $delete_uid = $request->post('delete_id');
        $delete_id = Base::getIdByUid($delete_uid);

        $db = Db::table('usernotice')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->select('userid')
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '消息不存在，删除失败']);
        }
        if ($my_aid != $db->userid) {
            return json(['code' => -1, 'msg' => '无权删除']);
        }
        $info = Db::table('usernotice')
            ->where('id', $delete_id)
            ->update([
                'isdel' => 1
            ]);
        if ($info) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => -1, 'msg' => '删除失败']);
    }

    /**
     * 删除我的全部消息
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteAllMyNotice(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Db::table('usernotice')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        return json(['code' => 1, 'msg' => '删除成功']);
    }
};
