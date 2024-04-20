<?php

namespace app\controller;

use stdClass;
use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

//problemIndex排名表格中赛题题目信息
class Oj
{
    /**
     * @var array $oj_db_key oj里一道题目展示给用户的字段 
     */
    static $oj_db_key = [
        'id',
        'public',
        'problemName',
        'problemLabe',
        'createrid',
        'problemFrom',
        'problemContent',
        'problemCinTest',
        'problemCoutTest',
        'ACNum',
        'ALLSubmitNum',
        'Time',
        'Memory',
        'ACpoint',
    ];

    /**
     * @var array $oj_db_key oj题库列表展示给用户的字段 
     */
    static $oj_list_db_key = [
        'id',
        'problemName',
        'problemLabe',
        'createrid',
        'problemFrom',
        'ACNum',
        'ALLSubmitNum',
        'ACpoint'
    ];

    /**
     * @var array $oj_back_db_key oj后台里一道题目展示给用户的字段 
     */
    static $oj_back_db_key = [
        'id',
        'public',
        'problemName',
        'problemLabe',
        'createrid',
        'problemFrom',
        'problemContent',
        'problemCinTest',
        'problemCoutTest',
        'ACNum',
        'ALLSubmitNum',
        'Time',
        'Memory',
        'ACpoint',
        'think',
        'code'
    ];

    /**
     * 判断是否是该用户出的题目（root不进行限制）
     * @param int $problem_id 问题id
     * @param string $user_id 用户id
     * @return bool
     */
    static public function judgeIsMyProblem($problem_id, $user_id)
    {
        $prodb = Base::getOjData($problem_id);
        if (!$prodb) {
            return false;
        }
        $isroot = Base::judgeIsRoot($user_id);
        if ($prodb->createrid != $user_id && !$isroot) {
            return false;
        }
        return true;
    }

    /**
     * 用户非竞赛题目进入页面时补全代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookProblemMySolveCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $language = $request->post('language');
        if (!$problem_id || !$language || ($contest_id && $contest_id > 0)) {
            return json(['code' => 0, 'data' => '']);
        }
        $db = Db::table('solveproblem')
            ->where('userid', $my_aid)
            ->where('problemid', $problem_id)
            ->where('language', $language)
            ->orderBy('id', 'desc')
            ->where('isdel', 0)
            ->select('code')
            ->first();
        if ($db) {
            return json(['code' => 1, 'data' => $db->code]);
        }
        return json(['code' => 0, 'data' => '']);
    }

    /**
     * 后台管理员题目列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function backGetProblemList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($my_aid);
        $data = [];
        //为1，管理员可查看自己的题目
        if ($isroot) {
            $data = Db::table('oj')
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->where('isdel', 0)
                ->count();
        } else {
            $data = Db::table('oj')
                ->where('createrid', $my_aid)
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->where('createrid', $my_aid)
                ->where('isdel', 0)
                ->count();
        }
        foreach ($data as &$tem) {
            $hassolve = Db::table('solveproblem')
                ->where('userid', $my_aid)
                ->where('problemid', $tem->id)
                ->where('isdel', 0)
                ->exists();
            if ($hassolve) {
                $tem->hassolve = 1;
            } else {
                $tem->hassolve = 0;
            }
            $tem->ACpoint = round($tem->ACpoint, 2);
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 后台管理员题目搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function backSearchProblem(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);

        $isroot = Base::judgeIsRoot($my_aid);

        if ($isroot) {
            $data = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', $key)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', $key)
                        ->where('isdel', 0);
                })
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', $key)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', $key)
                        ->where('isdel', 0);
                })
                ->count();
        } else {
            $data = Db::table('oj')
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query
                        ->where('createrid', $my_aid)
                        ->where('problemName', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query
                        ->where('createrid', $my_aid)
                        ->where('problemLabe', $key)
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemFrom', $key)
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->select(oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query
                        ->where('createrid', $my_aid)
                        ->where('problemName', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query
                        ->where('createrid', $my_aid)
                        ->where('problemLabe', $key)
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemFrom', $key)
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->count();
        }
        foreach ($data as &$tem) {
            $tem->ACpoint = round($tem->ACpoint, 2);
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return \json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 查看题库列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getProblemList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($my_aid);
        //root管理员可查看所有题目
        if ($isroot) {
            $data = Db::table('oj')
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->where('isdel', 0)
                ->count();
        } else {
            $data = Db::table('oj')
                ->orWhere(function ($query) use ($my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) {
                    $query->where('public', 1)
                        ->where('isdel', 0);
                })
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->orWhere(function ($query) use ($my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) {
                    $query->where('public', 1)
                        ->where('isdel', 0);
                })
                ->count();
        }

        foreach ($data as &$tem) {
            $hassolve = Db::table('solveproblem')
                ->where('userid', $my_aid)
                ->where('problemid', $tem->id)
                ->where('isdel', 0)
                ->exists();
            if ($hassolve) {
                $tem->hassolve = 1;
            } else {
                $tem->hassolve = 0;
            }
            $tem->ACpoint = round($tem->ACpoint, 2);
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 查看具体的一个题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookOneProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);

        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $ismy = Oj::judgeIsMyProblem($problem_id, $my_aid);
        $db = null;
        $begintime = 0;
        //是竞赛的题目，从题库访问不了且竞赛未开始访问不了
        if ($contest_id > 0) {
            $db = Db::table('contestproblem')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->first();
            $contest_db = Db::table('contest')
                ->where('id', $contest_id)
                ->where('isdel', 0)
                ->select('begin')
                ->first();
            if ($db && $contest_db) {
                $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
                if (!$ismycontest && !$ismy && time() < strtotime($begintime)) {
                    return \json(['code' => -1, 'msg' => '竞赛未开始！无法查看赛题！']);
                }
                $begintime = $contest_db->begin;
                $data = Base::getOjData($problem_id);
                $data->ACpoint = round($data->ACpoint ?? 1, 2);
                if (!$ismycontest && time() >= strtotime($begintime)) {
                    $data->problemLabe = '赛题保密';
                    $data->problemFrom = '赛题保密';
                }
                Base::dataToSafe($data);
                return json(['code' => 1, 'data' => $data, 'msg' => '题目加载成功']);
            }
            return \json(['code' => -1, 'data' => [], 'msg' => '信息错误']);
        }
        $data = null;
        $data = Base::getOjData($problem_id);
        if (!$ismy && $data->public != 1) {
            return \json(['code' => -1, 'data' => [], 'msg' => '权限不足']);
        }
        $data->ACpoint = round($data->ACpoint ?? 1, 2);
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'msg' => '题目加载成功']);
    }

    /**
     * 后台查看具体的一个题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function backLookOneProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $ismy = Oj::judgeIsMyProblem($problem_id, $my_aid);
        if (!$ismy) {
            return json(['code' => -1, 'msg' => '无权限！']);
        }
        $db = null;
        $begintime = 0;
        //是竞赛的题目，从题库访问不了且竞赛未开始访问不了
        if ($contest_id > 0) {
            $db = Db::table('contestproblem')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->first();
            $contest_db = Db::table('contest')
                ->where('id', $contest_id)
                ->where('isdel', 0)
                ->select('begin')
                ->first();
            if ($db && $contest_db) {
                $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
                if (!$ismycontest && time() < strtotime($begintime)) {
                    return \json(['code' => -1, 'msg' => '竞赛未开始！无法查看赛题！']);
                }
                $begintime = $contest_db->begin;
                $data = Db::table('oj')
                    ->where('id', $problem_id)
                    ->where('isdel', 0)
                    ->select(Oj::$oj_back_db_key)
                    ->first();
                $data->ACpoint = round($data->ACpoint ?? 1, 2);
                if (!$ismy && ($db && time() >= strtotime($begintime))) {
                    $data->problemLabe = '赛题保密';
                    $data->problemFrom = '赛题保密';
                }
                Base::dataToSafe($data);
                return json(['code' => 1, 'data' => $data, 'msg' => '题目加载成功']);
            } else {
                return \json(['code' => -1, 'data' => [], 'msg' => '信息错误']);
            }
        } else {
            $data = null;
            if ($ismy) {
                $data = Db::table('oj')
                    ->where('id', $problem_id)
                    ->where('isdel', 0)
                    ->select(Oj::$oj_back_db_key)
                    ->first();
            } else {
                $data = Db::table('oj')
                    ->where('id', $problem_id)
                    ->where('public', 1)
                    ->where('isdel', 0)
                    ->select(Oj::$oj_back_db_key)
                    ->first();
            }
            $data->ACpoint = round($data->ACpoint ?? 1, 2);
            Base::dataToSafe($data);
        }
        return json(['code' => 1, 'data' => $data, 'msg' => '题目加载成功']);
    }

    /**
     * 搜索题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchProblem(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if ($isroot) {
            $data = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->select(oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->count();
        } else {
            $data = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->select(oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemName', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key) {
                    $query->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('public', 1)
                        ->where('isdel', 0);
                })
                ->count();
        }

        foreach ($data as &$tem) {
            $hassolve = Db::table('solveproblem')
                ->where('userid', $my_aid)
                ->where('problemid', $tem->id)
                ->where('isdel', 0)
                ->exists();
            if ($hassolve) {
                $tem->hassolve = 1;
            } else {
                $tem->hassolve = 0;
            }
            $tem->ACpoint = round($tem->ACpoint, 2);
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return \json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 添加题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function addProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isadmin = Base::judgeIsAdmin($my_aid);
        if (!$isadmin) {
            return \json(['code' => -1, 'msg' => '权限不足']);
        }
        $data = $request->post('data');
        if (strlen($data['problemName']) <= 2) {
            return \json(['code' => -1, 'msg' => '题目名称长度过短']);
        }
        if (strlen($data['problemName']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目名称长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }
        if (strlen($data['problemLabe']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目标签长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }
        if (strlen($data['problemFrom']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目来源长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }
        unset($data['id']);
        $data['createrid'] = $my_aid;
        $data['id'] = Base::insertToDb('oj', $data);
        if ($data['id']) {
            Base::dataToSafe($data);
            return json(['code' => 1, 'data' => $data, 'msg' => '添加成功']);
        }
        return json(['code' => 0, 'data' => [], 'msg' => '添加失败']);
    }

    /**
     * 删除题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return \json(['code' => -1, 'msg' => '权限不足']);
        }
        $problem_uid = $request->post('delete_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $contest = Db::table('contestproblem')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->pluck('contestid');
        $has = new stdClass;
        $redis4 = Redis::connection('db4');
        foreach ($contest as &$tem) {
            if (isset($has->$tem)) {
                unset($tem);
                continue;
            }
            $has->$tem = 1;
        }

        foreach ($contest as &$t) {
            $iscontest = Db::table('contest')
                ->where('id', $t)
                ->where('isdel', 0)
                ->select('type')
                ->first();
            if (!$iscontest) {
                Db::table('contestproblem')
                    ->where('contestid', $t)
                    ->where('isdel', 0)
                    ->update(['isdel' => 1]);
                Db::table('contestrank')
                    ->where('contestid', $t)
                    ->where('isdel', 0)
                    ->update(['isdel' => 1]);
                Db::table('joincontest')
                    ->where('contestid', $t)
                    ->where('isdel', 0)
                    ->update(['isdel' => 1]);
                continue;
            }
            //缓存清理
            $redis4->del('ContestRank' . $t . 'peopledata');
            $redis4->del('ContestRank' . $t . 'timedata');
            $redis4->del('ContestRank' . $t . 'echartsrank');
            $redis4->del('Contest' . $t . 'resarray');
        }
        $deldb = Db::table('oj')
            ->where('id', $problem_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('contestrank')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('solveproblem')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('contestproblem')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('question_sheet_data')
            ->where('question_id', $problem_id)
            ->update([
                'isdel' => 1
            ]);
        $md5_problem_id = Base::doubleMd5($problem_id);
        Base::deleteAllFile(Base::$testdata_path . $md5_problem_id . '/');
        Base::updateOjDataRedis($problem_id);
        if ($deldb) {
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => 0, 'msg' => '删除失败']);
    }

    /**
     * 更新题目
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        Base::dataToUnSafe($data);
        $ismypro = $this->judgeIsMyProblem($data['id'], $my_aid);
        if ($ismypro != 1) {
            return \json(['code' => -1, 'msg' => '权限不足']);
        }
        if (strlen($data['problemName']) <= 2) {
            return \json(['code' => -1, 'msg' => '题目名称长度过短']);
        }
        if (strlen($data['problemName']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目名称长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }
        if (strlen($data['problemLabe']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目标签长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }
        if (strlen($data['problemFrom']) >= Base::$other_name_len_limit) {
            return \json(['code' => -1, 'msg' => '题目来源长度限制 ' . Base::$other_name_len_limit . ' 字符以内']);
        }

        Db::table('oj')
            ->where('id', $data['id'])
            ->where('isdel', 0)
            ->update($data);
        Base::updateOjDataRedis($data['id']);
        return json(['code' => 1, 'msg' => '更新成功']);
    }

    /**
     * 测试样例下载
     * @param Request $request
     */
    public function downloadTest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $md5_problem_id = Base::doubleMd5($problem_id);
        // 鉴权
        $ismypro = $this->judgeIsMyProblem($problem_id, $my_aid);
        if ($ismypro != 1) {
            return \json(['code' => -1, 'msg' => '权限不足']);
        }
        $db = Base::getOjData($problem_id);
        if (!$db) {
            return json(['code' => -1, 'msg' => '题目不存在！']);
        }
        $data = Base::getOjTestDataList($problem_id);
        if (!sizeof($data)) {
            return json(['code' => -1, 'msg' => '题目测试样例不存在！']);
        }
        $path = Base::$testdata_path . $md5_problem_id . '/';
        $alltestpath = Base::$testdata_path . $md5_problem_id . '/';
        $test_data_list = Base::getOjTestDataList($problem_id);
        Base::deleteAllFile($path);
        Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
        //文件路径加文件名称
        $zip_path = '/tmp/file/' . (uniqid() . mt_rand(1, 100000) . time()) . '/' . $db->problemName . '.zip';
        Base::judgeCreatPath($zip_path);
        Base::make_zip_file_for_folder($zip_path, $path); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
        return response('')->download($zip_path);
    }
};
