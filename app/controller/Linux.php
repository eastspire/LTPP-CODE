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
        'password',
        'cpu',
        'memory'
    ];

    /**
     * 购买服务器
     */
    public function buyLinux(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $password = $request->post('passsword');
        $cpu = $request->post('cpu');
        $memory = $request->post('memory');
        $port_num = $request->post('port_num');
        $msg = Ssh::buy($my_aid, $password, $cpu, $memory, $port_num);
        return json([
            'code' => 1,
            'msg' => $msg,
        ]);
    }

    /**
     * 获取我的服务器数据
     */
    private function getMyOneLinuxDb($linux_name, $my_aid)
    {
        $db = null;
        if (Base::judgeIsRoot($my_aid)) {
            $db = Db::table('ssh')
                ->where('name', $linux_name)
                ->where('isdel', 0)
                ->select(Linux::$linux_list_db_key)
                ->first();
        } else {
            $db = Db::table('ssh')
                ->where('name', $linux_name)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->select(Linux::$linux_list_db_key)
                ->first();
        }
        return $db;
    }

    /**
     * 获取全站服务器列表
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
                $tem->user_name = Base::$unknow_user_name;
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
     * 获取我的服务器列表
     * @return string $res json 
     */
    public function getMyList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $allnum = 0;
        $db = Db::table('ssh')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select(Linux::$linux_list_db_key)
            ->orderBy('begin_port', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('ssh')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->count();
        $user_db = Base::getUserData($my_aid);
        $user_name = $user_db ? $user_db->name : Base::$unknow_user_name;
        foreach ($db as &$tem) {
            $tem->user_name = $user_name;
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
     * 搜索全站服务器列表（名称，创建者，端口）
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
                $tem->user_name = Base::$unknow_user_name;
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
     * 搜索我的服务器列表（名称，端口）
     * @return string $res json 
     */
    public function searchMyList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        $key = $request->post('key');
        Base::judgePageLimitIsSafe($page, $limit);
        $allnum = 0;
        $db = [];
        $db = Db::table('ssh')
            ->orWhere(function ($query) use ($key, $my_aid) {
                $query->where('userid', $my_aid)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($key, $my_aid) {
                $query->where('userid', $my_aid)
                    ->where('begin_port', '<=', $key)
                    ->where('end_port', '>=', $key)
                    ->where('isdel', 0);
            })
            ->select(Linux::$linux_list_db_key)
            ->orderBy('begin_port', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('ssh')
            ->orWhere(function ($query) use ($key, $my_aid) {
                $query->where('userid', $my_aid)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($key, $my_aid) {
                $query->where('userid', $my_aid)
                    ->where('begin_port', '<=', $key)
                    ->where('end_port', '>=', $key)
                    ->where('isdel', 0);
            })
            ->count();
        $user_db = Base::getUserData($my_aid);
        $user_name = $user_db ? $user_db->name : Base::$unknow_user_name;
        foreach ($db as &$tem) {
            $tem->user_name = $user_name;
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
        if (!$this->getMyOneLinuxDb($name, $my_aid)) {
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
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
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
        if (!$this->getMyOneLinuxDb($name, $my_aid)) {
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
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
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
        if (!$this->getMyOneLinuxDb($name, $my_aid)) {
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
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
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
        if (!$this->getMyOneLinuxDb($name, $my_aid)) {
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
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
        ]);
    }

    /**
     * 创建快照
     * @return string $res json 
     */
    public function creatImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        if (!$this->getMyOneLinuxDb($name, $my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/creatImage';
        $data = [
            'name' => $name
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
        ]);
    }

    /**
     * 回滚快照
     * @return string $res json 
     */
    public function backLastImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        $db = $this->getMyOneLinuxDb($name, $my_aid);
        if (!$db) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/backLastImage';
        $port  = $db->begin_port;
        $password = $db->password;
        $port_num = $db->end_port - $db->begin_port + 1;
        $cpu = $db->cpu;
        $memory = $db->memory;
        $data = [
            'name' => $name,
            'port' => $port,
            'password' => $password,
            'port_num' => $port_num,
            'cpu' =>  $cpu,
            'memory' => $memory
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
        ]);
    }

    /**
     * 重置快照
     * @return string $res json 
     */
    public function resetImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $name = $request->post('name');
        $db = $this->getMyOneLinuxDb($name, $my_aid);
        if (!$db) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $url = 'http://' . Base::$ssh_ip . ':' . Base::$ssh_port . '/SSH/resetImage';
        $port  = $db->begin_port;
        $password = $db->password;
        $port_num = $db->end_port - $db->begin_port + 1;
        $cpu = $db->cpu;
        $memory = $db->memory;
        $data = [
            'name' => $name,
            'port' => $port,
            'password' => $password,
            'port_num' => $port_num,
        ];
        RedisQueue::send(Base::$redis_queue_request_name, [
            'user_id' => $my_aid,
            'url' => $url,
            'is_post' => true,
            'data' => $data,
            'cpu' =>  $cpu,
            'memory' => $memory
        ]);
        return json([
            'code' => 1,
            'msg' => '任务已提交！请等待机器人回复结果！',
        ]);
    }
};
