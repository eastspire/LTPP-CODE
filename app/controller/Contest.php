<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-19 23:50:37
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-09 08:14:52
 * @FilePath: \LTPP-CODE\app\controller\Contest.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;
use stdClass;
use Webman\RedisQueue\Redis as RedisQueue;

class Contest
{
    /**
     * @var array $symbol 分词依据
     */
    static $symbol = [
        '{',
        '}',
        '(',
        ')',
        ' ',
        ';',
        '#',
        '/',
        '//',
        '/*',
        '*/',
        '<',
        '>',
        '?',
        ':',
        '\'',
        '"',
        '`',
        '.',
        ',',
        '[',
        ']',
        '&',
        '|',
        '!',
        '~',
        '-'
    ];

    /**
     * @var array $contest_type_list 竞赛赛制类型列表
     */
    static $contest_type_list = ['SQS', 'ACM', 'OI', 'IOI'];

    /**
     * @var array $db_get_limit 排名条数限制
     */
    static $db_get_limit = 200;

    /**
     * @var array $contest_db_key 竞赛页面展示给用户的字段 
     */
    static $contest_db_key = [
        'id',
        'name',
        'content',
        'begin',
        'end',
        'creater',
        'allpeople',
        'type',
        'createrid'
    ];

    /**
     * @var array $contest_list_db_key 竞赛列表展示给用户的字段 
     */
    static $contest_list_db_key = [
        'id',
        'name',
        'content',
        'begin',
        'end',
        'creater',
        'allpeople',
        'type',
        'createrid'
    ];

    /**
     * @var array $contestproblem_db_key 竞赛题目数据库展示字段
     */
    static $contestproblem_db_key = [
        'contestid',
        'problemid'
    ];

    /**
     * 缓冲区key名称
     */
    static $redis_array_name = 'LTPP_CONTEST_RANK';

    /**
     * 发送更新排名消息
     * @param int $contest_id
     */
    static public function sendUpdateRankMQ($contest_id = 0)
    {
        try {
            $redis24 = Redis::connection('db24');
            $redis24->rpush(Contest::$redis_array_name, ...[$contest_id]);
        } catch (Exception $e) {
            $msg = '添加排名缓冲区异常：' . $e->getMessage();
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), $msg);
        }
    }

    /**
     * 判断是否是该用户出的竞赛，以及root不进行限制
     * @param int $contest_id 竞赛ID
     * @return bool $res true|false
     */
    static public function judgeIsMyContest($contest_id, $user_id)
    {
        if (!$contest_id || !$user_id) {
            return false;
        }
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return false;
        }
        $isroot = Base::judgeIsRoot($user_id);
        if ($contest_db->createrid != $user_id && !$isroot) {
            return false;
        }
        return true;
    }

    /**
     * 前端判断是否是该用户出的竞赛，以及root不进行限制
     * @param int $contest_id 竞赛ID
     * @return string $res true|false
     */
    public function frontendJudgeIsMyContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        return json(['code' => 1, 'data' => Contest::judgeIsMyContest($contest_id, $my_aid) ? 1 : 0]);
    }

    /**
     * 管理员更新添加竞赛的题库列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function backGetContestProblemList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($my_aid);
        //为1，管理员可查看所有题目
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
                ->where('public', 1)
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->orderBy('id', 'asc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('oj')
                ->where('public', 1)
                ->where('isdel', 0)
                ->count();
        }
        foreach ($data as &$tem) {
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }

    /**
     * 管理员更新添加竞赛的搜索题库列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function backContestSearchProblem(Request $request)
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
                ->orderBy('id', 'asc')
                ->select(Oj::$oj_list_db_key)
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
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->select(Oj::$oj_list_db_key)
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
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemName', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemLabe', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->orWhere(function ($query) use ($key, $my_aid) {
                    $query->where('createrid', $my_aid)
                        ->where('problemFrom', 'like', '%' . $key . '%')
                        ->where('isdel', 0);
                })
                ->count();
        }
        foreach ($data as &$tem) {
            if ($tem->ACpoint > 1) {
                $tem->ACpoint = 1;
            }
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'allnum' => $allnum]);
    }


    /**
     * 报名竞赛
     * @param Request $request 请求
     * @return string $res json
     */
    public function joinContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '无该竞赛']);
        }
        $now = time();
        if (strtotime($contest_db->end) < $now) {
            return json(['code' => -1, 'msg' => '竞赛已经结束无法报名']);
        }
        $isjoin = Db::table('joincontest')
            ->where('userid', $my_aid)
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->exists();
        if ($isjoin) {
            return json(['code' => -1, 'msg' => '您已报名该竞赛，无法多次报名']);
        }
        $res = Db::table('joincontest')
            ->insert([
                'userid' => $my_aid,
                'contestid' => $contest_id
            ]);
        Db::table('contest')
            ->where('id', $contest_id)
            ->where('isdel', 0)
            ->increment('allpeople', 1);
        if ($res) {
            Contest::sendUpdateRankMQ($contest_id);
            Base::updateContestDataRedis($contest_id);
            return json(['code' => 1, 'msg' => '报名成功']);
        }
        return json(['code' => -1, 'msg' => '报名失败，请重新尝试！']);
    }

    /**
     * 后台查看所有竞赛列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function backGetContestList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($my_aid);
        if ($isroot) {
            $db = Db::table('contest')
                ->where('isdel', 0)
                ->select(Contest::$contest_list_db_key)
                ->orderBy('id', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('contest')
                ->where('isdel', 0)
                ->count();
        } else {
            $db = Db::table('contest')
                ->where('isdel', 0)
                ->where('createrid', $my_aid)
                ->orderBy('id', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('contest')
                ->where('createrid', $my_aid)
                ->where('isdel', 0)
                ->count();
        }
        Base::dataToSafe($db);
        if ($db) {
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 后台搜索竞赛
     * @param Request $request 请求
     * @return string $res json
     */
    public function backSearchContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($my_aid);
        if ($isroot) {
            $db = Db::table('contest')
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->select(Contest::$contest_list_db_key)
                ->orderBy('id', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('contest')
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->count();
        } else {
            $db = Db::table('contest')
                ->where('name', 'like', '%' . $key . '%')
                ->where('createrid', $my_aid)
                ->where('isdel', 0)
                ->select(Contest::$contest_list_db_key)
                ->orderBy('id', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('contest')
                ->where('name', 'like', '%' . $key . '%')
                ->where('createrid', $my_aid)
                ->where('isdel', 0)
                ->count();
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'msg' => '搜索到' . $allnum . '条信息', 'data' => $db, 'allnum' => $allnum]);
    }

    /**
     * 查看所有竞赛列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getContestList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $db = Db::table('contest')
            ->where('isdel', 0)
            ->select(Contest::$contest_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('contest')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($db);
        if ($db) {
            return json(['code' => 1, 'data' => $db, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 搜索竞赛
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchContest(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $db = Db::table('contest')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(Contest::$contest_list_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('contest')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($db);
        return json(['code' => 1, 'msg' => '搜索到' . $allnum . '条信息', 'data' => $db, 'allnum' => $allnum]);
    }

    /**
     * 后台查看具体一个竞赛信息,转换格式成时间戳，为了日历可以解析出来时间
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$ismycontest) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $db = Base::getContestData($contest_id);
        Base::dataToSafe($db);
        if ($db) {
            $db->begin = strtotime($db->begin) * 1000;
            $db->end = strtotime($db->end) * 1000;
            return json(['code' => 1, 'msg' => '竞赛加载成功', 'data' => $db]);
        }
        return json(['code' => -1, 'msg' => '竞赛加载失败']);
    }

    /**
     * 用户查看具体一个竞赛信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function userLookContest(Request $request)
    {
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);

        $db = Base::getContestData($contest_id);
        Base::dataToSafe($db);
        if ($db) {
            return json(['code' => 1, 'msg' => '竞赛加载成功', 'data' => $db]);
        }
        return json(['code' => -1, 'msg' => '竞赛加载失败']);
    }

    /**
     * 竞赛界面查看具体一个竞赛的题目列表及题目信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookContestProblem(Request $request)
    {
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        //是否报名判断
        $isjoin = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        $is_me_contest = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$isjoin && !$is_me_contest) {
            return json(['code' => -1, 'msg' => '您未报名该竞赛']);
        }
        $now = time();
        //竞赛是否开始
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '无该竞赛']);
        }
        $begintime = strtotime($contest_db->begin);
        if ($now < $begintime) {
            $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
            if (!$ismycontest) {
                return json(['code' => -1, 'msg' => '竞赛未开始！无法查看赛题列表！']);
            }
        }

        $redis4 = Redis::connection('db4');
        //缓存存在读取缓存
        if ($redis4->get('Contest' . $contest_id . 'problemdata' . $my_aid)) {
            $redispro = json_decode($redis4->get('Contest' . $contest_id . 'problemdata' . $my_aid) ?? '', true);
            return json(['code' => 1, 'msg' => '赛题列表加载完成', 'data' => $redispro]);
        }

        $db = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->select(Contest::$contestproblem_db_key)
            ->where('isdel', 0)
            ->get();

        $res = array();
        foreach ($db as &$tem) {
            $temdb = Db::table('oj')
                ->where('id', $tem->problemid)
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->first();
            if (!$temdb) {
                continue;
            }

            if ($contest_db->type == 'OI') {
                $temdb->hasac = 0;
            } else {
                $hasac = Db::table('contestrank')
                    ->where('userid', $my_aid)
                    ->where('contestid', $contest_id)
                    ->where('problemid', $tem->problemid)
                    ->where('score', 100)
                    ->where('submittime', '>=', $contest_db->begin)
                    ->where('submittime', '<=', $contest_db->end)
                    ->where('isdel', 0)
                    ->exists();
                if ($hasac) {
                    $temdb->hasac = 1;
                } else {
                    $temdb->hasac = 0;
                }
            }
            $res[] = $temdb;
        }
        Base::dataToSafe($res);
        //存入缓存
        $redis4->set('Contest' . $contest_id . 'problemdata' . $my_aid, json_encode($res));
        return json(['code' => 1, 'msg' => '竞赛题目列表加载完成', 'data' => $res]);
    }

    /**
     * 后台查看具体一个竞赛题目列表及题目信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function backLookContestProblem(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$ismycontest) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $db = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select(Contest::$contestproblem_db_key)
            ->get();
        $res = array();
        foreach ($db as &$tem) {
            $temdb = Db::table('oj')
                ->where('id', $tem->problemid)
                ->where('isdel', 0)
                ->select(Oj::$oj_list_db_key)
                ->first();
            if (!$temdb) {
                continue;
            }
            $res[] = $temdb;
        }
        Base::dataToSafe($res);
        return json(['code' => 1, 'msg' => '竞赛题目已选择列表加载完成', 'data' => $res]);
    }

    /**
     * 创建竞赛
     */
    public function addContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isadmin = Base::judgeIsAdmin($my_aid);
        if (!$isadmin) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $data = $request->post('data');
        $time = $request->post('time');
        $defaultnum = $request->post('defaultnum');
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            $defaultnum = 0;
        }
        if (sizeof($data) < 3) {
            return json(['code' => -1, 'msg' => '竞赛信息不完整']);
        }
        if (strlen($data['name']) <= 2) {
            return json(['code' => -1, 'msg' => '竞赛名称长度过短']);
        }
        if ($time == '') {
            return json(['code' => -1, 'msg' => '请设置竞赛开始时间和结束时间']);
        }
        if ($time[0] == '' || $time[1] == '') {
            return json(['code' => -1, 'msg' => '请设置竞赛开始时间和结束时间']);
        }
        $begin = (int) ($time[0] / 1000);
        $end = (int) ($time[1] / 1000);
        if ($end - $begin < 600) {
            return json(['code' => -1, 'msg' => '竞赛时长必须大于等于10分钟']);
        }
        $begin = date('Y-m-d H:i:s', (int) $begin);
        $end = date('Y-m-d H:i:s', (int) $end);
        $data['begin'] = $begin;
        $data['end'] = $end;
        $data['createrid'] = $my_aid;
        $userdb = Base::getUserData($my_aid);
        if ($userdb) {
            $data['creater'] = $userdb->name;
        } else {
            return json(['code' => -1, 'msg' => '该账号不存在']);
        }
        //插入竞赛信息
        $res_id = Base::insertToDb('contest', $data);
        // 缓存竞赛
        Base::updateContestDataRedis($res_id);

        $problemdata = $request->post('problemdata');
        foreach ($problemdata as &$tem) {
            $tem = Base::getIdByUid($tem);
        }
        $postpronum = 0;
        $insert_pro = [];
        $has_pro = new stdClass;
        foreach ($problemdata as &$tem) {
            $prodb = Db::table('oj')
                ->where('id', $tem)
                ->where('isdel', 0)
                ->exists();
            // 题目不存在则跳过
            if (!$prodb || (isset($has_pro->$tem) && $has_pro->$tem == 1)) {
                continue;
            }
            $db = Db::table('contestproblem')
                ->where('problemid', $tem)
                ->where('contestid', $res_id)
                ->where('isdel', 0)
                ->exists();
            //不存在就插入
            if (!$db) {
                $has_pro->$tem = 1;
                $insert_pro[] = [
                    'problemid' => $tem,
                    'contestid' => $res_id
                ];
                ++$postpronum;
            }
        }
        if ($postpronum <= 0) {
            return json(['code' => -1, 'msg' => '赛题不能为空']);
        }
        Db::table('contestproblem')
            ->insert($insert_pro);
        // 默认参加竞赛人员
        if ($defaultnum) {
            if (!is_numeric($defaultnum)) {
                return json(['code' => -1, 'msg' => '默认参赛人数必须是整数']);
            }
            $total = Db::table('user')->where('isdel', 0)->count();
            if ($defaultnum > $total) {
                $defaultnum = $total;
            }
            $userdb = Db::table('user')
                ->where('email', Base::getRobotEmail())
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit($defaultnum)
                ->pluck('id')
                ->toArray();
            $sum = sizeof($userdb);
            $insert_user = [];
            $cnt_i = 0;
            foreach ($userdb as &$tem) {
                ++$cnt_i;
                $insert_user[] = [
                    'userid' => $tem,
                    'contestid' => $res_id,
                ];
                if ($cnt_i % 888 == 0 && !empty($insert_user)) {
                    Db::table('joincontest')->insert($insert_user);
                    $insert_user = [];
                    $cnt_i = 1;
                }
            }
            if (!empty($insert_user)) {
                Db::table('joincontest')->insert($insert_user);
            }
            $insert_user = [];
            Db::table('contest')
                ->where('id', $res_id)
                ->increment('allpeople', $sum);
        }
        // 参赛用户使用缓存
        $joinuser = Db::table('joincontest')
            ->where('contestid', $res_id)
            ->select('userid')
            ->get();
        $redis4 = Redis::connection('db4');
        foreach ($joinuser as &$tem) {
            $proary = array();
            foreach ($problemdata as &$pro) {
                $problem = Db::table('oj')
                    ->where('id', $pro)
                    ->where('isdel', 0)
                    ->select(Oj::$oj_list_db_key)
                    ->first();
                if (!$problem) {
                    continue;
                }
                $problem->hasac = 0;
                $proary[] = $problem;
            }
            Base::dataToSafe($proary);
            $redis4->set('Contest' . $res_id . 'problemdata' . $tem->userid, json_encode($proary));
        }
        Base::updateContestDataRedis($res_id);
        Contest::sendUpdateRankMQ($res_id);
        return json(['code' => 1, 'msg' => '竞赛添加成功']);
    }

    /**
     * 取消机器人已完成竞赛     
     */
    public function resetRobotFinishContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '竞赛不存在！']);
        }
        $now = date('Y-m-d H:i:s', time());
        if ($now < $contest_db->begin) {
            return json(['code' => -1, 'msg' => '竞赛未开始，请修改竞赛开始时间！']);
        }
        if ($now > $contest_db->end) {
            return json(['code' => -1, 'msg' => '竞赛已结束，请修改竞赛结束时间！']);
        }
        // 竞赛未结束，更新机器人竞赛
        Db::table('robotcontestfinish')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        $redis27 = Redis::connection('db27');
        $key = Base::$robot_contest_redis_front . $contest_id;
        $redis27->del($key);
        return json(['code' => 1, 'msg' => '操作成功！']);
    }

    /**
     * 更新竞赛
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        $time = $request->post('time');
        $contest_uid = $data['id'];
        $contest_id = Base::getIdByUid($contest_uid);
        $data['id'] = $contest_id;
        $ismycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$ismycontest) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        if ($time[0] == '' || $time[1] == '') {
            return json(['code' => -1, 'msg' => '请设置竞赛开始时间和结束时间']);
        }
        $begin = $time[0] / 1000;
        $end = $time[1] / 1000;
        if ($end - $begin < 600) {
            return json(['code' => -1, 'msg' => '竞赛时长必须大于等于10分钟']);
        }
        $begin = date('Y-m-d H:i:s', $begin);
        $end = date('Y-m-d H:i:s', $end);
        $data['begin'] = $begin;
        $data['end'] = $end;
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '竞赛不存在！']);
        }
        $now = date('Y-m-d H:i:s', time());
        if ($contest_db->end < $data['end'] && $now <= $data['end']) {
            // 时间推迟且竞赛未结束，则更新机器人竞赛
            Db::table('robotcontestfinish')
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            $redis27 = Redis::connection('db27');
            $key = Base::$robot_contest_redis_front . $contest_id;
            $redis27->del($key);
        }
        //更新竞赛信息
        Db::table('contest')
            ->where('id', $contest_id)
            ->where('isdel', 0)
            ->update(
                [
                    'name' => $data['name'],
                    'content' => $data['content'],
                    'begin' => $data['begin'],
                    'end' => $data['end'],
                    'type' => $data['type']
                ]
            );
        $problemdata = $request->post('problemdata');
        foreach ($problemdata as &$tem) {
            $tem = Base::getIdByUid($tem);
        }

        //先清空之前该竞赛题目，再插入新竞赛题目
        Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        if (!$problemdata) {
            return json(['code' => -1, 'msg' => '竞赛题目不能为空！']);
        }
        foreach ($problemdata as &$tem) {
            $prodb = Db::table('oj')
                ->where('id', $tem)
                ->where('isdel', 0)
                ->exists();

            // 题目不存在则跳过
            if (!$prodb) {
                continue;
            }
            $db = Db::table('contestproblem')
                ->where('problemid', $tem)
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->exists();
            //不存在就插入，一题在一场竞赛仅允许出现一次
            if (!$db) {
                Db::table('contestproblem')
                    ->insert(['problemid' => $tem, 'contestid' => $contest_id]);
            }
        }
        //竞赛删除清空该竞赛全部用户缓存
        $redis4 = Redis::connection('db4');
        $joinuser = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->get();
        foreach ($joinuser as &$tem) {
            $redis4->del('Contest' . $contest_id . 'problemdata' . $tem->userid);
        }
        Base::updateContestDataRedis($contest_id);
        return json(['code' => 1, 'msg' => '竞赛更新成功']);
    }

    /**
     * 删除竞赛
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteContest(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('delete_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        RedisQueue::send(Base::$redis_queue_delete_contest_name, [
            'contest_id' => $contest_id
        ]);
        return json(['code' => 1, 'msg' => '竞赛删除任务已提交']);
    }

    /**
     * 判断是否报名
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeIsJoin(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $isjoin = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($isjoin) {
            return json(['code' => 1, 'msg' => '您已经报名该竞赛']);
        }
        return json(['code' => -1, 'msg' => '您未报名该竞赛']);
    }

    /**
     * 查看个人报名竞赛列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function myJoinContest(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('joincontest')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->distinct()
            ->select('id', 'contestid')
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('joincontest')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select('userid')
            ->distinct()
            ->count();

        $res = array();
        foreach ($db as &$tem) {
            $temdb = Base::getContestData($tem->contestid);
            //如果竞赛存在
            if ($temdb) {
                //如果数组没有该竞赛，加入该竞赛
                $res[] = $temdb;
            } else {
                Db::table('joincontest')
                    ->where('contestid', $tem->contestid)
                    ->where('isdel', 0)
                    ->update(['isdel' => 1]);
            }
        }
        Base::dataToSafe($res);
        if ($db) {
            return json(['code' => 1, 'data' => $res, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 个人报名竞赛列表搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchMyJoinContest(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        $key = $request->post('key');
        Base::judgePageLimitIsSafe($page, $limit);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('joincontest')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->distinct()
            ->select('id', 'contestid')
            ->orderBy('id', 'desc')
            ->get();
        $allnum = 0;
        $all_list = array();
        foreach ($db as &$tem) {
            $temdb = Db::table('contest')
                ->where('id', $tem->contestid)
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->select(Contest::$contest_list_db_key)
                ->first();
            //如果存在
            if ($temdb) {
                $all_list[] = $temdb;
                ++$allnum;
            }
        }

        $res = [];
        $begin = ($page - 1) * $limit;
        for ($i = $begin; $i < $begin + $limit && $i < $allnum; ++$i) {
            $res[] = $all_list[$i];
        }
        Base::dataToSafe($res);
        if ($db) {
            return json(['code' => 1, 'data' => $res, 'msg' => '列表加载成功', 'allnum' => $allnum]);
        }
        return json(['code' => 0, 'msg' => Base::$no_more_msg, 'allnum' => 0]);
    }

    /**
     * 竞赛Echarts实时排名
     * @param Request $request 请求
     * @return string $res json
     */
    public function getContestRank(Request $request)
    {
        try {
            $contest_uid = $request->post('contest_id');
            $contest_id = Base::getIdByUid($contest_uid);
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return json(['code' => -1, 'peopledata' => [], 'timedata' => [], 'data' => [], 'msg' => '竞赛不存在！']);
            }
            if ($contest_db->type == 'OI') {
                return json(['code' => -1, 'peopledata' => [], 'timedata' => [], 'data' => [], 'msg' => 'OI赛制无法查看可视化排名!']);
            }
            $begintime = strtotime($contest_db->begin);
            $isbegin = false;
            if (time() >= $begintime) {
                $isbegin = true;
            }
            if (!$isbegin) {
                // 未开始
                return json(['code' => -1, 'peopledata' => [], 'timedata' => [], 'data' => [], 'msg' => '竞赛未开始！无法查看排名!']);
            }
            $json = Base::getContestRankEchartsData($contest_id);
            if (!$json) {
                return json(['code' => -1, 'peopledata' => [], 'timedata' => [], 'data' => [], 'msg' => '暂无排名！']);
            }
            $peopledata = $json['peopledata'];
            $timedata = $json['timedata'];
            $data = $json['data'];
            return json(['code' => 1, 'peopledata' => $peopledata, 'timedata' => $timedata, 'data' => $data, 'msg' => '统计信息完成！']);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【getContestRank】</strong>运行错误：' . $e->getMessage());
        }
        return json(['code' => 1, 'peopledata' => [], 'timedata' => [], 'data' => [], 'msg' => '信息计算中！']);
    }

    /**
     * 计算Echarts实时排名
     * @param Request $request 请求
     * @return void
     */
    static public function contestIdGetRankEcharts($contest_id = 0)
    {
        //x轴时间分成几块
        $div = 20;
        //竞赛id
        $redis4 = Redis::connection('db4');
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db || $contest_db->type == 'OI') {
            return;
        }
        //判断竞赛是否结束
        $begintime = strtotime($contest_db->begin);
        $endtime = strtotime($contest_db->end);

        //排名锁存在则返回
        $lockoneecharts = 'contestranklockecharts' . $contest_id;
        //加锁
        $lock_res = $redis4->setNx($lockoneecharts, 1);
        if (!$lock_res) {
            return;
        }

        //参与人员id名单
        $joinpeople = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->select('userid')
            ->distinct()
            ->get();
        $arr_joinpeople = $joinpeople->toArray();
        if (sizeof($arr_joinpeople) > 40) {
            // 解锁
            $redis4->del($lockoneecharts);
            return;
        }
        //参赛人员名称名单
        $respeople = array();
        $resdata = array();
        foreach ($joinpeople as &$tem) {
            $temdb = Base::getUserDataFromDb($tem->userid);
            if ($temdb) {
                $respeople[] = $temdb->name;
            }
        }

        //竞赛时间
        $begintime = $contest_db->begin;
        //x轴右端点取当前时间和结束时间最小的一个
        $endtime = date('Y-m-d H:i:s', min(strtotime($contest_db->end), time()));
        $numbegintime = strtotime($begintime);
        $numendtime = strtotime($endtime);
        $timearray = array();
        //x轴分$div段
        $smalltime = ($numendtime - $numbegintime) / $div;

        //全场竞赛全程时间x轴坐标数组（时间戳）
        for ($i = $numbegintime; $i <= $numendtime;) {
            $timearray[] = $i;
            $i += $smalltime;
        }
        //确保时间数组最后是当前时间和结束时间较小那个，不会产生小于这两者的情况，便于后面计算不会漏算
        $timearray[$div] = min(strtotime($contest_db->end), time());

        //竞赛未开始
        if ($numbegintime > time()) {
            $endtime = date('Y-m-d H:i:s', strtotime($contest_db->end));
            $numendtime = strtotime($endtime);
            $smalltime = ($numendtime - $numbegintime) / $div;
            //时间x轴坐标数组（格式化）
            $restimedata = array();
            for ($i = $numbegintime; $i <= $numendtime;) {
                $temtime = date('Y-m-d H:i:s', $i);
                $restimedata[] = $temtime;
                $i += $smalltime;
            }
            $resdata = array();
            foreach ($joinpeople as &$people) {
                $temdata = array();
                for ($i = 0; $i < $div; $i++) {
                    $temdata[] = 0;
                }
                $resdata[] = $temdata;
            }
            Base::dataToSafe($respeople);
            Base::dataToSafe($resdata);
            // 竞赛未开始存入缓存
            $json = [
                'peopledata' => $respeople,
                'timedata' => $restimedata,
                'data' => $resdata,
            ];
            Base::updateContestRankEcharts($contest_id, $json);
            // 解锁
            $redis4->del($lockoneecharts);
            return;
        }

        //没有用户提交代码初始化
        //时间x轴坐标数组（格式化）
        $restimedata = array();
        for ($i = $numbegintime; $i <= $numendtime;) {
            $temtime = date('Y-m-d H:i:s', (int) $i);
            $restimedata[] = $temtime;
            $i += $smalltime;
        }
        // 竞赛题目
        $problem_list = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('problemid')
            ->distinct()
            ->get()
            ->toArray();
        $problem_list_len = sizeof($problem_list);
        $resdata = array();
        //用户各个时间段内AC的数目
        //按照用户顺序得到AC数组
        foreach ($joinpeople as &$people) {
            $temarray = array();
            for ($i = 0; $i <= $div; ++$i) {
                $temarray[] = 0;
            }
            //本次竞赛所有提交记录
            $allusersubmit = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('userid', $people->userid)
                ->where('score', 100)
                ->where('submittime', '>=', $contest_db->begin)
                ->where('submittime', '<=', $contest_db->end)
                ->where('isdel', 0)
                ->select('submittime', 'problemid')
                ->get();
            $proobj = new stdClass;
            $arr_allusersubmit = $allusersubmit->toArray();
            $sublen = sizeof($arr_allusersubmit);
            for ($i = 0; $i < $sublen; ++$i) {
                $proid = $arr_allusersubmit[$i]->problemid;
                if (!isset($proobj->$proid) || $proobj->$proid != 1) {
                    $proobj->$proid = 1;
                    $problem_delete = true;
                    for ($j = 0; $j < $problem_list_len; ++$j) {
                        $tem_pro_id = $problem_list[$j]->problemid;
                        if ($tem_pro_id == $proid) {
                            $problem_delete = false;
                            break;
                        }
                    }
                    // 题目在竞赛中删除了
                    if ($problem_delete) {
                        continue;
                    }
                    //用户提交时间
                    $thismaxtime = strtotime($arr_allusersubmit[$i]->submittime);
                    //从1开始，位置零表示刚开始，不用遍历
                    for ($j = 1; $j <= $div; ++$j) {
                        //提交时间在该时间段内且状态为AC，AC数加一
                        if ($thismaxtime <= $timearray[$j]) {
                            ++$temarray[$j];
                        }
                    }
                }
            }
            //存入二维数组
            $resdata[] = $temarray;
        }
        $restimedata = array();
        //时间x轴坐标数组（格式化）
        for ($i = $numbegintime; $i <= $numendtime;) {
            $temtime = date('Y-m-d H:i:s', (int) $i);
            $restimedata[] = $temtime;
            $i += $smalltime;
        }
        //保证前端展示时间数组右边界是当前时间和结束时间较小者
        $restimedata[$div] = date('Y-m-d H:i:s', min(strtotime($contest_db->end), time()));

        //排名存入缓存
        Base::dataToSafe($respeople);
        Base::dataToSafe($resdata);
        // 竞赛未开始存入缓存
        $json = [
            'peopledata' => $respeople,
            'timedata' => $restimedata,
            'data' => $resdata,
        ];
        Base::updateContestRankEcharts($contest_id, $json);
        $redis4->del($lockoneecharts);
    }

    /**
     * 竞赛ACM，SQS赛制表格排名
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookAcmExcelRank(Request $request)
    {
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
            $contest_uid = $request->post('contest_id');
            $page = $request->post('page');
            $limit = $request->post('limit');
            $contest_id = Base::getIdByUid($contest_uid);
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return json(['code' => -1, 'msg' => '竞赛不存在！', 'data' => [], 'problemIndex' => []]);
            }
            if ($contest_db->type != 'ACM' && $contest_db->type != 'SQS') {
                return json(['code' => -1, 'msg' => '竞赛类型与预期不匹配！', 'data' => [], 'problemIndex' => []]);
            }
            $begintime = strtotime($contest_db->begin);
            $isbegin = false;
            if (time() >= $begintime) {
                $isbegin = true;
            }
            if (!$isbegin) {
                // 未开始
                return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => '竞赛未开始！无法查看排名！']);
            }
            $json = Base::getContestRankJsonData($contest_id);
            if (!$json) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => '暂无排名！']);
            }
            $data = $json['data'];
            $problemIndex = $json['problemIndex'];
            $myrank = 1;
            $total = $json['total'];
            foreach ($data as &$tem) {
                if (Base::getIdByUid($tem['id']) == $my_aid) {
                    $myrank = $tem['index'];
                    break;
                }
            }
            $data = Base::paging($page, $limit, $data);
            Base::dataToSafe($data, true);
            Base::dataToSafe($problemIndex);
            return json(['code' => 1, 'data' => $data, 'problemIndex' => $problemIndex, 'myrank' => $myrank, 'total' => $total]);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【lookAcmExcelRank】</strong>运行错误：' . $e->getMessage());
        }
        return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => '服务器异常！无法查看排名！']);
    }

    /**
     * OI，IOI赛制表格排名
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookOiExcelRank(Request $request)
    {
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
            $contest_uid = $request->post('contest_id');
            $page = $request->post('page');
            $limit = $request->post('limit');
            $contest_id = Base::getIdByUid($contest_uid);
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return json(['code' => -1, 'msg' => '竞赛不存在！', 'data' => [], 'problemIndex' => []]);
            }
            if ($contest_db->type != 'OI' && $contest_db->type != 'IOI') {
                return json(['code' => -1, 'msg' => '竞赛类型与预期不匹配！', 'data' => [], 'problemIndex' => []]);
            }
            //判断竞赛是否开始或结束
            $begintime = strtotime($contest_db->begin);
            $endtime = strtotime($contest_db->end);
            $isbegin = false;
            if (time() >= $begintime) {
                $isbegin = true;
            }
            if (!$isbegin) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => 'OI赛制竞赛结束可查看排名！']);
            }
            $is_mycontest = Contest::judgeIsMyContest($contest_id, $my_aid);
            // OI赛制未结束，非管理员不可看
            if ($contest_db->type == 'OI' && time() <= $endtime && !$is_mycontest) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => 'OI赛制竞赛结束可查看排名！']);
            }
            $json = Base::getContestRankJsonData($contest_id);
            if (!$json) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => '暂无排名！']);
            }
            $data = $json['data'];
            $problemIndex = $json['problemIndex'];
            $myrank = 1;
            $total = $json['total'];
            foreach ($data as &$tem) {
                if (Base::getIdByUid($tem['id']) == $my_aid) {
                    $myrank = $tem['index'];
                    break;
                }
            }
            $data = Base::paging($page, $limit, $data);
            Base::dataToSafe($data, true);
            Base::dataToSafe($problemIndex);
            return json(['code' => 1, 'data' => $data, 'problemIndex' => $problemIndex, 'myrank' => $myrank, 'total' => $total]);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【lookOiExcelRank】</strong>运行错误：' . $e->getMessage());
        }
        return json(['code' => 1, 'data' => [], 'problemIndex' => [], 'msg' => '服务器异常！无法查看排名！']);
    }

    /**
     * 删除竞赛缓存
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteRank(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $ismy = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$ismy) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $redis4 = Redis::connection('db4');
        // 解锁
        $lockonerank = 'contestranklock' . $contest_id;
        $redis4->del($lockonerank);
        // 解锁
        $lockecharts = 'contestranklockecharts' . $contest_id;
        $redis4->del($lockecharts);
        $user = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->pluck('userid');
        foreach ($user as &$tem) {
            $redis4->del('Contest' . $contest_id . 'problemdata' . $tem);
        }
        Base::updateContestDataRedis($contest_id);
        // 删除机器人完成竞赛缓存
        $redis27 = Redis::connection('db27');
        $key = Base::$robot_contest_redis_front . $contest_id;
        $redis27->del($key);
        // 删除查重缓存锁
        $redis32 = Redis::connection('db32');
        $redis32->del($contest_id);
        // 删除ContestRank代码缓存
        $redis29 = Redis::connection('db29');
        $redis30 = Redis::connection('db30');
        $old_id_list = $redis30->get(Base::$redis_contest_code_list_key_name . $contest_id);
        if ($old_id_list) {
            try {
                $old_id_list = json_decode($old_id_list, true);
            } catch (Exception $e) {
                $old_id_list = [];
            }
            foreach ($old_id_list as &$tem_one_old) {
                $redis29->del($tem_one_old[0]);
            }
        }
        $redis30->del(Base::$redis_contest_code_list_key_name . $contest_id);
        $redis32 = Redis::connection('db32');
        $redis32->del($contest_id);
        Contest::sendUpdateRankMQ($contest_id);
        return json(['code' => 1, 'msg' => '该竞赛缓存清理完成！']);
    }

    /**
     * 竞赛查看代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$user_id) {
            $user_id = $my_aid;
        }
        $contest = Db::table('contestproblem')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->select('contestid')
            ->get();
        if (!$contest) {
            return json(['code' => 1, 'data' => '竞赛不存在，请刷新页面！', 'language' => 'C++']);
        }
        $iscontest = false;

        $db = Base::getContestData($contest_id);
        if ($db) {
            if (strtotime($db->end) >= time()) {
                $iscontest = true;
            }
        }
        // 竞赛赛题且竞赛未结束，普通用户无法看代码
        $is_my_contest = Contest::judgeIsMyContest($contest_id, $my_aid);
        if ($iscontest && !$is_my_contest && $my_aid != $user_id) {
            return json(['code' => 1, 'data' => '由于该题目正处于竞赛中或者题目为私密状态所以您没有权限查看该代码！', 'language' => 'C++']);
        }
        if (!Base::judgeIsRoot($my_aid) && Base::judgeIsRobot($user_id) && !$is_my_contest && $my_aid != $user_id) {
            return json(['code' => 1, 'data' => '您没有权限查看该代码！', 'language' => 'C++']);
        }
        $type = $db->type;
        $code = '';
        $language = 'C++';

        if ($type == 'ACM' || $type == 'SQS') {
            $codedb = Db::table('contestrank')
                ->where('contestid', $contest_id)
                ->where('problemid', $problem_id)
                ->where('userid', $user_id)
                ->where('score', 100)
                ->where('isdel', 0)
                ->select('code', 'language')
                ->orderBy('id', 'desc')
                ->first();
            if ($codedb) {
                $code = $codedb->code;
                $language = $codedb->language;
            }
            if ($code != null && $code != '') {
                return json(['code' => 1, 'data' => $code, 'language' => $language]);
            }
            return json(['code' => 1, 'data' => '该用户未在该竞赛中通过本题！', 'language' => 'C++']);
        } else {
            if ($type == 'OI') {
                $codedb = Db::table('contestrank')
                    ->where('contestid', $contest_id)
                    ->where('problemid', $problem_id)
                    ->where('userid', $user_id)
                    ->where('isdel', 0)
                    ->select('code', 'language')
                    ->orderBy('id', 'desc')
                    ->first();
                if ($codedb) {
                    $code = $codedb->code;
                    $language = $codedb->language;
                }
                if ($code != null && $code != '') {
                    return json(['code' => 1, 'data' => $code, 'language' => $language]);
                }
                return json(['code' => 1, 'data' => '该用户未在该竞赛中提交本题！', 'language' => 'C++']);
            } else {
                $codedb = Db::table('contestrank')
                    ->where('contestid', $contest_id)
                    ->where('problemid', $problem_id)
                    ->where('userid', $user_id)
                    ->where('isdel', 0)
                    ->select('code', 'language')
                    ->orderBy('score', 'desc')
                    ->first();
                if ($codedb) {
                    $code = $codedb->code;
                    $language = $codedb->language;
                }
                if ($code != null && $code != '') {
                    return json(['code' => 1, 'data' => $code, 'language' => $language]);
                }
            }
        }
        return json(['code' => 1, 'data' => '该用户未在该竞赛中提交本题！', 'language' => 'C++']);
    }

    /**
     * 代码分词器
     */
    private function splitWords($str)
    {
        $res = [];
        $len = strlen($str);
        $words = '';
        for ($i = 0; $i < $len; ++$i) {
            $is_symbol = false;
            foreach (Contest::$symbol as &$t) {
                if ($str[$i] == $t) {
                    $is_symbol = true;
                    break;
                }
            }
            if ($is_symbol || $i == $len - 1) {
                $words && $res[] = $words;
                $words = '';
            } else {
                $words .= $str[$i];
            }
        }
        return $res;
    }

    /**
     * 查看竞赛用户代码
     */
    public function lookContestProblemCode(Request $request)
    {
        try {
            $key = $request->get('path');
            if (!$key) {
                return Base::notFoundPage();
            }
            $redis29 = Redis::connection('db29');
            if (!$redis29->exists($key)) {
                return Base::notFoundPage();
            }
            // 查询ID
            $id = $redis29->get($key);
            $contestrank_db = Base::getContestRankData($id);
            if ($contestrank_db) {
                $contestrank_db->language = Base::$map_language_to_markdown[$contestrank_db->language ?? 'C++'];
                return Base::codeToHTML($contestrank_db->code ?? '', $contestrank_db->language);
            }
        } catch (Exception $e) {
        }
        return Base::notFoundPage();
    }

    /**
     * 代码查重计算
     * @param string $code1
     * @param string $code2
     */
    private function codeDuplicationCheck($code1, $code2)
    {
        if (!$code1 || !$code2) {
            return 0;
        }
        $words1 = $this->splitWords($code1);
        $words2 = $this->splitWords($code2);
        $map = array_unique(array_merge($words1, $words2));
        $map1 = array_fill_keys($map, 0);
        $map2 = array_fill_keys($map, 0);
        foreach ($words1 as $key) {
            $map1[$key]++;
        }
        foreach ($words2 as $key) {
            $map2[$key]++;
        }
        $dot_product = 0;
        $value_product = 0;
        $value1_sum = 0;
        $value2_sum = 0;
        foreach ($map as $key) {
            $dot_product += $map1[$key] * $map2[$key];
            $value1_sum += pow($map1[$key], 2);
            $value2_sum += pow($map2[$key], 2);
        }
        $value_product = sqrt($value1_sum) * sqrt($value2_sum);
        if (!$value_product) {
            return 0;
        }
        return number_format($dot_product / $value_product, 2);
    }

    /**
     * 一场竞赛代码查重
     */
    public function codeCheckSimilarity(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $is_my = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$is_my) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '竞赛不存在']);
        }
        $now = date('Y-m-d H:i:s', time());
        if ($now < $contest_db->begin) {
            return json(['code' => -1, 'msg' => '竞赛未开始']);
        }
        $redis32 = Redis::connection('db32');
        if ($redis32->exists($contest_id) || !$redis32->setNx($contest_id, 1)) {
            return json(['code' => -1, 'msg' => '正在查重！请耐心等待！']);
        }
        $type = $contest_db->type;
        $user_list = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->distinct()
            ->pluck('userid')
            ->toArray();
        $problem_list = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->pluck('problemid');
        $problem_name = [];
        foreach ($problem_list as &$t) {
            $db = Db::table('oj')
                ->where('id', $t)
                ->where('isdel', 0)
                ->select('problemName')
                ->first();
            if (!$db) {
                $problem_name[$t] = '未知题目';
            } else {
                $problem_name[$t] = $db->problemName;
            }
        }
        $res = '';
        $map = [];
        foreach ($problem_list as &$t_problem) {
            foreach ($user_list as &$tem) {
                if (!isset($map[$tem])) {
                    $map[$tem] = [];
                }
                switch ($type) {
                    case 'ACM':
                        $map[$tem][$t_problem] = $this->getAcmIoiSqsOneUserContestCode($contest_db, $t_problem, $tem);
                        break;
                    case 'OI':
                        $map[$tem][$t_problem] = $this->getOiOneUserContestCode($contest_db, $t_problem, $tem);
                        break;
                    case 'IOI':
                        $map[$tem][$t_problem] = $this->getAcmIoiSqsOneUserContestCode($contest_db, $t_problem, $tem);
                        break;
                    case 'SQS':
                        $map[$tem][$t_problem] = $this->getAcmIoiSqsOneUserContestCode($contest_db, $t_problem, $tem);
                        break;
                    default:
                        break;
                }
            }
        }
        $len = sizeof($user_list);

        $percentage_list = [];
        Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');

        // 删除查重缓存锁
        $redis32 = Redis::connection('db32');
        $redis32->del($contest_id);
        // 删除旧的缓存
        $redis29 = Redis::connection('db29');
        $redis30 = Redis::connection('db30');
        $old_id_list = $redis30->get(Base::$redis_contest_code_list_key_name . $contest_id);
        if ($old_id_list) {
            try {
                $old_id_list = json_decode($old_id_list, true);
            } catch (Exception $e) {
                $old_id_list = [];
            }
            foreach ($old_id_list as &$tem_one_old) {
                $redis29->del($tem_one_old[0]);
            }
        }
        $redis30->del(Base::$redis_contest_code_list_key_name . $contest_id);
        // 索引数组
        $contestrank_code_safe_id_list = [];

        foreach ($problem_list as &$t) {
            for ($i = 0; $i < $len; ++$i) {
                $code1 = $map[$user_list[$i]][$t];
                if (!$code1) {
                    $code1 = new stdClass();
                    $code1->id = '';
                    $code1->code = '';
                }
                for ($j = $i + 1; $j < $len; ++$j) {
                    $code2 = $map[$user_list[$j]][$t];
                    if (!$code2) {
                        $code2 = new stdClass();
                        $code2->id = '';
                        $code2->code = '';
                    }
                    $duplication = $this->codeDuplicationCheck($code1->code, $code2->code);
                    if (!($duplication * 100)) {
                        continue;
                    }
                    $code1_safe_id = md5($code1->id);
                    $code2_safe_id = md5($code2->id);
                    // 缓存
                    $redis29->setNx($code1_safe_id, $code1->id);
                    $redis29->setNx($code2_safe_id, $code2->id);
                    // 存入索引数组
                    $contestrank_code_safe_id_list[] = [$code1_safe_id, $code1->id];
                    $contestrank_code_safe_id_list[] = [$code2_safe_id, $code2->id];
                    // 地址
                    $user_code_url_1 = Base::$GLOBlinuxurl . '/Contest/lookContestProblemCode?path=' . $code1_safe_id;
                    $user_code_url_2 = Base::$GLOBlinuxurl . '/Contest/lookContestProblemCode?path=' . $code2_safe_id;
                    $user_i_db = Base::getUserDataFromDb($user_list[$i]);
                    $user_j_db = Base::getUserDataFromDb($user_list[$j]);
                    if (!$user_i_db) {
                        $user_i_db = new stdClass();
                        $user_i_db->name = '未知用户';
                    }
                    if (!$user_j_db) {
                        $user_j_db = new stdClass();
                        $user_j_db->name = '未知用户';
                    }
                    $msg =
                        '<tr><td>题目：【<span class="title">' . $problem_name[$t] . '</span>】</td><td>用户：【<a class="user" href="' .
                        $user_code_url_1 . '" target="_blank">' .
                        $user_i_db->name . '</a>】和用户：【<a class="user" href="' .
                        $user_code_url_2 . '" target="_blank">' .
                        $user_j_db->name . '</a>】</td><td>代码相似度达到：<span class="duplication">' .
                        $duplication * 100 . '%</span></td></div></tr>';
                    $loc = number_format(floor($duplication * 10) / 10, 1);
                    if (isset($percentage_list[$loc])) {
                        $percentage_list[$loc] .= $msg;
                    } else {
                        $percentage_list[$loc] = $msg;
                    }
                }
            }
        }

        $redis30->setNx(Base::$redis_contest_code_list_key_name . $contest_id, json_encode($contestrank_code_safe_id_list));

        $similarity_css = Base::getCss('table');
        $res = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="icon" href="https://ltpp.vip/LTPPlogo.png" type="image/x-icon"><title>LTPP ' . $contest_db->name . ' 代码查重</title><style>' . $similarity_css . '</style></head><body>';
        krsort($percentage_list);
        foreach ($percentage_list as $key => $value) {
            if (!($key * 100)) {
                break;
            }
            $res .= '<table><tr><th class="tips" colspan="3">代码相似度达到【 ' . $key * 100 . '% 】</th></tr>' . $value . '</table><br/><br/><br/><br/><br/><br/>';
        }
        $res .= '</body></html>';
        $url = Base::writeNewStaticFile($my_aid, $res, 'html');
        $redis32->del($contest_id);
        return json(['code' => 1, 'url' => $url, 'msg' => '查重完成']);
    }

    /**
     * ACM,IOI,SQS赛制获取代码记录
     * @param stdClass $contest_db 镜像信息
     * @param int $problem_id 问题ID
     * @param int $user_id 用户ID
     */
    private function getAcmIoiSqsOneUserContestCode($contest_db, $problem_id, $user_id)
    {
        $code = Db::table('contestrank')
            ->where('contestid', $contest_db->id)
            ->where('problemid', $problem_id)
            ->where('userid', $user_id)
            ->where('submittime', '>=', $contest_db->begin)
            ->where('submittime', '<=', $contest_db->end)
            ->where('isdel', 0)
            ->select('id', 'code')
            ->orderBy('score', 'desc')
            ->first();
        if (!$code) {
            $code = new stdClass();
            $code->id = 0;
            $code->code = '';
        }
        return $code;
    }

    /**
     * OI赛制获取代码记录
     * @param stdClass $contest_db 镜像信息
     * @param int $problem_id 问题ID
     * @param int $user_id 用户ID
     */
    private function getOiOneUserContestCode($contest_db, $problem_id, $user_id)
    {
        $code = Db::table('contestrank')
            ->where('contestid', $contest_db->id)
            ->where('problemid', $problem_id)
            ->where('userid', $user_id)
            ->where('submittime', '>=', $contest_db->begin)
            ->where('submittime', '<=', $contest_db->end)
            ->where('isdel', 0)
            ->select('id', 'code')
            ->orderBy('id', 'desc')
            ->first();
        if (!$code) {
            $code = new stdClass();
            $code->id = 0;
            $code->code = '';
        }
        return $code->id;
    }

    /**
     * 生成 HTML 排名
     */
    public function publicContestRank(Request $request)
    {
        $contest_uid = $request->get('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $html = Base::getContestRankHtml($contest_id);
        if (!$html) {
            $js = Base::getJs('rank');
            $msg_css = Base::getCss('msg');
            $contest_db = Base::getContestData($contest_id);
            // 非消息队列获取排名，排名不在缓存
            return '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="icon" href="https://ltpp.vip/LTPPlogo.png" type="image/x-icon"><title>LTPP【' . $contest_db->name . '】竞赛排名</title><style>' . $msg_css . '</style><script>' . $js . '</script></head><body><h1>竞赛排名计算中</h1></body></html>';
        }
        return $html;
    }

    /**
     * 获取排名
     * @param int $contest_id 竞赛id
     */
    static public function contestIdGetRank($contest_id)
    {
        try {
            $my_aid = Base::getRootId();
            $contest_db = Base::getContestData($contest_id);
            $redis4 = Redis::connection('db4');
            if (!$contest_db) {
                return;
            }
            if ($contest_db->type == 'ACM' || $contest_db->type == 'SQS') {
                Contest::lookHtmlAcmExcelRank($my_aid, $contest_id, $contest_db, $redis4);
            } else if ($contest_db->type == 'OI' || $contest_db->type == 'IOI') {
                Contest::lookHtmlOiExcelRank($my_aid, $contest_id, $contest_db, $redis4);
            }
        } catch (Exception $e) {
            $title = 'contestIdGetRank运行异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<h4>' . $title . "</h4>\n" . $content);
        }
    }

    /**
     * HTML排名计算
     * @param {*} $problems 问题信息列表
     * @param {*} $data 排名数据
     * @param {*} $contest_db 数据库信息
     */
    static private function calculateContestRank(&$problems, &$data, $contest_db)
    {
        $html = '';
        try {
            if (!$problems || !$data || !$contest_db) {
                return $html;
            }
            $table_title = '';
            $table_body = '';
            $pro_index = 1;
            $problems_len = sizeof($problems);
            $rank_css = Base::getCss('table');
            $msg_css = Base::getCss('msg');
            $js = Base::getJs('rank');
            $endtime = strtotime($contest_db->end);
            // OI赛制HTML排名竞赛未结束不显示
            if ($contest_db->type == 'OI' && time() <= $endtime) {
                return '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="icon" href="https://ltpp.vip/LTPPlogo.png" type="image/x-icon"><title>LTPP【' . $contest_db->name . '】竞赛排名</title><style>' . $msg_css . '</style><script>' . $js . '</script></head><body><h1>OI赛制竞赛结束可查看排名</h1></body></html>';
            }
            if ($contest_db->type == 'ACM' || $contest_db->type == 'SQS') {
                $table_title = '<th>排名</th><th>用户</th><th>总AC数</th><th>总用时</th>';
                foreach ($problems as &$tem) {
                    $table_title .= '<th>P' . $pro_index++ . '</th>';
                }
                $table_title = '<tr>' . $table_title . '</tr>';
                foreach ($data as &$tem) {
                    $hour = floor($tem['totaltime'] / 3600);
                    $tem['totaltime'] = $hour . ':' . floor(floor($tem['totaltime'] - $hour * 3600) / 60) . ':' . floor($tem['totaltime'] % 60);
                    $table_body .= '<td class="RANK">' . $tem['index'] . '</td><td class="USER-NAME">' . $tem['name'] . '</td><td class="ACM-ACNUM">' . $tem['acnum'] . '</td><td class="TOTALTIME">' . $tem['totaltime'] . '</td>';
                    foreach ($tem['res'] as &$tem_pro) {
                        if ($tem_pro['firstAcTime'] != -1) {
                            if ($tem_pro['waNum'] == 0) {
                                $table_body .= '<td><div class="ACM-ONE-TIME-AC">✔</div><div class="FIRST-AC-TIME">( ' . $tem_pro['firstAcTime'] . ' )</div></td>';
                            } else {
                                $table_body .= '<td><div class="ACM-MORE-TIMES-AC">-' . $tem_pro['waNum'] . '</div><div class="FIRST-AC-TIME">(' . $tem_pro['firstAcTime'] . ')</div></td>';
                            }
                        } else if ($tem_pro['waNum'] != 0) {
                            $table_body .= '<td><div class="ACM-NO-AC">-' . $tem_pro['waNum'] . '</div></td>';
                        } else {
                            $table_body .= '<td></td>';
                        }
                    }
                    $table_body = '<tr>' . $table_body . '</tr>';
                }
            } else if ($contest_db->type == 'IOI' || $contest_db->type == 'OI') {
                $table_title = '<th>排名</th><th>用户</th><th>总分数</th><th>总用时</th>';
                foreach ($problems as &$tem) {
                    $table_title .= '<th>P' . $pro_index++ . '</th>';
                }
                $table_title = '<tr>' . $table_title . '</tr>';
                foreach ($data as &$tem) {
                    $hour = floor($tem['totaltime'] / 3600);
                    $tem['totaltime'] = $hour . ':' . floor(floor($tem['totaltime'] - $hour * 3600) / 60) . ':' . floor($tem['totaltime'] % 60);
                    $table_body .= '<td class="RANK">' . $tem['index'] . '</td><td class="USER-NAME">' . $tem['name'] . '</td><td class="ACM-ALLSCORE">' . $tem['allscore'] . '</td><td class="TOTALTIME">' . $tem['totaltime'] . '</td>';
                    foreach ($tem['res'] as &$tem_pro) {
                        if ($tem_pro['firstAcTime'] != -1) {
                            if ($tem_pro['score'] == 100) {
                                $table_body .= '<td><div class="IOI-AC">' . $tem_pro['score'] . '</div><div class="FIRST-AC-TIME">( ' . $tem_pro['firstAcTime'] . ' )</div></td>';
                            } else {
                                $table_body .= '<td><div class="IOI-NO-AC">' . $tem_pro['score'] . '</div><div class="FIRST-AC-TIME">( ' . $tem_pro['firstAcTime'] . ' )</div></td>';
                            }
                        } else {
                            $table_body .= '<td></td>';
                        }
                    }
                    $table_body = '<tr>' . $table_body . '</tr>';
                }
            }
            $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="icon" href="https://ltpp.vip/LTPPlogo.png" type="image/x-icon"><title>LTPP【' . $contest_db->name . '】竞赛排名</title><style>' . $rank_css . '</style><script>' . $js . '</script></head><body><table class="CONTEST"><tr><th class="CONTEST-NAME" colspan="' . ($problems_len + 4) . '">LTPP【' . $contest_db->name . '】实时竞赛排名</th></tr>' . $table_title . $table_body . '</body></html>';
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【calculateContestRank】</strong>运行错误：' . $e->getMessage());
            return $html;
        }
        return $html;
    }

    /**
     * 判断limit参数是否合法，不合法纠正
     * @param int $limit 每页多少条数据
     * @return void
     */
    static public function judgeLimitIsSafe(&$limit)
    {
        if (!$limit || !is_numeric($limit) || $limit <= 0 || $limit > Contest::$db_get_limit) {
            $limit = Contest::$db_get_limit;
        }
    }

    /**
     * HTML 竞赛ACM，SQS赛制表格排名计算
     * @param int $my_aid 用户ID
     * @param int $contest_id 竞赛ID
     * @param {*} $contest_db 竞赛数据库信息
     * @param {*} $redis4 数据库连接
     */
    static private function lookHtmlAcmExcelRank($my_aid, $contest_id, $contest_db, $redis4)
    {
        if (!$my_aid) {
            $my_aid = 0;
        }
        if (!$contest_db) {
            return;
        }
        if ($contest_db->type != 'ACM' && $contest_db->type != 'SQS') {
            return;
        }
        $begintime = strtotime($contest_db->begin);
        $endtime = strtotime($contest_db->end);

        //排名锁
        $lockone = 'contestranklock' . $contest_id;
        $now_key_value = Base::randString();
        //加锁
        $lock_res = $redis4->setNx($lockone, $now_key_value);
        if (!$lock_res) {
            return;
        }
        // 题目
        $prolist = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->select('problemid')
            ->distinct()
            ->where('isdel', 0)
            ->get();
        $problemIndex = [];
        foreach ($prolist as &$tem) {
            $problem_db = Base::getOjData($tem->problemid);
            if (!$problem_db) {
                continue;
            }
            $problemIndex[] = [
                'problemname' => $problem_db->problemName,
                'problemid' => $tem->problemid
            ];
        }
        $arr_prolist = $prolist->toArray();
        // 题目数目
        $pronum = sizeof($arr_prolist);

        //更新未ak用户时间补加总时长
        $res = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('userid')
            ->orderBy('id', 'desc')
            ->get();
        $resarray = array();

        foreach ($res as &$tem) {
            $now_lock = $redis4->get($lockone);
            if (!$now_lock || $now_lock != $now_key_value) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => []]);
            }
            $ta = [];
            $db = Base::getUserDataFromDb($tem->userid);
            if (!$db || empty($db)) {
                continue;
            }
            $userResProList = [];
            $firstactime = -1;
            $sumwa = 0;
            $ta['id'] = $db->id;
            $ta['name'] = $db->name;
            // AC的题目数目
            $acnum = 0;
            $endtime = min($endtime, max($begintime, time()));
            $acm_total_time = 0;
            //罚时加竞赛经过时间
            foreach ($problemIndex as &$tp) {
                $wa = 0;
                $addtime = Db::table('contestrank')
                    ->where('contestid', $contest_id)
                    ->where('problemid', $tp['problemid'])
                    ->where('userid', $tem->userid)
                    ->where('submittime', '>=', date('Y-m-d H:i:s', $begintime))
                    ->where('submittime', '<=', date('Y-m-d H:i:s', $endtime))
                    ->where('isdel', 0)
                    ->orderBy('id', 'asc')
                    ->get();
                $isac = false;
                foreach ($addtime as &$t) {
                    if ($t->score != 100) {
                        ++$wa;
                    } else {
                        $firstactime = $t->submittime;
                        $acm_total_time += strtotime($firstactime) - $begintime;
                        $ftime = (int) (strtotime($firstactime) - $begintime);
                        $hour = (int) ($ftime / 3600);
                        $minute = (int) (($ftime - $hour * 3600) / 60);
                        $seconds = $ftime - $hour * 3600 - $minute * 60;
                        $isac = true;
                        ++$acnum;
                        $userResProList[] = [
                            'id' => $tp['problemid'],
                            'waNum' => $wa,
                            'firstAcTime' => "$hour:$minute:$seconds",
                            'score' => ''
                        ];
                        // 遇到AC记录停止循环
                        break;
                    }
                }
                if (!$isac) {
                    $userResProList[] = [
                        'id' => $tp['problemid'],
                        'waNum' => $wa,
                        'firstAcTime' => -1,
                        'score' => ''
                    ];
                } else {
                    // AC则罚时
                    $sumwa += $wa;
                }
            }
            if ($contest_db->type == 'SQS') {
                // SQS赛制无罚时
                // 没有AK
                $ta['totaltime'] = max(0, $acm_total_time);
            } else {
                $ta['totaltime'] = max(0, $sumwa * 1200 + $acm_total_time);
            }
            $ta['res'] = $userResProList;
            $ta['acnum'] = $acnum;
            $ta['allscore'] = '';

            if (!empty($ta)) {
                $resarray[] = $ta;
            }
        }

        usort($resarray, function ($a, $b) {
            $total_acnum = (int) ((int) $b['acnum'] - (int) $a['acnum']);
            if ($total_acnum === 0) {
                return (int) $a['totaltime'] - (int) $b['totaltime'];
            }
            return $total_acnum;
        });

        $index = 0;
        $last_acnum = null;
        $last_totaltime = null;
        foreach ($resarray as &$tem) {
            if ($tem['acnum'] === $last_acnum && $tem['totaltime'] === $last_totaltime) {
                $tem['index'] = $index;
            } else {
                $tem['index'] = ++$index;
                $last_acnum = $tem['acnum'];
                $last_totaltime = $tem['totaltime'];
            }
        }
        $redis4->del($lockone);
        // 更新JSON缓存，顺序不可换，因为HTML更新传递引用，会导致数组总时间格式化
        $total = sizeof($resarray);
        $json = ['data' => $resarray, 'problemIndex' => $problemIndex, 'total' => $total];
        Base::updateContestRankJson($contest_id, $json);
        // 更新HTML缓存
        $html = Contest::calculateContestRank($problemIndex, $resarray, $contest_db);
        if ($html) {
            Base::updateContestRankHtml($contest_id, $html);
        }
    }

    /**
     * HTML OI，IOI赛制表格排名计算
     * @param int $my_aid 用户ID
     * @param int $contest_id 竞赛ID
     * @param {*} $contest_db 竞赛数据库信息
     * @param {*} $redis4 数据库连接
     */
    static private function lookHtmlOiExcelRank($my_aid, $contest_id, $contest_db, $redis4)
    {
        if (!$my_aid) {
            $my_aid = 0;
        }
        if (!$contest_db) {
            return;
        }
        if ($contest_db->type != 'OI' && $contest_db->type != 'IOI') {
            return;
        }
        $begintime = strtotime($contest_db->begin);
        $endtime = strtotime($contest_db->end);

        $lockone = 'contestranklock' . $contest_id;
        $now_key_value = Base::randString();
        //加锁
        $lock_res = $redis4->setNx($lockone, $now_key_value);
        if (!$lock_res) {
            // 未抢到锁
            return;
        }
        // 题目
        $prolist = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('problemid')
            ->get();
        $problemIndex = [];
        foreach ($prolist as &$tem) {
            $problem_db = Base::getOjData($tem->problemid);
            if (!$problem_db) {
                continue;
            }
            $problemIndex[] = [
                'problemname' => $problem_db->problemName,
                'problemid' => $tem->problemid
            ];
        }

        //更新未ak用户时间补加总时长
        $res = Db::table('joincontest')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->select('userid')
            ->orderBy('id', 'desc')
            ->get();
        $resarray = array();

        foreach ($res as &$tem) {
            $now_lock = $redis4->get($lockone);
            if (!$now_lock || $now_lock != $now_key_value) {
                return json(['code' => 1, 'data' => [], 'problemIndex' => []]);
            }
            $ta = [];
            $db = Base::getUserDataFromDb($tem->userid);
            if (!$db || empty($db)) {
                continue;
            }
            $userResProList = [];
            $allscore = 0;
            $ta['id'] = $db->id;
            $ta['name'] = $db->name;
            $endtime = min($endtime, max($begintime, time()));
            $ioi_total_time = 0;
            foreach ($problemIndex as &$tp) {
                $addtime = null;
                if ($contest_db->type == "OI") {
                    // 以最新的提交为准
                    $addtime = Db::table('contestrank')
                        ->where('contestid', $contest_id)
                        ->where('problemid', $tp['problemid'])
                        ->where('userid', $tem->userid)
                        ->where('submittime', '>=', date('Y-m-d H:i:s', $begintime))
                        ->where('submittime', '<=', date('Y-m-d H:i:s', $endtime))
                        ->where('isdel', 0)
                        ->select('submittime', 'score')
                        ->orderBy('id', 'desc')
                        ->first();
                } else {
                    // 取最高的分数，考虑时间需要从前开始枚举
                    $addtime = Db::table('contestrank')
                        ->where('contestid', $contest_id)
                        ->where('problemid', $tp['problemid'])
                        ->where('userid', $tem->userid)
                        ->where('submittime', '>=', date('Y-m-d H:i:s', $begintime))
                        ->where('submittime', '<=', date('Y-m-d H:i:s', $endtime))
                        ->where('isdel', 0)
                        ->select('submittime', 'score')
                        ->orderBy('score', 'desc')
                        ->orderBy('id', 'asc')
                        ->first();
                }
                if ($addtime) {
                    $firstactime = (int) strtotime($addtime->submittime);
                    $one_pro_score = $addtime->score;
                    $ftime = max(0, (int) ($firstactime - $begintime));
                    $ioi_total_time += max(0, $ftime);
                    $allscore += $one_pro_score;
                    if ($ftime == 0) {
                        $userResProList[] = [
                            'id' => $tp['problemid'],
                            'waNum' => '',
                            'firstAcTime' => -1,
                            'score' => 0
                        ];
                    } else {
                        $hour = (int) ($ftime / 3600);
                        $minute = (int) (($ftime - $hour * 3600) / 60);
                        $seconds = $ftime - $hour * 3600 - $minute * 60;
                        $userResProList[] = [
                            'id' => $tp['problemid'],
                            'waNum' => '',
                            'firstAcTime' => "$hour:$minute:$seconds",
                            'score' => $one_pro_score
                        ];
                    }
                } else {
                    $userResProList[] = [
                        'id' => $tp['problemid'],
                        'waNum' => '',
                        'firstAcTime' => -1,
                        'score' => 0
                    ];
                }
            }
            // OI赛制时间不进行计算
            if ($contest_db->type == 'OI') {
                $ta['totaltime'] = max(0, $endtime - $begintime);
            } else {
                $ta['totaltime'] = max(0, $ioi_total_time);
            }
            $ta['res'] = $userResProList;
            $ta['acnum'] = '';
            $ta['allscore'] = $allscore;
            if (!empty($ta)) {
                $resarray[] = $ta;
            }
        }
        usort($resarray, function ($a, $b) {
            $total_score = (int) ((int) $b['allscore'] - (int) $a['allscore']);
            if ($total_score === 0) {
                return (int) $a['totaltime'] - (int) $b['totaltime'];
            }
            return $total_score;
        });

        $index = 0;
        $last_allscore = null;
        $last_totaltime = null;
        foreach ($resarray as &$tem) {
            if ($tem['allscore'] === $last_allscore && $tem['totaltime'] === $last_totaltime) {
                $tem['index'] = $index;
            } else {
                $tem['index'] = ++$index;
                $last_allscore = $tem['allscore'];
                $last_totaltime = $tem['totaltime'];
            }
        }
        $redis4->del($lockone);
        // 更新JSON缓存，顺序不可换，因为HTML更新传递引用，会导致数组总时间格式化
        $total = sizeof($resarray);
        $json = ['data' => $resarray, 'problemIndex' => $problemIndex, 'total' => $total];
        Base::updateContestRankJson($contest_id, $json);
        // 更新HTML缓存
        $html = Contest::calculateContestRank($problemIndex, $resarray, $contest_db);
        if ($html) {
            Base::updateContestRankHtml($contest_id, $html);
        }
    }

    /**
     * 获取某竞赛所有题目汇总的HTML文件
     */
    public function getProblemMD(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $is_my = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$is_my) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '竞赛不存在！']);
        }
        $problem_list = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->pluck('problemid')
            ->toArray();
        $res_md = '<h1>【' . $contest_db->name . "】赛题</h1>\n\n<br>\n\n" . $contest_db->content . "\n\n<br><hr><br>\n\n";
        $res_md .= '<h2>出题人</h2>' . "\n\n<br>\n\n> " . ($contest_db->creater ?? '无') . "\n\n<br><hr><br>\n\n";
        foreach ($problem_list as &$problem_id) {
            $problem_db = Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->first();
            if (!$problem_db) {
                continue;
            }
            // 题目
            $res_md .= '<h3>' . $problem_db->problemName . "</h3>\n\n<br>\n\n";
            // 内容
            $res_md .= $problem_db->problemContent . "\n\n<br>\n\n";
            // 输入样例
            $res_md .= '<h5>输入样例</h5>' . "\n\n > " . $problem_db->problemCinTest . "\n\n<br>\n\n";
            // 输出样例
            $res_md .= '<h5>输出样例</h5>' . "\n\n > " . $problem_db->problemCoutTest . "\n\n<br>\n\n";
            // 时间限制
            $res_md .= '<h5>时间限制（单位：MS）</h5>' . "\n\n > " . $problem_db->Time . "\n\n<br>\n\n";
            // 内存限制
            $res_md .= '<h5>内存限制（单位：MB）</h5>' . "\n\n > " . $problem_db->Memory . "\n\n<br>\n\n";
            // 题目来源
            $res_md .= '<h5>题目来源</h5>' . "\n\n > " . $problem_db->problemFrom . "\n\n<br><hr><br>\n\n";
        }
        return $res_md;
    }

    /**
     * 获取某竞赛所有题目题解汇总的HTML文件
     */
    public function getProblemSolveMD(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $contest_uid = $request->post('contest_id');
        $contest_id = Base::getIdByUid($contest_uid);
        $contest_db = Base::getContestData($contest_id);
        if (!$contest_db) {
            return json(['code' => -1, 'msg' => '竞赛不存在！']);
        }
        $is_my = Contest::judgeIsMyContest($contest_id, $my_aid);
        if (!$is_my) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $problem_list = Db::table('contestproblem')
            ->where('contestid', $contest_id)
            ->where('isdel', 0)
            ->pluck('problemid')
            ->toArray();
        $res_md = '<h1>【' . $contest_db->name . "】题解</h1>\n\n<br>\n\n" . $contest_db->content . "\n\n<br><hr><br>\n\n";
        $res_md .= '<h2>出题人</h2>' . "\n\n<br>\n\n> " . ($contest_db->creater ?? '无') . "\n\n<br><hr><br>\n\n";
        $res_md .= '<h2>题解编写人</h2>' . "\n\n<br>\n\n> " . ($contest_db->creater ?? '无') . "\n\n<br><hr><br>\n\n";
        foreach ($problem_list as &$problem_id) {
            $problem_db = Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->first();
            if (!$problem_db) {
                continue;
            }
            $code = '无';
            $language = 'cpp';
            $code_db = null;
            if ($problem_db->code && $problem_db->code != Base::$oj_ac_code_default) {
                $code = $problem_db->code;
                $language = 'cpp';
            } else {
                $code_db = Db::table('solveproblem')
                    ->where('problemid', $problem_id)
                    ->where('isdel', 0)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($code_db) {
                    $code = $code_db->code;
                    switch ($code_db->language) {
                        case 'C++': {
                                $language = 'cpp';
                                break;
                            }
                        case 'C': {
                                $language = 'c';
                                break;
                            }
                        case 'Java': {
                                $language = 'java';
                                break;
                            }
                        case 'Python3': {
                                $language = 'python';
                                break;
                            }
                        case 'JavaScript': {
                                $language = 'js';
                                break;
                            }
                        case 'TypeScript': {
                                $language = 'ts';
                                break;
                            }
                        case 'Go': {
                                $language = 'go';
                                break;
                            }
                        case 'C#': {
                                $language = 'csharp';
                                break;
                            }
                        case 'Ruby': {
                                $language = 'ruby';
                                break;
                            }
                        case 'Rust': {
                                $language = 'rust';
                                break;
                            }
                        case 'PHP': {
                                $language = 'php';
                                break;
                            }
                        default: {
                                $language = 'cpp';
                                break;
                            }
                    }
                }
            }
            // 题目
            $res_md .= '<h2>' . $problem_db->problemName . "</h2>\n\n<br>\n\n";
            // 内容
            $res_md .= $problem_db->problemContent . "\n\n<br>\n\n";
            // 输入样例
            $res_md .= '<h5>输入样例</h5>' . "\n\n > " . $problem_db->problemCinTest . "\n\n<br>\n\n";
            // 输出样例
            $res_md .= '<h5>输出样例</h5>' . "\n\n > " . $problem_db->problemCoutTest . "\n\n<br>\n\n";
            // 时间限制
            $res_md .= '<h5>时间限制（单位：MS）</h5>' . "\n\n > " . $problem_db->Time . "\n\n<br>\n\n";
            // 内存限制
            $res_md .= '<h5>内存限制（单位：MB）</h5>' . "\n\n > " . $problem_db->Memory . "\n\n<br>\n\n";
            // 题目来源
            $res_md .= '<h5>题目来源</h5>' . "\n\n > " . $problem_db->problemFrom . "\n\n<br>\n\n";
            // 解题思路
            if (!$problem_db->think) {
                $problem_db->think = '无';
            }
            $res_md .= '<h5>解题思路</h5>' . "\n\n> " . $problem_db->think . "\n\n<br>\n\n";
            // AC代码
            if (!$code) {
                $code = '无';
            }
            $res_md .= '<h5>AC代码</h5>' . "\n\n```$language\n" . $code . "\n```\n\n<br><hr><br>\n\n";
        }
        return $res_md;
    }
}
