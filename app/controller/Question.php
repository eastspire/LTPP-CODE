<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-07-25 08:28:32
 * @FilePath: \LTPP-CODE\app\controller\Question.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use support\Request;
use support\Db;
use support\Redis;
use Tinywan\Jwt\JwtToken;


class Question
{
    /**
     * 列表数据库字段
     */
    static $question_list_db_key = [
        'id',
        'name',
        'userid',
        'time',
        'answer_num',
    ];

    /**
     * 详情页数据库字段
     */
    static $question_db_key = [
        'id',
        'question',
        'userid',
        'time',
        'answer_num',
    ];

    /**
     * 提问限速缓存过期时间
     */
    static $time_out = 10;

    /**
     * 数据条数限制
     */
    static $limit = 50;

    /**
     * 判断是否有权限更新或者删除问题
     * @param int $userid 用户id
     * @param int $question_id 问题id
     */
    private function judgeIsMyQuestion($userid, $question_id)
    {
        if (Base::judgeIsRoot($userid)) {
            return true;
        }
        $ishas = Db::table('question')
            ->where('id', $question_id)
            ->where('userid', $userid)
            ->where('isdel', 0)
            ->exists();
        return $ishas;
    }

    /**
     * 获取问题列表
     */
    public function getList(Request $request)
    {
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        if ($question_id) {
            $db = Db::table('question')
                ->where('id', '<', $question_id)
                ->where('isdel', 0)
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $db = Db::table('question')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->get();
        }
        foreach ($db as &$t) {
            $t->name = Base::removeImgAlt($t->name);
            $user = Base::getUserData($t->userid);
            if ($user && !empty($user)) {
                $t->writer = $user->name;
                $t->headimage = $user->headimage;
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'msg' => '加载完成']);
    }

    /**
     * 发布问题
     */
    public function writeOneQuestion(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_name = $request->post('question_name');
        $redis7 = Redis::connection('db7');
        $key = 'WriteOneQuestion' . $my_aid;
        if ($redis7->exists($key) && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '提问速度太快了，' . Question::$time_out . '秒后请再次尝试！']);
        }
        $redis7->setEx($key, Question::$time_out, 1);
        $name = mb_substr($question_name, 0, 535);
        $question_name = Base::removeImgAlt($question_name);
        $name = Base::removeImgAlt($name);
        Db::table('question')
            ->insert([
                'name' => $name,
                'question' => $question_name,
                'time' => date('Y-m-d h:i:s', time()),
                'userid' => $my_aid,
                'answer_num' => 0
            ]);
        return json(['code' => 1, 'msg' => '提问成功']);
    }

    /**
     * 查看一个问题详情
     */
    public function loadOneQuestion(Request $request)
    {
        $my_aid = null;
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
        } catch (Exception) {
        }
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $db = Db::table('question')
            ->where('id', $question_id)
            ->where('isdel', 0)
            ->select(Question::$question_db_key)
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '问题不存在']);
        }
        $user = Base::getUserData($db->userid);
        if ($user && !empty($user)) {
            $db->writer = $user->name;
            $db->headimage = $user->headimage;
        }
        $islove = false;
        if ($my_aid) {
            $islove = Db::table('lovequestion')
                ->where('questionid', $question_id)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->exists();
        }
        if ($islove) {
            $db->islove = true;
        } else {
            $db->islove = false;
        }
        $is_can_edit = false;
        if ($my_aid && ($db->userid == $my_aid || Base::judgeIsRoot($my_aid))) {
            $is_can_edit = true;
        }
        $db->question = Base::removeImgAlt($db->question);
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'is_can_edit' => $is_can_edit, 'msg' => '加载完成']);
    }

    /**
     * 删除一个问题
     */
    public function deleteOneQuestion(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('delete_id');
        $question_id = Base::getIdByUid($question_uid);
        $is_my = $this->judgeIsMyQuestion($my_aid, $question_id);
        if (!$is_my) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $db = Db::table('question')
            ->where('id', $question_id)
            ->update(['isdel' => 1]);
        Db::table('lovequestion')
            ->where('questionid', $question_id)
            ->where('userid', $my_aid)
            ->update(['isdel' => 1]);
        Db::table('answer')
            ->where('questionid', $question_id)
            ->update(['isdel' => 1]);
        if ($db) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => -1, 'msg' => '删除失败']);
    }

    /**
     * 更新一个问题
     */
    public function updataOneQuestion(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        Base::dataToUnSafe($data);
        $is_my = $this->judgeIsMyQuestion($my_aid, $data['id']);
        if (!$is_my) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $db = Db::table('question')
            ->where('id', $data['id'])
            ->update(['question' => $data['question']]);
        if ($db) {
            return json(['code' => 1, 'msg' => '更新成功']);
        }
        return json(['code' => -1, 'msg' => '问题信息未更改']);
    }

    /**
     * 收藏问题
     */
    public function collectionOneQuestion(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $islove = Db::table('lovequestion')
            ->where('questionid', $question_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($islove) {
            return json(['code' => -1, 'msg' => '无法重复收藏']);
        }
        $is_historylove = Db::table('lovequestion')
            ->where('questionid', $question_id)
            ->where('userid', $my_aid)
            ->where('isdel', 1)
            ->select('id')
            ->first();
        if ($is_historylove) {
            $res = Db::table('lovequestion')
                ->where('id', $is_historylove->id)
                ->update(['isdel' => 0]);
        } else {
            $res = Db::table('lovequestion')
                ->insert([
                    'questionid' => $question_id,
                    'userid' => $my_aid
                ]);
        }
        if ($res) {
            return json(['code' => 1, 'msg' => '收藏成功']);
        }
        return json(['code' => -1, 'msg' => '收藏失败']);
    }

    /**
     * 取消收藏问题
     */
    public function deleteLoveQuestion(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $islove = Db::table('lovequestion')
            ->where('questionid', $question_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select('id')
            ->first();
        if (!$islove) {
            return json(['code' => -1, 'msg' => '您未收藏']);
        }
        $res = Db::table('lovequestion')
            ->where('id', $islove->id)
            ->update([
                'isdel' => 1
            ]);
        if ($res) {
            return json(['code' => 1, 'msg' => '取消收藏成功']);
        }
        return json(['code' => -1, 'msg' => '取消收藏失败']);
    }


    /**
     * 获取我的提问的问题
     */
    public function getMyQuestionList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $question_id = Base::getIdByUid($question_uid);
        if ($question_id) {
            $db = Db::table('question')
                ->where('userid', $my_aid)
                ->where('id', '<', $question_id)
                ->where('isdel', 0)
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $db = Db::table('question')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->get();
        }
        foreach ($db as &$t) {
            $user = Base::getUserData($t->userid);
            if ($user && !empty($user)) {
                $t->writer = $user->name;
                $t->headimage = $user->headimage;
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'msg' => '加载完成']);
    }

    /**
     * 搜索问题列表
     */
    public function searchList(Request $request)
    {
        $key = $request->post('key');
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        if ($question_id) {
            $db = Db::table('question')
                ->where('name', 'like', '%' . $key . '%')
                ->where('id', '<', $question_id)
                ->where('isdel', 0)
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $db = Db::table('question')
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->get();
        }
        foreach ($db as &$t) {
            $user = Base::getUserData($t->userid);
            if ($user && !empty($user)) {
                $t->writer = $user->name;
                $t->headimage = $user->headimage;
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'msg' => '加载完成']);
    }

    /**
     * 搜索我的提问的问题
     */
    public function searchMyQuestionList(Request $request)
    {
        $key = $request->post('key');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $question_id = Base::getIdByUid($question_uid);
        if ($question_id) {
            $db = Db::table('question')
                ->where('userid', $my_aid)
                ->where('name', 'like', '%' . $key . '%')
                ->where('id', '<', $question_id)
                ->where('isdel', 0)
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $db = Db::table('question')
                ->where('userid', $my_aid)
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit(Question::$limit)
                ->select(Question::$question_list_db_key)
                ->get();
        }
        foreach ($db as &$t) {
            $user = Base::getUserData($t->userid);
            if ($user && !empty($user)) {
                $t->writer = $user->name;
                $t->headimage = $user->headimage;
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'msg' => '加载完成']);
    }
};
