<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Notice
{
    /**
     * @var array $notice_db_key 公告数据库展示用户字段
     */
    static $notice_db_key = [
        'id',
        'content',
        'time'
    ];

    /**
     * 添加公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function addOneNotice(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');
        $data = [
            'content' => $tabledata['content'],
            'time' => date('Y-m-d h:i:s', time()),
        ];
        if ($tabledata['content'] == '') {
            return json(['code' => -1, 'msg' => '公告不能为空']);
        }
        $res = Base::insertToDb('notice', $data);
        if ($res) {
            return json(['code' => 1, 'data' => [], 'msg' => '公告添加成功']);
        }
        return json(['code' => -1, 'data' => [], 'msg' => '公告添加失败']);
    }

    /**
     * 删除某条公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteOneNotice(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $delete_uid = $request->post('delete_id');
        $delete_id = Base::getIdByUid($delete_uid);
        if (!$delete_id) {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }

        Db::table('notice')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);

        return json(['code' => 1, 'msg' => '公告删除成功']);
    }

    /**
     * 更新某条公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateOneNotice(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');
        $tabledata['id'] = Base::getIdByUid($tabledata['id']);
        Db::table('notice')
            ->where('id', $tabledata['id'])
            ->where('isdel', 0)
            ->update([
                'content' => $tabledata['content']
            ]);
        return json(['code' => 1, 'msg' => '公告更新成功']);
    }

    /**
     * 后台搜索公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function backFindNotice(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('notice')
            ->where('content', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(Notice::$notice_db_key)
            ->orderBy('id', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('notice')
            ->where('content', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        foreach ($info as &$tem) {
            $tem->content = Base::utfsubstr(strip_tags($tem->content), 0, Base::$back_one_notice_max_length);
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '查找到 ' . $allnum . ' 条公告']);
    }

    /**
     * 首页加载公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadNotice(Request $request)
    {
        $page = $request->post('page');

        $info = Db::table('notice')
            ->where('isdel', 0)
            ->select(Notice::$notice_db_key)
            ->orderBy('id', 'asc')
            ->paginate(1, '*', 'page', $page)
            ->items();
        $allnum = Db::table('notice')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '获取到 ' . $allnum . ' 条公告']);
    }

    /**
     * 后台root加载公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function backLoadNotice(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('notice')
            ->where('isdel', 0)
            ->select(Notice::$notice_db_key)
            ->orderBy('id', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('notice')
            ->where('isdel', 0)
            ->count();
        foreach ($info as &$tem) {
            $tem->content = Base::utfsubstr(strip_tags($tem->content), 0, Base::$back_one_notice_max_length);
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '获取到 ' . $allnum . ' 条公告']);
    }

    /**
     * 后台root查看一个公告
     * @param Request $request 请求
     * @return string $res json
     */
    public function backlookOneNotice(Request $request)
    {
        $notice_uid = $request->post('notice_id');
        $notice_id = Base::getIdByUid($notice_uid);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $data = Db::table('notice')
            ->where('id', $notice_id)
            ->where('isdel', 0)
            ->select(Notice::$notice_db_key)
            ->first();
        if ($data) {
            return json(['code' => 1, 'data' => $data->content, 'msg' => '加载完成']);
        }
        Base::dataToSafe($data);
        return json(['code' => -1, 'data' => $data, 'msg' => '公告不存在']);
    }
}
