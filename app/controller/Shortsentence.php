<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

class shortsentence
{
    /**
     * @var array $shortsentence_db_key 短句数据库展示的字段
     */
    static $shortsentence_db_key = [
        'id',
        'hitokoto',
        'from'
    ];

    /**
     * 添加短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function addShortSentence(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');

        $data = [
            'hitokoto' => $tabledata['hitokoto'],
            'from' => $tabledata['from'],
        ];
        if ($tabledata['hitokoto'] == '') {
            return json(['code' => -1, 'msg' => '短句不能为空']);
        }
        $info = Base::insertToDb('shortsentence', $data);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'msg' => '短句添加成功']);
        }
        return json(['code' => -1, 'data' => $info, 'msg' => '短句添加失败']);
    }

    /**
     * 删除一条短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteShortSentence(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        $delete_uid = $request->post('delete_id');
        $delete_id = Base::getIdByUid($delete_uid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $info = Db::table('shortsentence')
            ->where('id', $delete_id)
            ->update(['isdel' => 1]);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'msg' => '短句删除成功']);
        }
        return json(['code' => -1, 'data' => $info, 'msg' => '短句删除失败']);
    }

    /**
     * 更新一条短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateShortSentence(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');
        $tabledata['id'] = Base::getIdByUid($tabledata['id']);
        $info = Db::table('shortsentence')
            ->where('id', $tabledata['id'])
            ->where('isdel', 0)
            ->update([
                'hitokoto' => $tabledata['hitokoto'],
                'from' => $tabledata['from']
            ]);
        return json(['code' => 1, 'data' => $info, 'msg' => '短句更新成功']);
    }

    /**
     * 查找短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function findShortSentenceList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('shortsentence')
            ->where('hitokoto', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(Shortsentence::$shortsentence_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('shortsentence')
            ->where('hitokoto', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '查找到 ' . $allnum . ' 条短句']);
    }

    /**
     * 首页加载短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadShortSentence(Request $request)
    {
        $page = $request->post('page');
        $limit = 1;
        $info = Db::table('shortsentence')
            ->where('isdel', 0)
            ->select(Shortsentence::$shortsentence_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('shortsentence')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '获取到 ' . $allnum . ' 条短句']);
    }

    /**
     * root管理员后台查看全部短句
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadAllShortSentence(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('shortsentence')
            ->where('isdel', 0)
            ->select(Shortsentence::$shortsentence_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('shortsentence')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '获取到 ' . $allnum . ' 条短句']);
    }

    /**
     * 刷新换一批
     * @param Request $request 请求
     * @return string $res json
     */
    public function refreshShortSentence(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = 1;
        $redisuser = 'ShortSentence' . $my_aid;

        $redis3 = Redis::connection('db3');
        if ($redis3->get($redisuser)) {
            if ($redis3->get($redisuser) > 5) {
                $allnum = array();
                $allnum[0] = [
                    'hitokoto' => '请求频率过快，请稍后尝试',
                    'from' => 'LTPP'
                ];
                return json(['code' => 1, 'data' => $allnum, 'allnum' => 0, 'msg' => '请求过快，获取到 0 条短句']);
            } else {
                $redis3->incr($redisuser);
            }
        } else {
            $redis3->setEx($redisuser, 60, 1);
        }
        $info = Db::table('shortsentence')
            ->where('isdel', 0)
            ->select(Shortsentence::$shortsentence_db_key)
            ->inRandomOrder()
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('shortsentence')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '获取到 ' . $allnum . ' 条短句']);
    }
}
