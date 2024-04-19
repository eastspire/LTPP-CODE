<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 12:20:24
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-21 14:54:09
 * @FilePath: \LTPP-CODE\app\controller\Chatfile.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Db;
use support\Request;
use Tinywan\Jwt\JwtToken;

class App
{
    /**
     * 列表页展示字段
     */
    static $app_db_list_key = [
        'id',
        'name',
        'user_id',
        'opentimes',
        'url',
        'time',
        'image',
    ];

    /**
     * 详情页展示字段
     */
    static $app_db_key = [
        'id',
        'name',
        'user_id',
        'opentimes',
        'url',
        'time',
        'image',
        'content'
    ];

    /**
     * 获取APP列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadAllAppList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $data = Db::table('app')
            ->where('isdel', 0)
            ->select(App::$app_db_list_key)
            ->orderBy('opentimes', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('app')
            ->where('isdel', 0)
            ->count();
        foreach ($data as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 所有APP搜索
     */
    public function allAppKeySearch(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $data = Db::table('app')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(App::$app_db_list_key)
            ->orderBy('opentimes', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('app')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        foreach ($data as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 加载自己发布的应用
     * @param Request $request 请求
     * @return string $res json 
     */
    public function loadMyAppList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('app')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->select(App::$app_db_list_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('app')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->count();
        foreach ($info as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "您一共发布 $allnum 个应用"]);
    }

    /**
     * 个人应用搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function myAppKeySearch(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return json(['code' => -1, 'msg' => '查询失败']);
        }
        $info = Db::table('app')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->select(App::$app_db_list_key)
            ->paginate($limit, '*', 'page', $page)
            ->items(); //模糊查询
        $allnum = Db::table('app')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->count(); //模糊查询
        foreach ($info as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "查询到 $allnum 条结果"]);
    }

    /**
     * 后台加载所有发布的应用
     * @param Request $request 请求
     * @return string $res json 
     */
    public function backLoadAllAppList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('app')
            ->where('isdel', 0)
            ->select(App::$app_db_list_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('app')
            ->where('isdel', 0)
            ->count();
        foreach ($info as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "您一共发布 $allnum 个应用"]);
    }

    /**
     * 后台全部应用搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function backAllAppKeySearch(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return json(['code' => -1, 'msg' => '查询失败']);
        }
        $info = Db::table('app')
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->select(App::$app_db_list_key)
            ->paginate($limit, '*', 'page', $page)
            ->items(); //模糊查询
        $allnum = Db::table('app')
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->count(); //模糊查询
        foreach ($info as &$tem) {
            $db = Base::getUserData($tem->user_id);
            if (!$db) {
                $tem->user_name = Base::$unknow_user_name;
                continue;
            }
            $tem->user_name = $db->name;
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "查询到 $allnum 条结果"]);
    }

    /**
     * 添加应用
     */
    public function add(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        if (!isset($data['name']) || !$data['name']) {
            return json(['code' => -1, 'msg' => '请填写应用名称']);
        }
        if (!isset($data['image']) || !$data['image']) {
            return json(['code' => -1, 'msg' => '请填写应用图标URL地址']);
        }
        if (!isset($data['content']) || !$data['content']) {
            return json(['code' => -1, 'msg' => '请填写应用介绍']);
        }
        $resid = Base::insertToDb('app', [
            'name' => $data['name'],
            'user_id' => $my_aid,
            'url' => $data['url'],
            'image' => $data['image'],
            'content' => $data['content'],
        ]);
        if ($resid) {
            return json(['code' => 1, 'msg' => '添加应用成功']);
        }
        return json(['code' => -1, 'msg' => '添加应用失败']);
    }

    /**
     * 更新应用
     */
    public function update(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        $app_id = Base::getIdByUid($data['id']);
        $old_data = Base::getAppData($app_id);
        if (!isset($data['name']) || !$old_data) {
            return json(['code' => -1, 'msg' => '应用不存在']);
        }
        if ($my_aid != $old_data->user_id && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        if (!isset($data['name']) || !$data['name']) {
            return json(['code' => -1, 'msg' => '请填写应用名称']);
        }
        if (!isset($data['image']) || !$data['image']) {
            return json(['code' => -1, 'msg' => '请填写应用图标URL地址']);
        }
        if (!isset($data['content']) || !$data['content']) {
            return json(['code' => -1, 'msg' => '请填写应用介绍']);
        }
        Db::table('app')
            ->where('id', $app_id)
            ->where('isdel', 0)
            ->update([
                'name' => $data['name'],
                'user_id' => $my_aid,
                'url' => $data['url'],
                'image' => $data['image'],
                'content' => $data['content'],
            ]);
        Base::updateAppDataRedis($app_id);
        return json(['code' => 1, 'msg' => '应用更新成功']);
    }

    /**
     * 删除应用
     */
    public function delete(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $app_uid = $request->post('delete_id');
        $app_id = Base::getIdByUid($app_uid);
        $data = Base::getAppData($app_id);
        if (!$data) {
            return json(['code' => -1, 'msg' => '应用不存在']);
        }
        if ($my_aid != $data->user_id && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        Db::table('app')
            ->where('id', $app_id)
            ->where('isdel', 0)
            ->update([
                'isdel' => 1,
            ]);
        Base::updateAppDataRedis($app_id);
        return json(['code' => 1, 'msg' => '应用删除成功']);
    }

    /**
     * 查看APP信息
     */
    public function lookOneApp(Request $request)
    {
        $app_uid = $request->post('id');
        $app_id = Base::getIdByUid($app_uid);
        $data = Base::getAppData($app_id);
        if (!$data) {
            return json(['code' => -1, 'msg' => '应用不存在']);
        }
        $db = Base::getUserData($data->user_id);
        if (!$db) {
            $data->user_name = Base::$unknow_user_name;
        } else {
            $data->user_name = $db->name;
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'msg' => '加载成功', 'data' => $data]);
    }

    /**
     * 增加访问次数
     */
    public function addOpenTimes(Request $request)
    {
        $app_uid = $request->post('id');
        $app_id = Base::getIdByUid($app_uid);
        $data = Base::getAppData($app_id);
        if (!$data) {
            return json(['code' => -1, 'msg' => '应用不存在']);
        }
        Db::table('app')
            ->where('id', $app_id)
            ->where('isdel', 0)
            ->increment('opentimes', 1);
        Base::updateAppDataRedis($app_id);
        return json(['code' => 1, 'msg' => '访问成功', 'data' => []]);
    }
};
