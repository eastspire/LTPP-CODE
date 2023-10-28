<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class ArticleComment
{
    /**
     * 评论数据库展示给用户的字段
     * @var array $comment_db_key 评论数据库展示给用户的字段
     */
    static $comment_db_key = [
        'id',
        'articleid',
        'userid',
        'maincommentid',
        'touserid',
        'username',
        'tousername',
        'text',
        'time'
    ];


    /**
     * 主评论最大加载数目
     */
    static $comment_list_limit = 10;

    /**
     * 主评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function addComment(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $text = $request->post('text');
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $tem = preg_replace('/ /', '', $text);
        if ($tem == '') {
            return \json(['code' => 0, 'msg' => '评论内容不能为空']);
        }
        $user_db = Base::getUserData($my_aid);
        if (!$user_db || empty($user_db)) {
            return \json(['code' => 0, 'msg' => '用户不存在']);
        }

        $data = [
            'articleid' => $article_id,
            'userid' => $my_aid,
            'text' => $text,
            'time' => date('Y-m-d h:i:s', time()),
            'username' => $user_db->name,
            'touserid' => 0,
            'tousername' => '',
            'maincommentid' => 0
        ];
        $article_db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->select('name', 'writerid')
            ->first();
        if (!$article_db) {
            return \json(['code' => -1, 'msg' => '文章不存在，评论发表失败', 'data' => []]);
        }

        $res_id = Base::insertToDb('articlecomment', $data);

        Db::table('articlecomment')
            ->where('id', $res_id)
            ->update(['maincommentid' => $res_id]);

        $data['maincommentid'] = $res_id;
        $data['touserarray'] = [];

        if ($my_aid != $article_db->writerid) {
            $data = [
                'userid' => $article_db->writerid,
                'notice' => mb_substr($user_db->name . '在《' . $article_db->name . '》你的文章底下评论' . $text, 0, Mynotice::$notice_len_limit),
                'articleid' => $article_id,
                'videoid' => 0,
                'fanuserid' => 0,
                'questionid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }

        $data['userheadimg'] = $user_db->headimage;
        Base::dataToSafe($data, true);
        if ($res_id) {
            return \json(['code' => 1, 'msg' => '评论发表成功', 'data' => $data]);
        }
        return \json(['code' => -1, 'msg' => '评论发表失败', 'data' => []]);
    }

    /**
     * 子评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function addToUserComment(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $temtext = $request->post('text');
        $tem = preg_replace('/ /', '', $temtext);
        if ($tem == '') {
            return \json(['code' => 0, 'msg' => '评论内容不能为空']);
        }
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $text = $request->post('text');
        $maincomment_uid = $request->post('maincomment_id');
        $maincomment_id = Base::getIdByUid($maincomment_uid);
        $touser_uid = $request->post('touser_id');
        $touser_id = Base::getIdByUid($touser_uid);
        $user_db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();

        $touser = Db::table('user')
            ->where('id', $touser_id)
            ->where('isdel', 0)
            ->select('name')
            ->first();

        if (!$touser) {
            return \json(['code' => -1, 'msg' => '该用户已注销，无法回复该用户']);
        }
        $data = [
            'articleid' => $article_id,
            'userid' => $my_aid,
            'text' => $text,
            'time' => date('Y-m-d h:i:s', time()),
            'username' => $user_db->name,
            'touserid' => $touser_id,
            'tousername' => $touser->name,
            'maincommentid' => $maincomment_id
        ];
        $article_db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->first();
        if (!$article_db) {
            return \json(['code' => -1, 'msg' => '文章已被删除，评论发表失败，请刷新页面！', 'data' => []]);
        }
        if (
            !Db::table('articlecomment')
                ->where('articleid', $article_id)
                ->where('id', $maincomment_id)
                ->where('isdel', 0)
                ->exists()
        ) {
            return \json(['code' => -1, 'msg' => '评论已被删除，无法回复，请刷新页面！', 'data' => []]);
        }
        $resid = Base::insertToDb('articlecomment', $data);

        if ($my_aid != $touser_id) {
            $data = [
                'userid' => $touser_id,
                'notice' => mb_substr($user_db->name . '在《' . $article_db->name . '》回复了你' . $text, 0, Mynotice::$notice_len_limit),
                'articleid' => $article_id,
                'questionid' => 0,
                'videoid' => 0,
                'fanuserid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }

        // 获取主评论的用户id
        $mainuserdb = Db::table('articlecomment')
            ->where('id', $maincomment_id)
            ->where('isdel', 0)
            ->select('userid')
            ->first();
        // 评论底下不是自己回复自己则发送通知
        if ($mainuserdb && $mainuserdb->userid != $my_aid) {
            $data = [
                'userid' => $mainuserdb->userid,
                'notice' => mb_substr($user_db->name . '在《' . $article_db->name . '》你的评论底下评论' . $text, 0, Mynotice::$notice_len_limit),
                'articleid' => $article_id,
                'videoid' => 0,
                'fanuserid' => 0,
                'questionid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }
        $writerdb = Db::table('user')
            ->where('id', $article_db->writerid)
            ->where('isdel', 0)
            ->exists();
        if ($writerdb && $my_aid != $article_db->writerid) {
            $data = [
                'userid' => $article_db->writerid,
                'notice' => mb_substr($user_db->name . '在《' . $article_db->name . '》你的文章底下评论' . $text, 0, Mynotice::$notice_len_limit),
                'articleid' => $article_id,
                'fanuserid' => 0,
                'videoid' => 0,
                'questionid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }

        $data['userheadimg'] = Base::getUserHeadimage($my_aid);
        $data['touserheadimg'] = Base::getUserHeadimage($touser_id);
        Base::dataToSafe($data, true);
        if ($resid) {
            return \json(['code' => 1, 'msg' => '评论发表成功', 'data' => $data]);
        }
        return \json(['code' => -1, 'msg' => '评论发表失败', 'data' => []]);
    }

    /**
     * 加载评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadComment(Request $request)
    {
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $limit = ArticleComment::$comment_list_limit;
        $comment_uid = $request->post('comment_id');
        $comment_id = Base::getIdByUid($comment_uid);
        Base::judgeLimitIsSafe($limit);
        if ($comment_id) {
            $db = Db::table('articlecomment')
                ->where('id', $comment_id)
                ->exists();
            if (!$db) {
                return \json(['data' => []]);
            }
            $comment_db = Db::table('articlecomment')
                ->where('articleid', $article_id)
                ->where('touserid', "0")
                ->where('id', '<', $comment_id)
                ->where('isdel', 0)
                ->select(ArticleComment::$comment_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $comment_db = Db::table('articlecomment')
                ->where('articleid', $article_id)
                ->where('touserid', "0")
                ->where('isdel', 0)
                ->select(ArticleComment::$comment_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        }
        $res = array();
        foreach ($comment_db as &$tem) {
            $tem->userheadimg = '';
            $tem->userheadimg = Base::getUserHeadimage($tem->userid);
            $temary = get_object_vars($tem);
            $temary['touserarray'] = array();
            $temdb = Db::table('articlecomment')
                ->where('articleid', $article_id)
                ->where('maincommentid', $tem->id)
                ->where('id', '!=', $tem->id)
                ->where('isdel', 0)
                ->select(ArticleComment::$comment_db_key)
                ->get();
            if ($temdb && !empty($temdb)) {
                foreach ($temdb as &$tt) {
                    $tt->userheadimg = '';
                    $tt->touserheadimg = '';
                    $tt->userheadimg = Base::getUserHeadimage($tt->userid);
                    $tt->touserheadimg = Base::getUserHeadimage($tt->touserid);
                    $temary['touserarray'][] = get_object_vars($tt);
                }
            }
            if (!empty($temary) && sizeof($temary) > 0) {
                $res[] = $temary;
            }
        }
        Base::dataToSafe($res, true);
        return \json(['code' => 1, 'data' => $res]);
    }

    /**
     * 删除一条评论
     * @param Request $request
     * @return string $res json
     */
    public function deleteComment(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $delete_uid = $request->post('comment_id');
        $delete_id = Base::getIdByUid($delete_uid);
        $is_root = Base::judgeIsRoot($my_aid);

        $db = Db::table('articlecomment')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->select(ArticleComment::$comment_db_key)
            ->first();
        if (!$db) {
            return \json(['code' => -1, 'msg' => '评论不存在！']);
        }
        if (!$is_root && $db->userid != $my_aid) {
            return \json(['code' => -1, 'msg' => '权限不足！']);
        }
        $res = Db::table('articlecomment')
            ->where('articleid', $article_id)
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('articlecomment')
            ->where('articleid', $article_id)
            ->where('touserid', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('articlecomment')
            ->where('articleid', $article_id)
            ->where('maincommentid', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        if ($res) {
            return \json(['code' => 1, 'msg' => '删除成功']);
        }
        return \json(['code' => -1, 'msg' => '删除失败']);
    }
}
;