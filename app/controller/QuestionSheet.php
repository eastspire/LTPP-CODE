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
use stdClass;
use support\Db;
use support\Request;
use Tinywan\Jwt\JwtToken;

class QuestionSheet
{
    /**
     * 题单列表数据库展示的字段
     * @var array $question_sheet_list_db_key 题单数据库展示的字段
     */
    static $question_sheet_list_db_key = [
        'id',
        'name',
        'creator_id',
        'time',
        'password',
        'people_num'
    ];

    /**
     * 查看信息
     */
    public function lookOneQuestionSheetData(Request $request)
    {
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        $db = Base::getQuestionSheetData($question_sheet_id);
        if (!$db) {
            return json([
                'code' => -1,
                'data' => [],
                'msg' => '题单不存在'
            ]);
        }
        $db->password = $db->password ? true : false;
        $user_db = Base::getUserData($db->creator_id);
        if (!$user_db) {
            return json([
                'code' => -1,
                'data' => [],
                'msg' => '用户不存在'
            ]);
        }
        $db->creator_name = $user_db->name;
        Base::dataToSafe($db);
        return json([
            'code' => 1,
            'data' => $db,
            'msg' => '加载成功'
        ]);
    }

    /**
     * 后台查看信息
     */
    public function backLookOneQuestionSheetData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        $db = Base::getQuestionSheetData($question_sheet_id);
        if (!$db) {
            return json([
                'code' => -1,
                'data' => [],
                'msg' => '题单不存在'
            ]);
        }
        if (!Base::judgeIsMyQuestionSheet($question_sheet_id, $my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $user_db = Base::getUserData($db->creator_id);
        if (!$user_db) {
            return json([
                'code' => -1,
                'data' => [],
                'msg' => '用户不存在'
            ]);
        }
        $db->creator_name = $user_db->name;
        Base::dataToSafe($db);
        return json([
            'code' => 1,
            'data' => $db,
            'msg' => '加载成功'
        ]);
    }

    /**
     * 获取题目列表
     */
    public function lookOneQuestionSheetProblemList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        $question_sheet_data = Base::getQuestionSheetData($question_sheet_id);
        $res = [];
        if (
            !$question_sheet_data->password  ||
            ($question_sheet_data->password && $this->judgeIsJoin($question_sheet_id, $my_aid)) ||
            ($question_sheet_data->password && ($question_sheet_data->creator_id == $my_aid || Base::judgeIsRoot($my_aid)))
        ) {
            $problem = Base::getQuestionSheetProblemListData($question_sheet_id);
            foreach ($problem as &$tem) {
                $temdb = Db::table('oj')
                    ->where('id', $tem->question_id)
                    ->where('isdel', 0)
                    ->select(Oj::$oj_list_db_key)
                    ->first();
                if (!$temdb) {
                    continue;
                }
                $res[] = $temdb;
            }
        }
        Base::dataToSafe($res);
        return json([
            'code' => 1,
            'data' => $res,
            'msg' => '加载成功'
        ]);
    }

    /**
     * 查看全部题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookAllQuestionSheetList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $db = Db::table('question_sheet')
            ->where('isdel', 0)
            ->select(QuestionSheet::$question_sheet_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('question_sheet')
            ->where('isdel', 0)
            ->count();
        if ($db) {
            foreach ($db as &$tem) {
                $user_db = Base::getUserData($tem->creator_id);
                if ($user_db) {
                    $tem->creator_name = $user_db->name;
                } else {
                    $tem->creator_name = Base::$unknow_user_name;
                }
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 查看我的题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookMyQuestionSheetList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('question_sheet')
            ->where('creator_id', $my_aid)
            ->where('isdel', 0)
            ->select(QuestionSheet::$question_sheet_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('question_sheet')
            ->where('creator_id', $my_aid)
            ->where('isdel', 0)
            ->count();
        if ($db) {
            $user_db = Base::getUserData($my_aid);
            foreach ($db as &$tem) {
                if ($user_db) {
                    $tem->creator_name = $user_db->name;
                } else {
                    $tem->creator_name = Base::$unknow_user_name;
                }
                $tem->password = $tem->password ? true : false;
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 查看我加入的题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookMyJoinQuestionSheetList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('join_question_sheet')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->select('question_sheet_id')
            ->distinct()
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('join_question_sheet')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->distinct()
            ->count();
        if ($db) {
            $user_db = Base::getUserData($my_aid);
            foreach ($db as &$tem) {
                $tem = Base::getQuestionSheetData($tem->question_sheet_id);
                if ($user_db) {
                    $tem->creator_name = $user_db->name;
                } else {
                    $tem->creator_name = Base::$unknow_user_name;
                }
                $tem->password = $tem->password ? true : false;
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 搜索全部题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchAllQuestionSheetList(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $db = Db::table('question_sheet')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(QuestionSheet::$question_sheet_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('question_sheet')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        if ($db) {
            foreach ($db as &$tem) {
                $user_db = Base::getUserData($tem->creator_id);
                if ($user_db) {
                    $tem->creator_name = $user_db->name;
                } else {
                    $tem->creator_name = Base::$unknow_user_name;
                }
                $tem->password = $tem->password ? true : false;
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 搜索我的题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchMyQuestionSheetList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $db = Db::table('question_sheet')
            ->where('creator_id', $my_aid)
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(QuestionSheet::$question_sheet_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('question_sheet')
            ->where('creator_id', $my_aid)
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        if ($db) {
            $user_db = Base::getUserData($my_aid);
            foreach ($db as &$tem) {
                if ($user_db) {
                    $tem->creator_name = $user_db->name;
                } else {
                    $tem->creator_name = Base::$unknow_user_name;
                }
                $tem->password = $tem->password ? true : false;
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 搜索我加入的题单列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchMyJoinQuestionSheetList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $key = $request->post('key');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('join_question_sheet')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->select('question_sheet_id')
            ->distinct()
            ->orderBy('id', 'desc')
            ->get();

        if ($db) {
            $res = [];
            $allnum = 0;
            $resdata = [];
            $user_db = Base::getUserData($my_aid);
            foreach ($db as &$tem) {
                $tem_db = Base::getQuestionSheetData($tem->question_sheet_id);
                $has = Db::table('question_sheet')
                    ->where('id', $tem->question_sheet_id)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0)
                    ->exists();
                if (!$has) {
                    continue;
                }
                ++$allnum;
                if ($user_db) {
                    $tem_db->creator_name = $user_db->name;
                } else {
                    $tem_db->creator_name = Base::$unknow_user_name;
                }
                $tem_db->password = $tem_db->password ? true : false;
                $res[] = $tem_db;
            }
            for ($i = $limit * ($page - 1); $i < $limit * $page && $i < $allnum; ++$i) {
                $resdata[] = $res[$i];
            }
            Base::dataToSafe($db);
            return json(['code' => 1, 'data' => $resdata, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'data' => [], 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 判断是否加入题单
     * @param int $question_sheet_id
     * @param int $my_aid
     */
    private function judgeIsJoin($question_sheet_id, $my_aid)
    {
        try {
            $question_sheet_data = Base::getQuestionSheetData($question_sheet_id);
            if (!$question_sheet_data) {
                return false;
            }
            $db = Db::table('join_question_sheet')
                ->where('question_sheet_id', $question_sheet_id)
                ->where('user_id', $my_aid)
                ->where('isdel', 0)
                ->exists();
            if ($db) {
                return true;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 判断是否加入题单
     */
    public function judgeIsJoinOneQuestionSheet(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        if ($this->judgeIsJoin($question_sheet_id, $my_aid)) {
            return json([
                'code' => 1,
                'msg' => '您已加入题单',
            ]);
        }
        return json([
            'code' => -1,
            'msg' => '您未加入题单',
        ]);
    }

    /**
     * 加入题单
     */
    public function joinOneQuestionSheet(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        $password = $request->post('password');
        $question_sheet_data = Base::getQuestionSheetData($question_sheet_id);
        if (!$question_sheet_data) {
            return json([
                'code' => -1,
                'msg' => '题单不存在',
            ]);
        }
        $db = Db::table('join_question_sheet')
            ->where('question_sheet_id', $question_sheet_id)
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json([
                'code' => -1,
                'msg' => '您已加入题单，禁止重复加入',
            ]);
        }
        if ($question_sheet_data->password != $password) {
            return json([
                'code' => -1,
                'msg' => '密码错误，加入失败',
            ]);
        }
        $res = Base::insertToDb('join_question_sheet', [
            'question_sheet_id' => $question_sheet_id,
            'user_id' => $my_aid
        ]);
        if ($res) {
            Db::table('question_sheet')
                ->where('id', $question_sheet_id)
                ->where('isdel', 0)
                ->increment('people_num', 1);
            Base::updateQuestionSheetDataRedis($question_sheet_id);
            return json([
                'code' => 1,
                'msg' => '加入题单成功',
            ]);
        }
        return json([
            'code' => -1,
            'msg' => '加入题单失败，请重新加入',
        ]);
    }

    /**
     * 添加一个题单
     * @param Request $request 请求
     * @return string $res json
     */
    public function addOneQuestionSheet(Request $request)
    {
        $data = $request->post('data');
        $question_sheet_data = $request->post('question_sheet_data');
        if (!$question_sheet_data) {
            return json(['code' => -1, 'msg' => '题目列表不能为空']);
        }
        $pro_num = 0;
        foreach ($question_sheet_data as &$tem) {
            $tem = Base::getIdByUid($tem);
            $prodb = Base::getOjData($tem);
            if (!$prodb) {
                continue;
            }
            ++$pro_num;
        }
        if ($pro_num <= 0) {
            return json([
                'code' => -1,
                'msg' => '题目列表不能为空',
            ]);
        }
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data['content'] = Base::removeImgAlt($data['content']);
        $res_id = Base::insertToDb('question_sheet', [
            'name' => $data['name'] ?? '',
            'creator_id' => $my_aid,
            'content' => $data['content'] ?? '',
            'password' => $data['password'] ?? ''
        ]);
        Base::updateQuestionSheetDataRedis($res_id);
        if (!$res_id) {
            Base::updateQuestionSheetProblemListDataRedis($res_id);
            return json([
                'code' => -1,
                'msg' => '添加失败',
            ]);
        }
        $has = new stdClass();
        foreach ($question_sheet_data as &$tem) {
            $prodb = Base::getOjData($tem);
            if (!$prodb) {
                continue;
            }
            if (isset($has->$tem) && $has->$tem) {
                continue;
            }
            $has->$tem = true;
            Base::insertToDb('question_sheet_data', [
                'question_sheet_id' => $res_id,
                'question_id' => $tem,
            ]);
        }
        Base::updateQuestionSheetProblemListDataRedis($res_id);
        return json([
            'code' => 1,
            'msg' => '添加成功',
        ]);
    }

    /**
     * 更新一个题单
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateOneQuestionSheet(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        $question_sheet_data = $request->post('question_sheet_data');
        $question_sheet_uid = $data['id'];
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        if (!Base::judgeIsMyQuestionSheet($question_sheet_id, $my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        $pro_num = 0;
        foreach ($question_sheet_data as &$tem) {
            $tem = Base::getIdByUid($tem);
            $prodb = Base::getOjData($tem);
            if (!$prodb) {
                continue;
            }
            ++$pro_num;
        }
        if (!$pro_num) {
            return json([
                'code' => -1,
                'msg' => '题目列表不能为空',
            ]);
        }
        Db::table('question_sheet')
            ->where('id', $question_sheet_id)
            ->where('isdel', 0)
            ->update([
                'name' => $data['name'] ?? '',
                'creator_id' => $my_aid,
                'content' => $data['content'] ?? '',
                'password' => $data['password'] ?? ''
            ]);
        Base::updateQuestionSheetDataRedis($question_sheet_id);
        $data = Base::getUserData($my_aid);
        Db::table('question_sheet_data')
            ->where('question_sheet_id', $question_sheet_id)
            ->update([
                'isdel' => 1
            ]);
        $has = new stdClass();
        foreach ($question_sheet_data as &$tem) {
            $prodb = Base::getOjData($tem);
            if (!$prodb) {
                continue;
            }
            if (isset($has->$tem) && $has->$tem) {
                continue;
            }
            $has->$tem = true;
            Base::insertToDb('question_sheet_data', [
                'question_sheet_id' => $question_sheet_id,
                'question_id' => $tem,
            ]);
        }
        Base::updateQuestionSheetProblemListDataRedis($question_sheet_id);
        return json([
            'code' => 1,
            'msg' => '更新成功',
        ]);
    }

    /**
     * 删除一个题单
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteOneQuestionSheet(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_sheet_uid = $request->post('question_sheet_id');
        $question_sheet_id = Base::getIdByUid($question_sheet_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json([
                'code' => -1,
                'msg' => '权限不足',
            ]);
        }
        Db::table('question_sheet')
            ->where('id', $question_sheet_id)
            ->update([
                'isdel' => 1
            ]);
        Db::table('join_question_sheet')
            ->where('question_sheet_id', $question_sheet_id)
            ->update([
                'isdel' => 1
            ]);
        Db::table('question_sheet_data')
            ->where('question_sheet_id', $question_sheet_id)
            ->update([
                'isdel' => 1
            ]);
        Base::updateQuestionSheetDataRedis($question_sheet_id);
        Base::updateQuestionSheetProblemListDataRedis($question_sheet_id);
        return json([
            'code' => 1,
            'msg' => '删除成功',
        ]);
    }
};
