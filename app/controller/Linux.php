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
use support\Db;
use Tinywan\Jwt\JwtToken;
use Webman\RedisQueue\Redis as RedisQueue;

class Linux
{
    /**
     * 服务器列表数据库展示的字段
     * @var array $linux_list_db_key 服务器列表数据库展示的字段
     */
    static $linux_list_db_key = [
        'name',
        'userid',
        'end_port',
        'begin_port',
        'buy_time',
        'password'
    ];

    /**
     * 获取服务器列表
     * @return string $res json 
     */
    public function getList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $allnum = 0;
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
                'data' => [],
                'allnum' => $allnum
            ]);
        }
        $db = Db::table('ssh')
            ->where('isdel', 0)
            ->select(Linux::$linux_list_db_key)
            ->orderBy('begin_port', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('ssh')
            ->where('isdel', 0)
            ->count();
        foreach ($db as &$tem) {
            $user_db = Base::getUserData($tem->userid);
            if ($user_db) {
                $tem->user_name = $user_db->name;
            } else {
                $tem->user_name = '未知用户';
            }
        }
        Base::dataToSafe($db);
        return json([
            'code' => 1,
            'msg' => '加载完成',
            'data' => $db,
            'allnum' => $allnum
        ]);
    }

    /**
     * 搜索服务器列表（名称，创建者，端口）
     * @return string $res json 
     */
    public function searchList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        $key = $request->post('key');
        Base::judgePageLimitIsSafe($page, $limit);
        $allnum = 0;
        $db = [];
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
                'data' => $db,
                'allnum' => $allnum
            ]);
        }
        $search_user_db = Db::table('user')
            ->where('name', $key)
            ->where('isdel', 0)
            ->select('id')
            ->first();
        if ($search_user_db) {
            $db = Db::table('ssh')
                ->orWhere(function ($query) use ($key) {
                    $query->where('name', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('begin_port', '<=', $key)
                        ->where('end_port', '>=', $key)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($search_user_db) {
                    $query->where('userid',  $search_user_db->id)
                        ->where('isdel', 0);
                })
                ->select(Linux::$linux_list_db_key)
                ->orderBy('begin_port', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('ssh')
                ->orWhere(function ($query) use ($key) {
                    $query->where('name', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('begin_port', '<=', $key)
                        ->where('end_port', '>=', $key)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($search_user_db) {
                    $query->where('userid',  $search_user_db->id)
                        ->where('isdel', 0);
                })
                ->count();
        } else {
            $db = Db::table('ssh')
                ->orWhere(function ($query) use ($key) {
                    $query->where('name', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('begin_port', '<=', $key)
                        ->where('end_port', '>=', $key)
                        ->where('isdel', 0);
                })
                ->select(Linux::$linux_list_db_key)
                ->orderBy('begin_port', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('ssh')
                ->orWhere(function ($query) use ($key) {
                    $query->where('name', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('begin_port', '<=', $key)
                        ->where('end_port', '>=', $key)
                        ->where('isdel', 0);
                })
                ->count();
        }
        foreach ($db as &$tem) {
            $user_db = Base::getUserData($tem->userid);
            if ($user_db) {
                $tem->user_name = $user_db->name;
            } else {
                $tem->user_name = '未知用户';
            }
        }
        Base::dataToSafe($db);
        return json([
            'code' => 1,
            'msg' => '加载完成',
            'data' => $db,
            'allnum' => $allnum
        ]);
    }

    /**
     * 关闭服务器
     * @return string $res json 
     */
    public function shutdown(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/shutdown';
        $data = [
            'name' => $name
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交',
        ]);
    }

    /**
     * 开启服务器
     * @return string $res json 
     */
    public function poweron(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/poweron';
        $data = [
            'name' => $name
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交',
        ]);
    }

    /**
     * 重启服务器
     * @return string $res json 
     */
    public function reboot(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/reboot';
        $data = [
            'name' => $name
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交',
        ]);
    }

    /**
     * 删除服务器
     * @return string $res json 
     */
    public function delete(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        Db::table('ssh')
            ->where('name', $name)
            ->where('isdel', 0)
            ->update([
                'isdel' => 1
            ]);
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/delete';
        $data = [
            'name' => $name
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交',
        ]);
    }
};
