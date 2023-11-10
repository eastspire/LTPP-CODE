<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;


class Video
{
    /**
     * 视频文件夹路径
     * @var string $video_root_path 视频文件夹路径
     */
    static $video_root_path = 'static/video/';

    /**
     * @var array $video_db_key 数据库展示视频字段
     */
    static $video_db_key = [
        'id',
        'name',
        'tag',
        'url',
        'fabulous',
        'love'
    ];

    /**
     * @var array $video_comment_db_key 数据库展示视频评论字段
     */
    static $video_comment_db_key = [
        'id',
        'maincommentid',
        'userid',
        'touserid',
        'videoid',
        'username',
        'text',
        'tousername',
        'time'
    ];


    /**
     * 获取收藏或者点赞的视频
     * @param string $db_name 数据库名称
     * @param string $user_id 用户id
     * @param int $page 第几页
     * @param int $limit 每页数据上限
     * @return array $res [视频数据，总数]
     */
    static protected function getVideoFromLoveOrFabulous($db_name, $user_id, $page, $limit, $key = '')
    {
        $allnum = 0;
        $res = [];
        if ($key == '') {
            // 不是搜索
            $db = Db::table($db_name)
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->get();
            if (!$db) {
                return [[], 0];
            }
            foreach ($db as &$tem) {
                $tem_res = Db::table('video')
                    ->where('id', $tem->videoid)
                    ->where('isdouyin', 0)
                    ->select(Video::$video_db_key)
                    ->first();
                if (!$tem_res) {
                    continue;
                }
                ++$allnum;
                if ($allnum == $page) {
                    $res = $tem_res;
                }
            }
        } else {
            // 是搜索
            $db = Db::table($db_name)
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->select('videoid')
                ->get();
            if (!$db) {
                return [[], 0];
            }
            foreach ($db as &$tem) {
                $oneVideo = Db::table('video')
                    ->where('id', $tem->videoid)
                    ->where('isdel', 0)
                    ->where('isdouyin', 0)
                    ->where('name', 'like', '%' . $key . '%')
                    ->orderBy('id', 'desc')
                    ->select(Video::$video_db_key)
                    ->first();
                if (!$oneVideo) {
                    continue;
                }
                ++$allnum;
                if ($allnum == $page) {
                    $res = $oneVideo;
                }
            }
        }
        return [$res, $allnum];
    }

    /**
     * 上传视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function uploadVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $md5month = md5(date("Y-m", time()));
        $path = Base::$LTPP_public_path . Video::$video_root_path . $md5month;
        Base::judgeCreatPath($path);
        $file = $request->file('file');
        if ($file->getUploadExtension() != 'mp4') {
            Base::deleteAllFile($file->getRealPath());
            return json(['code' => -1, 'msg' => '格式错误，仅支持mp4格式文件']);
        }
        do {
            $name = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($path . '/' . $name));
        $file->move($path . '/' . $name);

        Base::$GLOBlinuxurl = Base::getGLOBlinuxurl();

        $tag = '';
        $up_name = str_replace('.' . $file->getUploadExtension(), '', $file->getUploadName());
        for ($i = 0; $i < strlen($up_name); ) {
            if ($up_name[$i] == '#') {
                $tem = '';
                for ($j = $i + 1; $j < strlen($up_name); ++$j) {
                    if ($up_name[$j] == ' ' || $up_name[$j] == '#') {
                        $i = $j - 1;
                        break;
                    }
                    $tem .= $up_name[$j];
                }
                if ($tem != '') {
                    $tag = $tag . ' ' . $tem;
                }
            }
            ++$i;
        }
        Base::insertToDb('video', [
            'name' => $up_name,
            'isdouyin' => 0,
            'tag' => $tag,
            'url' => Base::$GLOBlinuxurl . '/' . Video::$video_root_path . $md5month . '/' . $name
        ]);
        Base::deleteAllFile($file->getRealPath());
        return json(['code' => 1, 'msg' => '上传成功']);
    }

    /**
     * 添加视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function addVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');
        $data = [
            'name' => $tabledata['name'],
            'isdouyin' => 0,
            'tag' => $tabledata['tag'],
            'url' => $tabledata['url'],
            'fabulous' => 0,
            'love' => 0
        ];
        if ($tabledata['name'] == '' || $tabledata['url'] == '') {
            return json(['code' => -1, 'msg' => '信息不全']);
        }
        $res = Base::insertToDb('video', $data);
        if ($res) {
            return json(['code' => 1, 'msg' => '视频添加成功']);
        }
        return json(['code' => -1, 'msg' => '视频添加失败']);
    }

    /**
     * 删除一个视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->select('url')
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '该条记录不存在，无法删除']);
        }
        $info = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('videocomment')
            ->where('videoid', $video_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        $url = $db->url;
        $len = strlen($url);
        $name = '';
        $times = 0;
        for ($i = $len - 1; $i >= 0; --$i) {
            if ($times == 1 && $url[$i] == '/') {
                break;
            }
            if ($url[$i] == '/') {
                ++$times;
            }
            $name = $url[$i] . $name;
        }
        if (file_exists(Base::$LTPP_public_path . Video::$video_root_path . $name)) {
            unlink(Base::$LTPP_public_path . Video::$video_root_path . $name);
        }
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'msg' => '视频删除成功']);
        }
        return json(['code' => -1, 'data' => $info, 'msg' => '视频删除失败']);
    }

    /**
     * 更新视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $tabledata = $request->post('tabledata');
        $tabledata['id'] = Base::getIdByUid($tabledata['id']);
        $info = Db::table('video')
            ->where('id', $tabledata['id'])
            ->update([
                'url' => $tabledata['url'],
                'name' => $tabledata['name'],
                'tag' => $tabledata['tag']
            ]);

        return json(['code' => 1, 'data' => $info, 'msg' => '视频更新成功']);
    }

    /**
     * 搜索视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function findVideo(Request $request)
    {
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = 1;
        $info = Db::table('video')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->where('isdouyin', 0)
            ->select(Video::$video_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('video')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->where('isdouyin', 0)
            ->count();
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info[0], 'allnum' => $allnum, 'msg' => '查找视频成功']);
        }
        return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '未找到符合条件的视频']);
    }

    /**
     * 手机端视频列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function appLoadVideo(Request $request)
    {
        $video_uid = $request->post('id');
        $video_id = Base::getIdByUid($video_uid);
        $do = $request->post('do');
        $limit = 3;
        $data = Base::getDataByLimit('video', $video_id, $limit, Video::$video_db_key, $do);
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'msg' => '视频列表获取成功']);
    }


    /**
     * 视频列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadVideo(Request $request)
    {
        $page = $request->post('page');
        $limit = 1;
        $info = Db::table('video')
            ->where('isdel', 0)
            ->where('isdouyin', 0)
            ->select(Video::$video_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('video')
            ->where('isdel', 0)
            ->where('isdouyin', 0)
            ->count();
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info[0], 'allnum' => $allnum, 'msg' => '视频列表获取成功']);
        }
        return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '暂时没有视频']);
    }

    /**
     * 后台视频表格搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function backFindVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '权限不足']);
        }
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('video')
            ->orWhere(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($key) {
                $query->where('tag', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->select(Video::$video_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('video')
            ->orWhere(function ($query) use ($key) {
                $query->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($key) {
                $query->where('tag', 'like', '%' . $key . '%')
                    ->where('isdel', 0);
            })
            ->count();
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '查找视频成功']);
        }
        return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '未找到符合条件的视频']);
    }

    /**
     * 后台视频表格列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function backLoadVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '权限不足']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('video')
            ->where('isdel', 0)
            ->select(Video::$video_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('video')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '视频列表获取成功']);
        }
        return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '暂时没有视频']);
    }

    /**
     * 清空视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteAllVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'data' => [], 'allnum' => 0, 'msg' => '权限不足']);
        }
        Db::table('video')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('fabulousvideo')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('lovevideo')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('videocomment')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Base::deleteAllFile(Base::$LTPP_public_path . Video::$video_root_path);
        return json(['code' => 1, 'msg' => '清空视频成功']);
    }

    /**
     * 判断是否点赞该视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeIsFabulous(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => 1, 'data' => 1]);
        }
        return json(['code' => 1, 'data' => 0]);
    }

    /**
     * 判断是否收藏该视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeIsLove(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->exists();
        if ($db) {
            return json(['code' => 1, 'data' => 1]);
        }
        return json(['code' => 1, 'data' => 0]);
    }

    /**
     * 加载评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadUserComment(Request $request)
    {
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $comment_uid = $request->post('comment_id');
        $comment_id = Base::getIdByUid($comment_uid);
        $limit = 10;
        Base::judgeLimitIsSafe($limit);
        if ($comment_id) {
            $res_db = Db::table('videocomment')
                ->where('videoid', $video_id)
                ->where('maincommentid', 0)
                ->where('id', '<', $comment_id)
                ->where('isdel', 0)
                ->select(Video::$video_comment_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $res_db = Db::table('videocomment')
                ->where('videoid', $video_id)
                ->where('maincommentid', 0)
                ->where('isdel', 0)
                ->select(Video::$video_comment_db_key)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        }
        $is_end = (sizeof($res_db->toArray()) < $limit);
        $res = array();
        foreach ($res_db as &$tem) {
            $tem->userheadimg = Base::getUserHeadimage($tem->userid);
            $temary = get_object_vars($tem);
            $temary['touserarray'] = array();
            $temdb = Db::table('videocomment')
                ->where('videoid', $video_id)
                ->where('maincommentid', $tem->id)
                ->where('id', '!=', $tem->id)
                ->where('isdel', 0)
                ->get();
            if ($temdb && !empty($temdb)) {
                foreach ($temdb as $tt) {
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
        return \json(['code' => 1, 'data' => $res, 'is_end' => $is_end]);
    }

    /**
     * 发表评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function sendMyComment(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $comment = $request->post('text');
        $temtext = $request->post('text');
        $tem = preg_replace('/ /', '', $temtext);
        if ($tem == '') {
            return \json(['code' => 0, 'msg' => '评论内容不能为空']);
        }

        $time = date('Y-m-d H:i:s', time());
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $maincomment_uid = $request->post('maincomment_id');
        $maincomment_id = Base::getIdByUid($maincomment_uid);
        $username = '';
        $tousername = '';
        if (!$maincomment_id) {
            $maincomment_id = 0;
        }
        $touser_uid = $request->post('touser_id');
        $touser_id = Base::getIdByUid($touser_uid);
        $username = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if (!$username) {
            return json(['code' => -1, 'msg' => '该用户不存在']);
        }
        $username = $username->name;
        if ($touser_id && $touser_id != 0) {
            $tousername = Db::table('user')
                ->where('id', $touser_id)
                ->where('isdel', 0)
                ->select('name')
                ->first();
            if (!$tousername) {
                return json(['code' => -1, 'msg' => '该用户不存在']);
            }
            $tousername = $tousername->name;
        }
        if ($touser_id == 0) {
            $touser_id = $my_aid;
        }
        $videodb = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->first();
        if (!$videodb) {
            return json(['code' => -1, 'msg' => '该视频不存在']);
        }

        $comment_id = Base::insertToDb('videocomment', [
            'videoid' => $video_id,
            'userid' => $my_aid,
            'text' => $comment,
            'time' => $time,
            'username' => $username,
            'tousername' => $tousername,
            'touserid' => $touser_id,
            'maincommentid' => $maincomment_id
        ]);
        // 不是给自己回复
        if ($touser_id != $my_aid) {
            Base::insertToDb('usernotice', [
                'userid' => $touser_id,
                'notice' => mb_substr($username . '在视频《' . $videodb->name . '》中评论了你:' . $comment, 0, Mynotice::$notice_len_limit),
                'articleid' => 0,
                'fanuserid' => 0,
                'questionid' => 0,
                'videoid' => $video_id,
                'time' => date('Y-m-d H:i:s', time())
            ]);
        }
        if ($comment_id) {
            return json(['code' => 1, 'msg' => '评论成功']);
        }
        return json(['code' => -1, 'msg' => '评论失败']);
    }

    /**
     * 查看一个视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookOneVideo(Request $request)
    {
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $videodb = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->where('isdouyin', 0)
            ->select(Video::$video_db_key)
            ->first();
        if (!$videodb) {
            return json(['code' => -1, 'data' => [], 'msg' => '视频不存在']);
        }
        Base::dataToSafe($videodb);
        return json(['code' => 1, 'data' => $videodb, 'msg' => '视频加载完成']);
    }

    /**
     * 收藏视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function loveVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->select(Video::$video_db_key)
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '视频不存在']);
        }
        $db = Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->first();
        if ($db) {
            return json(['code' => -1, 'msg' => '不允许重复收藏']);
        }

        $db = Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 1)
            ->select('id')
            ->first();

        if ($db) {
            Db::table('lovevideo')
                ->where('id', $db->id)
                ->update([
                    'isdel' => 0
                ]);
        } else {
            Db::table('lovevideo')->insert([
                'videoid' => $video_id,
                'userid' => $my_aid
            ]);
        }

        Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->increment('love', 1);
        return json(['code' => 1, 'msg' => '收藏成功']);
    }

    /**
     * 点赞视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function fabulousVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            return json(['code' => -1, 'msg' => '视频不存在']);
        }
        $db = Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => -1, 'msg' => '不允许重复点赞']);
        }

        $db = Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 1)
            ->select('id')
            ->first();

        if ($db) {
            Db::table('fabulousvideo')
                ->where('id', $db->id)
                ->update([
                    'isdel' => 0
                ]);
        } else {
            Db::table('fabulousvideo')->insert([
                'videoid' => $video_id,
                'userid' => $my_aid
            ]);
        }


        Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->increment('fabulous', 1);
        return json(['code' => 1, 'msg' => '点赞成功']);
    }

    /**
     * 取消点赞
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteFabulousVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            return json(['code' => -1, 'msg' => '视频不存在']);
        }
        $db = Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            return json(['code' => -1, 'msg' => '您未点赞该视频']);
        }
        Db::table('fabulousvideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('video')
            ->where('id', $video_id)
            ->where('fabulous', '>', 0)
            ->where('isdel', 0)
            ->decrement('fabulous', 1);
        return json(['code' => 1, 'msg' => '已取消点赞']);
    }

    /**
     * 取消收藏
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteLoveVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);
        $db = Db::table('video')
            ->where('id', $video_id)
            ->where('isdel', 0)
            ->select(Video::$video_db_key)
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '视频不存在']);
        }
        Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('lovevideo')
            ->where('videoid', $video_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('video')
            ->where('id', $video_id)
            ->where('love', '>', 0)
            ->where('isdel', 0)
            ->decrement('love', 1);
        return json(['code' => 1, 'msg' => '已取消收藏']);
    }

    /**
     * 删除评论
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteComment(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);

        $video_uid = $request->post('video_id');
        $video_id = Base::getIdByUid($video_uid);

        $delete_uid = $request->post('delete_id');
        $delete_id = Base::getIdByUid($delete_uid);


        $db = Db::table('videocomment')
            ->where('id', $delete_id)
            ->where('videoid', $video_id)
            ->where('isdel', 0)
            ->select('userid')
            ->first();
        if (!$db) {
            return \json(['code' => -1, 'msg' => '评论不存在！']);
        }
        if (!$isroot && $db->userid != $my_aid) {
            return \json(['code' => -1, 'msg' => '权限不足！']);
        }
        Db::table('videocomment')
            ->where('id', $delete_id)
            ->where('videoid', $video_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('videocomment')
            ->where('videoid', $video_id)
            ->where('maincommentid', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        return \json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 搜索收藏的视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function findLoveVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = 1;
        $db = Video::getVideoFromLoveOrFabulous('LoveVideo', $my_aid, $page, $limit, $key);
        $res = $db[0];
        $allnum = $db[1];
        Base::dataToSafe($res);
        if ($res) {
            return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => '查找视频成功']);
        }
        return json(['code' => -1, 'data' => $res, 'allnum' => $allnum, 'msg' => '未找到符合条件的视频']);
    }

    /**
     * 收藏的视频列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadLoveVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = 1;
        $db = Video::getVideoFromLoveOrFabulous('lovevideo', $my_aid, $page, $limit);
        $res = $db[0];
        $allnum = $db[1];
        Base::dataToSafe($res);
        if ($res) {
            return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => '视频加载成功']);
        }
        return json(['code' => -1, 'data' => $res, 'allnum' => $allnum, 'msg' => '未有收藏的视频']);
    }

    /**
     * 搜索点赞的视频
     * @param Request $request 请求
     * @return string $res json
     */
    public function findFabulousVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = 1;
        $db = Video::getVideoFromLoveOrFabulous('fabulousvideo', $my_aid, $page, $limit, $key);
        $res = $db[0];
        $allnum = $db[1];
        Base::dataToSafe($res);
        if ($res) {
            return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => '查找视频成功']);
        }
        return json(['code' => -1, 'data' => $res, 'allnum' => $allnum, 'msg' => '未找到符合条件的视频']);
    }

    /**
     * 点赞的视频列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadFabulousVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = 1;
        $db = Video::getVideoFromLoveOrFabulous('fabulousvideo', $my_aid, $page, $limit);
        $res = $db[0];
        $allnum = $db[1];
        Base::dataToSafe($res);
        if ($res) {
            return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => '视频加载成功']);
        }
        return json(['code' => -1, 'data' => $res, 'allnum' => $allnum, 'msg' => '未有点赞的视频']);
    }
}
;