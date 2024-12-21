<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Answer
{
    /**
     * 回答数据库展示给用户的字段
     * @var array $answer_db_key 回答数据库展示给用户的字段
     */
    static $answer_db_key = [
        'id',
        'questionid',
        'mainanswerid',
        'userid',
        'touserid',
        'answer',
        'time'
    ];

    /**
     * 回答加载条数限制
     */
    static $answer_list_limit = 10;

    /**
     * 主回答
     * @param Request $request 请求
     * @return string $res json
     */
    public function addAnswer(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $answer = $request->post('answer');
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $tem = preg_replace('/ /', '', $answer);
        if ($tem == '') {
            return \json(['code' => 0, 'msg' => '回答不能为空']);
        }
        $answer = Base::removeImgAlt($answer);
        $user_db = Base::getUserData($my_aid);
        if (!$user_db || empty($user_db)) {
            return \json(['code' => 0, 'msg' => '用户不存在']);
        }

        $data = [
            'questionid' => $question_id,
            'userid' => $my_aid,
            'mainanswerid' => 0,
            'touserid' => 0,
            'answer' => $answer,
            'time' => date('Y-m-d h:i:s', time()),
        ];
        $question_db = Db::table('question')
            ->where('id', $question_id)
            ->where('isdel', 0)
            ->select('userid', 'name')
            ->first();
        if (!$question_db) {
            return \json(['code' => -1, 'msg' => '问题不存在，回答发表失败', 'data' => []]);
        }

        $weiter_db = Base::getUserData($question_db->userid);
        if (!$weiter_db || empty($weiter_db)) {
            return \json(['code' => -1, 'msg' => '用户不存在，回答发表失败', 'data' => []]);
        }

        $res_id = Base::insertToDb('answer', $data);

        Db::table('answer')
            ->where('id', $res_id)
            ->update(['mainanswerid' => $res_id]);
        $data['mainanswerid'] = $res_id;
        $data['touserarray'] = [];
        if ($my_aid != $question_db->userid) {
            $data = [
                'userid' => $question_db->userid,
                'notice' => mb_substr($user_db->name . '在《' . $question_db->name . '》问题回复了你' . $answer, 0, Mynotice::$notice_len_limit),
                'articleid' => 0,
                'questionid' => $question_id,
                'videoid' => 0,
                'fanuserid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }
        $data['userheadimg'] = $user_db->headimage;
        Db::table('question')
            ->where('id', $question_id)
            ->increment('answer_num', 1);
        Base::dataToSafe($data, true);
        if ($res_id) {
            return \json(['code' => 1, 'msg' => '回答发表成功', 'data' => $data]);
        }
        return \json(['code' => -1, 'msg' => '回答发表失败', 'data' => []]);
    }

    /**
     * 子回答
     * @param Request $request 请求
     * @return string $res json
     */
    public function addToUserAnswer(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $temanswer = $request->post('answer');
        $tem = preg_replace('/ /', '', $temanswer);
        if ($tem == '') {
            return \json(['code' => 0, 'msg' => '回答内容不能为空']);
        }
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $answer = $request->post('answer');
        $answer = Base::removeImgAlt($answer);
        $mainanswer_uid = $request->post('mainanswer_id');
        $mainanswer_id = Base::getIdByUid($mainanswer_uid);
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
            'questionid' => $question_id,
            'userid' => $my_aid,
            'answer' => $answer,
            'time' => date('Y-m-d h:i:s', time()),
            'touserid' => $touser_id,
            'mainanswerid' => $mainanswer_id
        ];
        $question_db = Db::table('question')
            ->where('id', $question_id)
            ->where('isdel', 0)
            ->select('userid', 'name')
            ->first();
        if (!$question_db) {
            return \json(['code' => -1, 'msg' => '问题已被删除，回答发表失败，请刷新页面！', 'data' => []]);
        }
        if (
            !Db::table('answer')
                ->where('questionid', $question_id)
                ->where('id', $mainanswer_id)
                ->where('isdel', 0)
                ->exists()
        ) {
            return \json(['code' => -1, 'msg' => '回答已被删除，无法回复，请刷新页面！', 'data' => []]);
        }
        $resid = Base::insertToDb('answer', $data);

        if ($my_aid != $touser_id) {
            $data = [
                'userid' => $touser_id,
                'notice' => mb_substr($user_db->name . '在《' . $question_db->name . '》回复了你' . $answer, 0, Mynotice::$notice_len_limit),
                'articleid' => 0,
                'questionid' => $question_id,
                'videoid' => 0,
                'fanuserid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }

        // 获取主回答的用户id
        $mainuserdb = Db::table('answer')
            ->where('id', $mainanswer_id)
            ->where('isdel', 0)
            ->select('userid')
            ->first();
        // 回答底下不是自己回复自己则发送通知
        if ($mainuserdb && $mainuserdb->userid != $my_aid) {
            $data = [
                'userid' => $mainuserdb->userid,
                'notice' => mb_substr($user_db->name . '在《' . $question_db->name . '》你的回答底下回答' . $answer, 0, Mynotice::$notice_len_limit),
                'questionid' => $question_id,
                'articleid' => 0,
                'videoid' => 0,
                'fanuserid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }
        $writerdb = Db::table('user')
            ->where('id', $question_db->userid)
            ->where('isdel', 0)
            ->exists();
        if ($writerdb && $my_aid != $question_db->userid) {
            $data = [
                'userid' => $question_db->userid,
                'notice' => $user_db->name . '在《' . $question_db->name . '》你的问题底下回答' . $answer,
                'questionid' => $question_id,
                'articleid' => 0,
                'fanuserid' => 0,
                'videoid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ];
            Base::insertToDb('usernotice', $data);
        }

        $data['userheadimg'] = Base::getUserHeadimage($my_aid);
        $data['touserheadimg'] = Base::getUserHeadimage($touser_id);
        Db::table('question')
            ->where('id', $question_id)
            ->increment('answer_num', 1);
        Base::dataToSafe($data, true);
        if ($resid) {
            return \json(['code' => 1, 'msg' => '回答发表成功', 'data' => $data]);
        }
        return \json(['code' => -1, 'msg' => '回答发表失败', 'data' => []]);
    }

    /**
     * 加载回答
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadAnswer(Request $request)
    {
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $limit = Answer::$answer_list_limit;
        $answer_uid = $request->post('answer_id');
        $answer_id = Base::getIdByUid($answer_uid);
        Base::judgeLimitIsSafe($limit);
        if ($answer_id) {
            $db = Db::table('answer')
                ->where('id', $answer_id)
                ->exists();
            if (!$db) {
                return \json(['data' => []]);
            }
            $answer_db = Db::table('answer')
                ->where('questionid', $question_id)
                ->where('touserid', "0")
                ->where('id', '<', $answer_id)
                ->where('isdel', 0)
                ->select(Answer::$answer_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $answer_db = Db::table('answer')
                ->where('questionid', $question_id)
                ->where('touserid', "0")
                ->where('isdel', 0)
                ->select(Answer::$answer_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        }
        $res = array();
        foreach ($answer_db as &$tem) {
            $tem->userheadimg = '';
            $user = Base::getUserData($tem->userid);
            if ($user && !empty($user)) {
                $tem->userheadimg = $user->headimage;
                $tem->username = $user->name;
            } else {
                $tem->userheadimg = '';
                $tem->username = '';
            }
            $temary = get_object_vars($tem);
            $temary['touserarray'] = array();
            $temdb = Db::table('answer')
                ->where('questionid', $question_id)
                ->where('mainanswerid', $tem->id)
                ->where('id', '!=', $tem->id)
                ->where('isdel', 0)
                ->select(Answer::$answer_db_key)
                ->get();
            if ($temdb && !empty($temdb)) {
                foreach ($temdb as &$tt) {
                    $tt->userheadimg = '';
                    $tt->touserheadimg = '';
                    $touser = Base::getUserData($tt->userid);
                    if ($touser) {
                        $tt->userheadimg = $touser->headimage;
                        $tt->username = $touser->name;
                    } else {
                        $tt->userheadimg = '';
                        $tt->username = '';
                    }
                    $totouser = Base::getUserData($tt->touserid);
                    if ($totouser) {
                        $tt->touserheadimg = $totouser->headimage;
                        $tt->tousername = $totouser->name;
                    } else {
                        $tt->touserheadimg = '';
                        $tt->tousername = '';
                    }
                    $tt->answer = Base::removeImgAlt($tt->answer);
                    $temary['touserarray'][] = get_object_vars($tt);
                }
            }
            if (!empty($temary) && sizeof($temary) > 0) {
                $res[] = $temary;
            }
        }
        Base::dataToSafe($res, true);
        return \json(['data' => $res]);
    }

    /**
     * 删除一条答案
     * @param Request $request
     * @return string $res json
     */
    public function deleteAnswer(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $question_uid = $request->post('question_id');
        $question_id = Base::getIdByUid($question_uid);
        $delete_uid = $request->post('answer_id');
        $delete_id = Base::getIdByUid($delete_uid);
        $is_root = Base::judgeIsRoot($my_aid);

        $db = Db::table('answer')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->select(Answer::$answer_db_key)
            ->first();
        if (!$db) {
            return \json(['code' => -1, 'msg' => '回答不存在！']);
        }
        if (!$is_root && $db->userid != $my_aid) {
            return \json(['code' => -1, 'msg' => '权限不足！']);
        }
        $res = Db::table('answer')
            ->where('questionid', $question_id)
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('answer')
            ->where('questionid', $question_id)
            ->where('touserid', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('answer')
            ->where('questionid', $question_id)
            ->where('mainanswerid', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        if ($res) {
            return \json(['code' => 1, 'msg' => '删除成功']);
        }
        return \json(['code' => -1, 'msg' => '删除失败']);
    }
};
