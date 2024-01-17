<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Codehistory
{
    /**
     * CodeHistory数据库展示用户代码列表的字段（不含具体代码）
     * @var array $code_history_db_key CodeHistory数据库展示用户代码列表的字段（不含具体代码）
     */
    static $code_history_db_key = [
        'id',
        'userid',
        'language',
        'status',
        'time',
        'usetime',
        'usememory'
    ];

    /**
     * CodeHistory数据库展示用户代码列表的字段（含具体代码）
     * @var array $code_history_db_key_has_code CodeHistory数据库展示用户代码列表的字段（含具体代码）
     */
    static $code_history_db_key_has_code = [
        'id',
        'userid',
        'language',
        'status',
        'code',
        'time',
        'usetime',
        'usememory'
    ];

    /**
     * 个人代码提交记录
     * @param Request $request 请求
     * @return string $res json
     */
    public function getMyCodeList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $user_db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if (!$user_db) {
            return json(['code' => -1, 'msg' => '用户不存在', 'data' => [], 'total' => 0]);
        }
        $db = Db::table('codehistory')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select(CodeHistory::$code_history_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $total = Db::table('codehistory')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->count();
        foreach ($db as &$tem) {
            $tem->user = $user_db->name;
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'msg' => '加载完成', 'data' => $db, 'total' => $total]);
    }

    /**
     * 全站代码提交记录
     * @param Request $request 请求
     * @return string $res json
     */
    public function getAllCodeList(Request $request)
    {
        $code_uid = $request->post('code_id');
        $code_id = Base::getIdByUid($code_uid);
        $limit = $request->post('limit');
        $do = $request->post('do');
        if ($limit > Base::$db_get_limit) {
            $limit = Base::$db_get_limit;
        }
        $db = [];
        if (!$code_id) {
            if ($do == 'up') {
                return json(['code' => 1, 'data' => [], 'msg' => '加载完成']);
            } else if ($do == 'down') {
                $db = Db::table('codehistory')
                    ->where('isdel', 0)
                    ->select(CodeHistory::$code_history_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }
        } else {
            $code_db = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('isdel', 0)
                ->select('id')
                ->first();
            if (!$code_db) {
                return json(['code' => 1, 'data' => [], 'msg' => '加载完成']);
            }
            $code_id = $code_db->id;
            if ($do == 'up') {
                $db = Db::table('codehistory')
                    ->where('id', '>', $code_id)
                    ->where('isdel', 0)
                    ->select(CodeHistory::$code_history_db_key)
                    ->orderBy('id', 'asc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
                $db = array_reverse($db);
            } else if ($do == 'down') {
                $db = Db::table('codehistory')
                    ->where('id', '<', $code_id)
                    ->where('isdel', 0)
                    ->select(CodeHistory::$code_history_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get()
                    ->toArray();
            }
        }
        foreach ($db as &$tem) {
            $user_db = Base::getUserData($tem->userid);
            if (!$user_db) {
                $tem->user = '账号已注销';
            } else {
                $tem->user = $user_db->name;
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'msg' => '加载完成']);
    }

    /**
     * 查看具体代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookOneCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        $code_uid = $request->post('code_id');
        $code_id = Base::getIdByUid($code_uid);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        if (!$code_id || !$my_aid) {
            return json([
                'code' => 1,
                'msg' => '参数错误！',
                'data' => [
                    'code' => '参数错误！',
                    'language' => 'C++'
                ]
            ]);
        }
        $code = null;
        // 题目里面查看用户代码
        if ($problem_uid) {
            // 是否有权查看
            $oj_db = Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->select('public')
                ->first();
            $contest = Db::table('contestproblem')
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->select('contestid')
                ->get();
            $iscontest = false;
            foreach ($contest as &$tem) {
                $ontest_db = Db::table('contest')
                    ->where('id', $tem->contestid)
                    ->first();
                if ($ontest_db) {
                    if (strtotime($ontest_db->end) >= time()) {
                        $iscontest = true;
                        break;
                    }
                }
            }
            $code = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->select(CodeHistory::$code_history_db_key_has_code)
                ->first();
            if (!$isroot && Base::judgeIsRobot($code->userid) && $code->userid != $my_aid) {
                return json([
                    'code' => 1,
                    'msg' => '您没有权限查看该代码！',
                    'data' => [
                        'code' => '您没有权限查看该代码！',
                        'language' => 'C++'
                    ]
                ]);
            }
            // 不是管理员，题目不公开或者是竞赛的赛题就不可查看
            if (($oj_db->public != 1 || $iscontest) && !$isroot && $code->userid != $my_aid) {
                return json([
                    'code' => 1,
                    'msg' => '您没有权限查看该代码！',
                    'data' => [
                        'code' => '您没有权限查看该代码！',
                        'language' => $code->language,
                    ]
                ]);
            }
        } else {
            // 非题目普通用户不可查看他人代码
            $code = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->select(CodeHistory::$code_history_db_key_has_code)
                ->first();
        }
        if ($isroot == 1) {
            $code = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('isdel', 0)
                ->select(CodeHistory::$code_history_db_key_has_code)
                ->first();
        }
        if (!$isroot && Base::judgeIsRobot($code->userid) && $code->userid != $my_aid) {
            return json([
                'code' => 1,
                'msg' => '您没有权限查看该代码！',
                'data' => [
                    'code' => '您没有权限查看该代码！',
                    'language' => 'C++'
                ]
            ]);
        }
        if (!$code) {
            return json([
                'code' => -1,
                'msg' => '您没有权限查看该代码！',
                'data' => [
                    'code' => '您没有权限查看该代码！',
                    'language' => 'C++',
                ]
            ]);
        }
        Base::dataToSafe($code);
        return json([
            'code' => 1,
            'msg' => '代码加载完成',
            'data' => [
                'code' => $code->code,
                'language' => $code->language,
            ]
        ]);
    }

    /**
     * 查看具体题目的全部提交代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function getOneProblemCodelist(Request $request)
    {
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $code_db = Db::table('codehistory')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->select(CodeHistory::$code_history_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $total = Db::table('codehistory')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->count();

        foreach ($code_db as &$tem) {
            $user_db = Base::getUserData($tem->userid);
            if ($user_db) {
                $tem->user = $user_db->name;
            }
        }
        Base::dataToSafe($code_db);
        return json(['data' => $code_db, 'total' => $total]);
    }

    /**
     * 搜索具体题目符合条件的提交的代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchOneProblemCodeList(Request $request)
    {
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $key = $request->post('key');
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $user_db = Db::table('user')
            ->where('name', $key)
            ->where('isdel', 0)
            ->select('id')
            ->first();
        $db = [];
        $total = 0;
        if ($user_db) {
            $userid = $user_db->id;
            $db = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('userid', $userid)
                ->where('isdel', 0)
                ->select(CodeHistory::$code_history_db_key)
                ->orderBy('id', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $total = Db::table('codehistory')
                ->where('problemid', $problem_id)
                ->where('userid', $userid)
                ->where('isdel', 0)
                ->count();
        }
        $list = Db::table('codehistory')
            ->orWhere(function ($query) use ($problem_id, $key) {
                $query->where('problemid', $problem_id)
                    ->where('language', $key)
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($problem_id, $key) {
                $query->where('problemid', $problem_id)
                    ->where('status', $key)
                    ->where('isdel', 0);
            })
            ->where('problemid', $problem_id)
            ->select(CodeHistory::$code_history_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $total += Db::table('codehistory')
            ->orWhere(function ($query) use ($problem_id, $key) {
                $query->where('problemid', $problem_id)
                    ->where('language', $key)
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($problem_id, $key) {
                $query->where('problemid', $problem_id)
                    ->where('status', $key)
                    ->where('isdel', 0);
            })
            ->where('problemid', $problem_id)
            ->count();
        foreach ($list as &$tem) {
            $db[] = $tem;
        }
        foreach ($db as &$tem) {
            $temdb = Base::getUserData($tem->userid);
            if ($temdb) {
                $tem->user = $temdb->name;
            } else {
                $tem->user = '无名氏';
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'msg' => '加载完成', 'data' => $db, 'allnum' => $total]);
    }
};
