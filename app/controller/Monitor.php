<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 13:36:03
 * @FilePath: \LTPP-CODE\app\controller\Monitor.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Monitor
{
    /**
     * 监控数据库选择的字段
     * @var array $monitor_db_key 监控数据库
     */
    static $monitor_db_key = [
        'id',
        'path',
        'function',
        'time',
        'userid'
    ];

    /**
     * 获取监控数据
     * @param Request $request
     * @return string $res json
     */
    public function getData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足', 'data' => []]);
        }
        $time = $request->post('time');
        if (!$time || sizeof($time) < 2) {
            $time = [0, time()];
        }
        $begin = (int) ($time[0] / 1000);
        $end = (int) ($time[1] / 1000);
        $limit = $request->post('limit');
        Base::judgeLimitIsSafe($limit);
        $page_last_uid = $request->post('id');
        $search_func_key = $request->post('key');
        $page_last_id = Base::getIdByUid($page_last_uid);
        Base::judgeLimitIsSafe($limit);
        $begin = date('Y-m-d H:i:s', (int) $begin);
        $end = date('Y-m-d H:i:s', (int) $end);
        if ($page_last_id) {
            if ($search_func_key) {
                $data = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('id', '<', $page_last_id)
                    ->where('function', 'like', '%' . $search_func_key . '%')
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
                $count = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('id', '<', $page_last_id)
                    ->where('function', 'like', '%' . $search_func_key . '%')
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->count();
            } else {
                $data = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('id', '<', $page_last_id)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
                $count = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('id', '<', $page_last_id)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->count();
            }
        } else {
            if ($search_func_key) {
                $data = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->where('function', 'like', '%' . $search_func_key . '%')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
                $count = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->where('function', 'like', '%' . $search_func_key . '%')
                    ->count();
            } else {
                $data = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
                $count = Db::table('monitor')
                    ->where('isdel', 0)
                    ->where('time', '>=', $begin)
                    ->where('time', '<=', $end)
                    ->count();
            }
        }
        $res = [];
        foreach ($data as &$tem) {
            $user_data = Base::getUserData($tem['userid']);
            if (!$user_data) {
                continue;
            }
            $tem['name'] = $user_data['name'];
            $tem['grade'] = $user_data['grade'];
            $res[] = $tem;
        }
        Base::dataToSafe($res);
        return json(['code' => 1, 'msg' => '监控获取成功', 'data' => $res, 'count' => $count]);
    }
};
