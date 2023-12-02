<?php

namespace app\controller;

use Exception;
use stdClass;
use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

class User
{
    static $user_db_key = [
        'id',
        'name',
        'online',
        'acnum',
        'fans',
        'registertime',
        'lastlogin',
        'sex',
        'headimage',
        'follow',
        'mysay',
        'email',
        'student_number',
        'enrollment_year',
        'school',
        'college',
        'subject',
        'class'
    ];

    static $video_bk_root_path = 'static/background/video/';
    static $image_bk_root_path = 'static/background/image/';
    static $headimage_root_path = 'static/headimage/';


    static $bk_user_db_key = [
        'id',
        'name',
        'online',
        'acnum',
        'grade',
        'fans',
        'password',
        'registertime',
        'lastlogin',
        'sex',
        'headimage',
        'follow',
        'email',
        'mysay',
        'student_number',
        'enrollment_year',
        'school',
        'college',
        'subject',
        'class',
        'money',
        'musiclovelistid',
        'musicuid'
    ];

    /**
     * 获取用户权限数值
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeGrade(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('grade')
            ->first();
        if (!$user_db) {
            return json(['code' => -1]);
        }
        return json(['code' => $user_db->grade]);
    }

    /**
     * 获取name
     * @param Request $request 请求
     * @return string $res json
     */
    public function getMyName(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if ($db) {
            return json(['code' => 1, 'name' => $db->name]);
        }
        return json(['code' => -1, 'name' => '无名氏']);
    }

    /**
     * 查看是否开启/关闭音乐
     * @param Request $request 请求
     * @return string $res json
     */
    public function getIsUseMusic(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('isusemusic')
            ->first();
        if (!$db) {
            return json(['code' => -1, 'data' => '用户不存在']);
        }
        $data = $db->isusemusic;
        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 更改音乐是否开启
     * @param Request $request 请求
     * @return string $res json
     */
    public function changeMusic(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('isusemusic')
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '用户不存在']);
        }
        if ($db->isusemusic == 1) {
            // 关闭音乐
            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->update(['isusemusic' => 0]);
            Base::updateUserDataRedis($my_aid);
            return json(['code' => 1, 'msg' => '音乐关闭成功']);
        }
        // 开启音乐
        Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->update(['isusemusic' => 1]);
        Base::updateUserDataRedis($my_aid);
        return json(['code' => 1, 'msg' => '音乐开启成功']);
    }

    /**
     * 使用QQ头像
     * @param Request $request 请求
     * @return string $res json
     */
    public function useQqHeadimage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('email')
            ->first();
        $qqheadimage = 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $db->email . '&spec=640';
        Db::table('user')
            ->where('id', $my_aid)
            ->update(['headimage' => $qqheadimage]);
        Base::updateUserDataRedis($my_aid);
        return \json(['code' => 1, 'msg' => '头像更新成功', 'url' => $qqheadimage]);
    }

    /**
     * 在线心跳
     * @param Request $request 请求
     * @return void $res json
     */
    public function sendHeart(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $now = time();
        //10分钟内有心跳记录，认定在线
        Db::table('user')
            ->where('id', $my_aid)
            ->update([
                'online' => 1,
                'lastlogin' => date('Y-m-d H:i:s', $now)
            ]);
        Base::updateUserDataRedis($my_aid);
    }

    /**
     * 加载头像
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadHeadimage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        return \json(['code' => 1, 'headimage' => Base::getUserHeadimage($my_aid)]);
    }

    /**
     * 获取图片背景
     * @param Request $request 请求
     * @return string $res json
     */
    public function getBkImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_data = Base::getUserData($my_aid);
        if (isset($user_data->bkimage)) {
            return json(['code' => 1, 'url' => $user_data->bkimage]);
        }
        return json(['code' => 1, 'url' => '']);
    }

    /**
     * 获取视频背景
     * @param Request $request 请求
     * @return string $res json
     */
    public function getBkVideo(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_data = Base::getUserData($my_aid);
        if (isset($user_data->bkvideo)) {
            return json(['code' => 1, 'url' => $user_data->bkvideo]);
        }
        return json(['code' => 1, 'url' => '']);
    }

    /**
     * 使用默认背景
     * @param Request $request 请求
     * @return void $res json
     */
    public function resetBkImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->update(['bkimage' => '']);
        Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('bkvideo')
            ->update(['bkvideo' => '']);
        Base::updateUserDataRedis($my_aid);
    }

    /**
     * 保存图片背景
     * @param Request $request 请求
     * @return string $res json
     */
    public function saveBkImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $file = $request->file('file');
        $fileextion = $file->getUploadExtension();
        if ($fileextion != 'jpg' && $fileextion != 'png' && $fileextion != 'jpeg' && $fileextion != 'gif') {
            return json(['code' => -1, 'url' => '', 'msg' => '图片格式不正确']);
        }
        // 大小限制
        if ($file->getSize() > Base::$image_size_limit && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'url' => '', 'msg' => '图片大小不能大于' . Base::$image_size_limit / Base::$one_mb_size . 'MB']);
        }
        $md5uid = Base::getPathMd5($my_aid);
        $newPath = Base::$LTPP_public_path . User::$image_bk_root_path . $md5uid; // 目标文件夹
        Base::judgeCreatPath($newPath);
        Base::deleteAllFile($newPath . '/');

        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            $userbk = Base::getGLOBlinuxurl() . '/' . User::$image_bk_root_path . $md5uid . '/' . $newName;

            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->update(['bkimage' => $userbk]);
            Base::updateUserDataRedis($my_aid);
            return \json(['code' => 1, 'msg' => '上传成功', 'url' => $userbk]);
        }
        return \json(['code' => -1, 'msg' => 'error', 'url' => "error"]);
    }

    /**
     * 保存视频背景
     * @param Request $request 请求
     * @return string $res json
     */
    public function saveVideoBkImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $file = $request->file('file');
        $fileextion = $file->getUploadExtension();
        if ($fileextion != 'mp4') {
            return json(['code' => -1, 'url' => '', 'msg' => '视频格式不正确']);
        }
        // 大小限制
        if ($file->getSize() > Base::$video_size_limit && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'url' => '', 'msg' => '视频大小不能大于' . Base::$video_size_limit / Base::$one_mb_size . 'MB']);
        }
        $md5uid = Base::getPathMd5($my_aid);
        $newPath = Base::$LTPP_public_path . User::$video_bk_root_path . $md5uid; // 目标文件夹
        Base::judgeCreatPath($newPath);
        Base::deleteAllFile($newPath . '/');

        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            $userbk = Base::getGLOBlinuxurl() . '/' . User::$video_bk_root_path . $md5uid . '/' . $newName;

            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->update(['bkvideo' => $userbk]);
            Base::updateUserDataRedis($my_aid);
            return \json(['code' => 1, 'msg' => '上传成功', 'url' => $userbk]);
        }
        return \json(['code' => -1, 'msg' => 'error', 'url' => "error"]);
    }

    /**
     * 保存头像
     * @param Request $request 请求
     * @return string $res json
     */
    public function saveHeadImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $file = $request->file('file');
        $fileextion = $file->getUploadExtension();
        if ($fileextion != 'jpg' && $fileextion != 'png' && $fileextion != 'jpeg' && $fileextion != 'gif') {
            return json(['code' => -1, 'url' => '', 'msg' => '图片格式不正确']);
        }
        // 大小限制
        if ($file->getSize() > Base::$image_size_limit && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'url' => '', 'msg' => '图片大小不能大于' . Base::$image_size_limit / Base::$one_mb_size . 'MB']);
        }
        $md5uid = Base::getPathMd5($my_aid);

        $newPath = Base::$LTPP_public_path . User::$headimage_root_path . $md5uid; // 目标文件夹
        Base::judgeCreatPath($newPath);
        Base::deleteAllFile($newPath . '/');

        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($newPath . '/' . $newName));

        if ($file && $file->isValid()) {
            $file->move($newPath . '/' . $newName);
            $userheadimage = Base::getGLOBlinuxurl() . '/' . User::$headimage_root_path . $md5uid . '/' . $newName;
            Db::table('user')
                ->where('id', $my_aid)
                ->update(['headimage' => $userheadimage]);

            Base::updateUserDataRedis($my_aid);
            return \json(['code' => 1, 'msg' => '上传成功', 'url' => $userheadimage]);
        }
        return \json(['code' => -1, 'msg' => 'error', 'url' => "error"]);
    }

    /**
     * 作者头像
     * @param Request $request 请求
     * @return string $res json
     */
    public function writerHeadimage(Request $request)
    {
        $writer_uid = $request->post('writer_id');
        $writer_id = Base::getIdByUid($writer_uid);
        $headimg = Base::getUserHeadimage($writer_id);
        return \json(['code' => 1, 'image' => $headimg]);
    }

    /**
     * 更新在线
     * @return void $res json
     */
    public function trueOnline()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->update(['online' => 1]);
        Base::updateUserDataRedis($my_aid);
    }

    /**
     * 更新离线
     * @return void $res json
     */
    public function falseOnline()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->update(['online' => 0]);
        Base::updateUserDataRedis($my_aid);
    }

    /**
     * 强制下线
     * @param Request $request 请求
     * @return string $res json
     */
    public function unOnline(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $user_uid = $request->post('user_id');
        $user_aid = Base::getIdByUid($user_uid);
        $redis14 = Redis::connection('db14');
        $redis14->del($user_aid . 'login');
        Db::table('user')
            ->where('id', $user_aid)
            ->where('isdel', 0)
            ->update(['online' => 0]);
        Base::updateUserDataRedis($my_aid);
        return json(['code' => 1, 'msg' => '强制下线成功！']);
    }


    /**
     * 判断是否关注
     * @param Request $request 请求
     * @return string $res json
     */
    public function JudgeIsFollow(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        $res = Db::table('followfans')
            ->where('userid', $my_aid)
            ->where('followid', $user_id)
            ->where('isdel', 0)
            ->exists();
        if ($res) {
            return \json(['code' => 1, 'msg' => '您已经关注过该用户，无法再次关注']);
        }
        return \json(['code' => -1, 'msg' => '您未关注该用户']);
    }

    /**
     * 粉丝关注转换
     * @param int id 用户id
     * @param int $page 第几页
     * @param int $limit 每页条数
     * @param string $do 操作
     * @param string $key 搜索关键字
     * @return array $res [数组，符合条件的总数目]
     */
    public function getFanFollow($id, $page, $limit, $do, $key = '')
    {
        Base::judgePageLimitIsSafe($page, $limit);
        $isroot = Base::judgeIsRoot($id);
        $allnum = 0;
        $res_fans = [];
        $res_follow = [];
        if ($do == 'follow') {
            $follow = Db::table('followfans')
                ->where('userid', $id)
                ->where('isdel', 0)
                ->get();
            $arr_follow = array();
            foreach ($follow as &$tem) {
                //获取关键字关注用户
                $dbfollow = Db::table('user')
                    ->where('id', $tem->followid)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0)
                    ->select(User::$user_db_key)
                    ->first();
                if ($dbfollow) {
                    $arr_follow[] = $dbfollow;
                }
            }

            $allnum = sizeof($arr_follow);
            $res_follow = array();
            for ($i = $limit * ($page - 1); $i < $limit * $page && $i < $allnum; ++$i) {
                $res_follow[] = $arr_follow[$i];
            }
            if (!$isroot) {
                foreach ($res_follow as &$tem) {
                    $tem->email = '保密信息';
                }
            }
        } else if ($do == 'fans') {
            $fans = Db::table('followfans')
                ->where('followid', $id)
                ->where('isdel', 0)
                ->get();
            $arr_fans = array();

            foreach ($fans as &$tem) {
                //获取粉丝列表
                $dbfans = Db::table('user')
                    ->where('id', $tem->userid)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0)
                    ->select(User::$user_db_key)
                    ->first();
                if ($dbfans) {
                    $arr_fans[] = $dbfans;
                }
            }
            $res_fans = array();
            $allnum = sizeof($arr_fans);
            for ($i = $limit * ($page - 1); $i < $limit * $page && $i < $allnum; ++$i) {
                $res_fans[] = $arr_fans[$i];
            }
            if (!$isroot) {
                foreach ($res_fans as &$tem) {
                    $tem->email = '保密信息';
                }
            }
        }

        if ($do == 'follow') {
            return [$res_follow, $allnum];
        }

        return [$res_fans, $allnum];
    }

    /**
     * 加载粉丝
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadFansList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $fans = $this->getFanFollow($my_aid, $page, $limit, 'fans');
        $res = $fans[0];
        $allnum = $fans[1];
        Base::dataToSafe($res);
        return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => "你有 $allnum 个粉丝"]);
    }

    /**
     * 删除粉丝
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteFans(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $fans_uid = $request->post('delete_id');
        $fans_id = Base::getIdByUid($fans_uid);
        $info = Db::table('followfans')
            ->where('userid', $fans_id)
            ->where('followid', $my_aid)
            ->update(['isdel' => 1]);
        if ($info) {
            //更新粉丝数目
            $user_db = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->select('fans')
                ->first();
            if (!$user_db) {
                return json(['code' => -1, 'msg' => '用户不存在']);
            }
            $fansnum = $user_db->fans - 1;
            if ($fansnum < 0) {
                $fansnum = 0;
            }
            Db::table('user')
                ->where('id', $my_aid)
                ->update(['fans' => $fansnum]);
            //更新关注
            $user_db = Db::table('user')
                ->where('id', $fans_id)
                ->where('isdel', 0)
                ->first();
            $follownum = $user_db->follow - 1;
            if ($follownum < 0) {
                $follownum = 0;
            }
            Db::table('user')
                ->where('id', $fans_id)
                ->where('isdel', 0)
                ->update(['follow' => $follownum]);
            Base::updateUserDataRedis($my_aid);
            return json(['code' => 1, 'msg' => '移除粉丝成功']);
        }
        Base::updateUserDataRedis($my_aid);
        return json(['code' => -1, 'msg' => '移除粉丝失败']);
    }

    /**
     * 搜索粉丝
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchFans(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $fans = $this->getFanFollow($my_aid, $page, $limit, 'fans', $key);
        $simply = $fans[0];
        $allnum = $fans[1];
        Base::dataToSafe($simply);
        return json(['code' => 1, 'data' => $simply, 'allnum' => $allnum, 'msg' => "查找到 $allnum 个粉丝"]);
    }

    /**
     * 关注用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function addFollow(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $follow_uid = $request->post('follow_id');
        $follow_id = Base::getIdByUid($follow_uid);
        return $this->followChange($my_aid, $follow_id, true);
    }

    /**
     * 判断现在是否关注
     */
    protected function judgeFollow($my_aid, $follow_id)
    {
        $is_has = Db::table('followfans')
            ->where('userid', $my_aid)
            ->where('followid', $follow_id)
            ->where('isdel', 0)
            ->exists();
        return $is_has;
    }

    /**
     * 判断曾经是否关注过
     */
    protected function judgeHistoryFollow($my_aid, $follow_id)
    {
        $db = Db::table('followfans')
            ->where('userid', $my_aid)
            ->where('followid', $follow_id)
            ->where('isdel', 1)
            ->select('id')
            ->first();
        if ($db) {
            return $db->id;
        }
        return 0;
    }

    /**
     * 用户关注加一/减一
     */
    protected function followChange($my_aid, $follow_id, $is_add = true)
    {
        $follow_user = Db::table('user')
            ->where('id', $follow_id)
            ->where('isdel', 0)
            ->exists();
        if (!$follow_user) {
            return \json(['code' => -1, 'msg' => '该用户账号已注销,无法关注']);
        }
        if ($is_add) {
            if ($this->judgeFollow($my_aid, $follow_id)) {
                return \json(['code' => -1, 'msg' => '您已关注,无法再次关注']);
            }
            //关注数加一
            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->increment('follow', 1);
            Db::table('user')
                ->where('id', $follow_id)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->increment('fans', 1);
            $userdb = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->select('name')
                ->first();
            Db::table('usernotice')
                ->insert([
                    'userid' => $follow_id,
                    'notice' => $userdb->name . '关注了你',
                    'questionid' => 0,
                    'videoid' => 0,
                    'articleid' => 0,
                    'fanuserid' => $my_aid,
                    'time' => date('Y-m-d H:i:s', time())
                ]);
            $has_id = $this->judgeHistoryFollow($my_aid, $follow_id);
            if ($has_id) {
                Db::table('followfans')
                    ->where('id', $has_id)
                    ->update(['isdel' => 0]);
            } else {
                Db::table('followfans')
                    ->insert([
                        'userid' => $my_aid,
                        'followid' => $follow_id
                    ]);
            }
            Base::updateUserDataRedis($my_aid);
            return \json(['code' => 1, 'msg' => '关注成功']);
        } else {
            if (!$this->judgeFollow($my_aid, $follow_id)) {
                return \json(['code' => -1, 'msg' => '您未关注']);
            }
            //关注数减一
            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->decrement('follow', 1);
            Db::table('user')
                ->where('id', $follow_id)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->decrement('fans', 1);
            Db::table('followfans')
                ->where('userid', $my_aid)
                ->where('followid', $follow_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Base::updateUserDataRedis($my_aid);
            return \json(['code' => 1, 'msg' => '取消关注成功']);
        }
    }

    /**
     * 加载关注列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadFollow(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        //获取关注
        $follow = $this->getFanFollow($my_aid, $page, $limit, 'follow');
        $res = $follow[0];
        $allnum = $follow[1];
        Base::dataToSafe($res);
        return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => "你关注了 $allnum 个用户"]);
    }

    /**
     * 取消关注
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteFollow(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $follow_uid = $request->post('delete_id');
        $follow_id = Base::getIdByUid($follow_uid);
        return $this->followChange($my_aid, $follow_id, false);
    }

    /**
     * 搜索关注
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchFollow(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);

        $page = $request->post('page');
        $limit = $request->post('limit');
        $key = $request->post('key');
        Base::judgePageLimitIsSafe($page, $limit);
        $follow = $this->getFanFollow($my_aid, $page, $limit, 'follow', $key);
        $simply = $follow[0];
        $allnum = $follow[1];
        Base::dataToSafe($simply);
        return json(['code' => 1, 'data' => $simply, 'allnum' => $allnum, 'msg' => "查找到 $allnum 个博主"]);
    }

    /**
     * 后台加载用户列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadUserList(Request $request)
    {
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('user')
            ->where('isdel', 0)
            ->select(User::$user_db_key)
            ->orderBy('grade', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('user')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => '加载用户列表成功']);
        }
        Base::dataToSafe($info);
        return json(['code' => -1, 'data' => $info, 'msg' => '加载用户列表失败']);
    }

    /**
     * 添加用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function addUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isadmin = Base::judgeIsAdmin($my_aid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isadmin) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $data = $request->post('data');
        if (strlen($data['name']) > 18 || strlen($data['name']) == 0) {
            return \json(['code' => -1, 'msg' => '用户名不能为空且长度不能大于18']);
        }

        if (empty($data)) {
            return \json(['code' => -1, 'msg' => '用户信息不能为空']);
        }

        if (!preg_match("/^[0-9]*$/", $data['grade'])) {
            return \json(['code' => -1, 'msg' => '权限必须是纯数字']);
        }

        if (strripos($data['email'], '@qq.com') === false) {
            return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
        }
        if (filter_var($data['headimage'], FILTER_VALIDATE_URL) === false) {
            $data['headimage'] = 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $data['email'] . '&spec=640';
        }

        $judgename = Db::table('user')
            ->where('name', $data['name'])
            ->where('isdel', 0)
            ->exists();
        if ($judgename) {
            return \json(['code' => -1, 'msg' => '用户已存在,请更换用户名后重新添加']);
        }

        if ($isadmin && !$isroot) {
            $data['grade'] = 1;
        }

        if ($data['sex'] == '' || ($data['sex'] != '男' && $data['sex'] != '女')) {
            $data['sex'] = '男';
        }

        $data['registertime'] = date('Y-m-d H:i:s', time());
        $data['lastlogin'] = date('Y-m-d H:i:s', time());
        $data['fans'] = 0;
        $data['follow'] = 0;
        $data['online'] = 0;
        $data['password'] = Base::passwordEncryption($data['password']);
        $res_id = Base::insertToDb('user', $data);
        Base::updateUserDataRedis($res_id);
        if ($res_id) {
            return json(['code' => 1, 'msg' => '用户添加成功']);
        }
        return json(['code' => -1, 'msg' => '用户添加失败']);
    }

    /**
     * 删除用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        $user_uid = $request->post('delete_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }

        $judgeroot = Db::table('user')
            ->where('id', $user_id)
            ->where('isdel', 0)
            ->first();

        if ($judgeroot->name == 'root' && $judgeroot->grade == 3) {
            return \json(['code' => -1, 'msg' => '超级管理员账号禁止删除']);
        }

        if ($judgeroot->name == '机器人') {
            return \json(['code' => -1, 'msg' => '机器人账号禁止删除']);
        }
        $info = Db::table('user')
            ->where('id', $user_id)
            ->where('isdel', 0)
            ->exists();

        if ($info) {
            $db = Db::table('followfans')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->select('followid')
                ->get();
            foreach ($db as &$tem) {
                Db::table('user')
                    ->where('id', $tem->followid)
                    ->where('fans', '>', 0)
                    ->lockForUpdate()
                    ->decrement('fans', 1);
            }
            $db = Db::table('followfans')
                ->where('followid', $user_id)
                ->where('isdel', 0)
                ->select('userid')
                ->get();
            foreach ($db as &$tem) {
                Db::table('user')
                    ->where('id', $tem->userid)
                    ->where('follow', '>', 0)
                    ->lockForUpdate()
                    ->decrement('follow', 1);
            }

            Db::table('followfans')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('followfans')
                ->where('followid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('blackip')
                ->where('id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('article')
                ->where('writerid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('codehistory')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('articlecomment')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('codehistory')
                ->where('isdel', 0)
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('codehistory')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('fabulousarticle')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('solveproblem')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('codehistory')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('usernotice')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('groupchat')
                ->where('get_user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            $group_list = Db::table('groupuser')
                ->where('user_id', $user_id)
                ->where('isdel', 0)
                ->get();
            Db::table('groupuser')
                ->where('user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            foreach ($group_list as &$tem) {
                try {
                    Db::table('group')
                        ->where('id', $tem->group_id)
                        ->where('isdel', 0)
                        ->decrement('total', 1);
                    Base::updateGroupDataRedis($tem->group_id);
                } catch (Exception $e) {
                }
            }
            Db::table('privatechat')
                ->where('get_user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('privatechat')
                ->where('post_user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('answer')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Db::table('lovequestion')
                ->where('userid', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);

            Db::table('privateuser')
                ->where('get_user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);

            Db::table('privateuser')
                ->where('post_user_id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);

            $res = Db::table('user')
                ->where('id', $user_id)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            $md5uid = Base::getPathMd5($user_id);
            $gitcode_path = Base::$LTPP_public_static_path . '/gitcode/' . $user_uid;
            if (file_exists($gitcode_path)) {
                Base::deleteAllFile($gitcode_path);
            }

            $image_bk_path = Base::deleteAllFile(Base::$LTPP_public_path . User::$image_bk_root_path . $md5uid);
            $video_bk_path = Base::deleteAllFile(Base::$LTPP_public_path . User::$video_bk_root_path . $md5uid);
            $headimage_path = Base::deleteAllFile(Base::$LTPP_public_path . User::$headimage_root_path . $md5uid);

            if (file_exists($image_bk_path)) {
                Base::deleteAllFile($image_bk_path);
            }
            if (file_exists($video_bk_path)) {
                Base::deleteAllFile($video_bk_path);
            }
            if (file_exists($headimage_path)) {
                Base::deleteAllFile($headimage_path);
            }
            if ($res) {
                Base::updateUserDataRedis($my_aid);
                return json(['code' => 1, 'msg' => '用户删除成功']);
            } {
                Base::updateUserDataRedis($my_aid);
                return json(['code' => -1, 'msg' => '用户删除失败']);
            }
        }
        return json(['code' => -1, 'data' => $info, 'msg' => '用户不存在']);
    }

    /**
     * 获取个人信息
     */
    public function getMyData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $my_data = Base::getUserData($my_aid);
        if (!$my_data || empty($my_data)) {
            return \json(['code' => -1, 'msg' => '用户不存在']);
        }
        $my_data->password = '';
        unset($my_data->grade);
        Base::dataToSafe($my_data);
        return json(['code' => 1, 'data' => $my_data, 'msg' => '加载完成']);
    }
    /**
     * root更新用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function rootUpdateUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $data = $request->post('data');
        if (strlen($data['name']) > 18) {
            return \json(['code' => -1, 'msg' => '用户名不能为空且长度不能大于18']);
        }
        $data['id'] = Base::getIdByUid($data['id']);
        $nullname = $data['name'];
        $temname = preg_replace('/ /', '', $nullname);
        if ($temname == '') {
            return \json(['code' => -1, 'msg' => '用户名不能为空']);
        }
        if (!preg_match("/^[0-9]*$/", $data['grade'])) {
            return \json(['code' => -1, 'msg' => '权限必须是纯数字']);
        }

        $isroot = Base::judgeIsRoot($my_aid);

        if (!$isroot) {
            return \json(['code' => -1, 'msg' => '您没有权限修改！']);
        }

        $db = Db::table('user')
            ->where('id', $data['id'])
            ->where('isdel', 0)
            ->select('name')
            ->first();

        if ($db->name == 'root' && $data['name'] != 'root') {
            return \json(['code' => -1, 'msg' => 'root账户名称禁止修改']);
        }

        if ($db->name == '机器人' && $data['name'] != '机器人') {
            return \json(['code' => -1, 'msg' => '机器人账户名称禁止修改']);
        }

        if ($data['name'] == 'root' && $db->name != 'root') {
            return \json(['code' => -1, 'msg' => '名称禁止改成root']);
        }

        if ($data['name'] == '机器人' && $db->name != '机器人') {
            return \json(['code' => -1, 'msg' => '名称禁止改成机器人']);
        }

        if ($db->name == 'root' && $data['grade'] != '3') {
            return \json(['code' => -1, 'msg' => 'root账户权限禁止修改']);
        }
        if ($data['password'] && $data['password'] != '' && (strlen($data['password']) > 40 || strlen($data['password']) < 6)) {
            return \json(['code' => -1, 'msg' => '密码长度必须大于5且小于40']);
        }

        if (!is_numeric($data['student_number'])) {
            return \json(['code' => -1, 'msg' => '学号必须是纯数字']);
        }

        if (!is_numeric($data['enrollment_year'])) {
            return \json(['code' => -1, 'msg' => '入学年份必须是纯数字']);
        }

        $has = Db::table('user')
            ->where('name', $data['name'])
            ->where('id', '!=', $data['id'])
            ->where('isdel', 0)
            ->exists();

        // 有其他用户是这个名字
        if ($has) {
            return \json(['code' => -1, 'msg' => '该名称已存在']);
        }

        if (strripos($data['email'], '@qq.com') === false) {
            return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
        }
        if (filter_var($data['headimage'], FILTER_VALIDATE_URL) === false) {
            $data['headimage'] = 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $data['email'] . '&spec=640';
        }

        $password = $data['password'];

        if ($data['grade'] > '3' || $data['grade'] < '0') {
            $data['grade'] = 1;
        }

        if ($password != '' && $password) {
            //说明用户密码改了
            $data['password'] = Base::passwordEncryption($password);
        } else {
            unset($data['password']);
        }

        $UpdataBlogandCommentName = Db::table('user')
            ->where('id', $data['id'])
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if (!$UpdataBlogandCommentName) {
            return \json(['code' => -1, 'msg' => '用户不存在']);
        }

        if ($UpdataBlogandCommentName->name != $data['name']) {
            //说明用户名更改
            //更新对应文章的作者名称
            Db::table('article')
                ->where('writerid', $data['id'])
                ->where('isdel', 0)
                ->update(['writer' => $data['name']]);
        }
        if ($UpdataBlogandCommentName->name != $data['name']) {
            // 更新评论用户名
            Db::table('articlecomment')
                ->where('userid', $data['id'])
                ->where('isdel', 0)
                ->update(['username' => $data['name']]);

            Db::table('articlecomment')
                ->where('touserid', $data['id'])
                ->where('isdel', 0)
                ->update(['tousername' => $data['name']]);
        }

        $info = Db::table('user')
            ->where('id', $data['id'])
            ->where('isdel', 0)
            ->update($data);

        Base::updateUserDataRedis($data['id']);

        if ($info) {
            return json(['code' => 1, 'msg' => '用户信息更新成功']);
        }
        return json(['code' => -1, 'msg' => '用户信息未更改，无需更新']);
    }

    /**
     * 用户更新自己信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);

        $data = $request->post('data');

        if (strlen($data['name']) > 18 || strlen($data['name']) < 3) {
            return \json(['code' => -1, 'msg' => '用户名长度必须大于2且小于18']);
        }
        if ($data['mysay'] && strlen($data['mysay']) > 1000) {
            return \json(['code' => -1, 'msg' => '个性签名长度不能大于1000']);
        }
        if ($data['password'] && $data['password'] != '' && (strlen($data['password']) > 40 || strlen($data['password']) < 6)) {
            return \json(['code' => -1, 'msg' => '密码长度必须大于5且小于40']);
        }
        $nullname = $data['name'];
        $temname = preg_replace('/ /', '', $nullname);
        if ($temname == '') {
            return \json(['code' => -1, 'msg' => '用户名不能为空']);
        }

        $db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();

        if ($data['sex'] != '男' && $data['sex'] != '女') {
            return \json(['code' => -1, 'msg' => '请选择给定的性别']);
        }

        if (!is_numeric($data['student_number'])) {
            return \json(['code' => -1, 'msg' => '学号必须是纯数字']);
        }

        if (!is_numeric($data['enrollment_year'])) {
            return \json(['code' => -1, 'msg' => '入学年份必须是纯数字']);
        }

        if ($db->name == 'root' && $data['name'] != 'root') {
            return \json(['code' => -1, 'msg' => 'root账户名称禁止修改']);
        }

        if ($db->name == '机器人' && $data['name'] != '机器人') {
            return \json(['code' => -1, 'msg' => '机器人账户名称禁止修改']);
        }

        if ($data['name'] == 'root' && $db->name != 'root') {
            return \json(['code' => -1, 'msg' => '名称禁止改成root']);
        }

        if ($data['name'] == '机器人' && $db->name != '机器人') {
            return \json(['code' => -1, 'msg' => '名称禁止改成机器人']);
        }

        $has = Db::table('user')
            ->where('name', $data['name'])
            ->where('id', '!=', $my_aid)
            ->where('isdel', 0)
            ->exists();

        // 有其他用户是这个名字
        if ($has) {
            return \json(['code' => -1, 'msg' => '该名称已存在']);
        }

        if (strripos($data['email'], '@qq.com') === false) {
            return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
        }
        if (filter_var($data['headimage'], FILTER_VALIDATE_URL) === false) {
            $data['headimage'] = 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $data['email'] . '&spec=640';
        }
        $password = $data['password'];
        if ($password != '' && $password) {
            //说明用户密码改了
            $data['password'] = Base::passwordEncryption($password);
        } else {
            unset($data['password']);
        }
        $UpdataBlogandCommentName = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if (!$UpdataBlogandCommentName) {
            return \json(['code' => -1, 'msg' => '用户不存在']);
        }

        if ($UpdataBlogandCommentName->name != $data['name']) {
            //说明用户名更改
            //更新对应文章的作者名称
            Db::table('article')
                ->where('writerid', $my_aid)
                ->where('isdel', 0)
                ->update(['writer' => $data['name']]);
        }
        if ($UpdataBlogandCommentName->name != $data['name']) {
            // 更新评论用户名
            Db::table('articlecomment')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->update(['username' => $data['name']]);

            Db::table('articlecomment')
                ->where('touserid', $my_aid)
                ->where('isdel', 0)
                ->update(['tousername' => $data['name']]);
        }
        $info = false;
        if ($password != '' && $password) {
            $info = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'musiclovelistid' => $data['musiclovelistid'],
                    'musicuid' => $data['musicuid'],
                    'mysay' => $data['mysay'],
                    'password' => $data['password'],
                    'sex' => $data['sex'],
                    'student_number' => $data['student_number'],
                    'enrollment_year' => $data['enrollment_year'],
                    'school' => $data['school'],
                    'college' => $data['college'],
                    'subject' => $data['subject'],
                    'class' => $data['class']
                ]);
        } else {
            $info = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'musiclovelistid' => $data['musiclovelistid'],
                    'musicuid' => $data['musicuid'],
                    'mysay' => $data['mysay'],
                    'sex' => $data['sex'],
                    'student_number' => $data['student_number'],
                    'enrollment_year' => $data['enrollment_year'],
                    'school' => $data['school'],
                    'college' => $data['college'],
                    'subject' => $data['subject'],
                    'class' => $data['class']
                ]);
        }
        Base::updateUserDataRedis($my_aid);
        if ($info) {
            return json(['code' => 1, 'msg' => '用户信息更新成功']);
        }
        return json(['code' => -1, 'msg' => '用户信息未更改，无需更新']);
    }

    /**
     * 公开全站用户列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function userList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);

        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('user')
            ->where('isdel', 0)
            ->select(User::$user_db_key)
            ->orderBy('id', 'desc')
            ->select(User::$user_db_key)
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('user')
            ->where('isdel', 0)
            ->count();

        $isroot = Base::judgeIsRoot($my_aid);

        if (!$isroot) {
            foreach ($info as &$tem) {
                $tem->email = '保密信息';
            }
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "共有 $allnum 个用户"]);
    }

    /**
     * 查找用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function findUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return \json(['code' => -1, 'msg' => '查询失败']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('user')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(User::$user_db_key)
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $allnum = Db::table('user')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            foreach ($info as &$tem) {
                unset($tem->grade);
                $tem->email = '保密信息';
            }
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "查找到 $allnum 个用户"]);
    }

    /**
     * 查看一个用户的信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookUserData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!isset($user_uid) || empty($user_uid)) {
            return \json(['code' => -1, 'msg' => '查询失败']);
        }
        $info = Base::getUserData($user_id);
        if (!$info) {
            return \json(['code' => -1, 'msg' => '用户不存在']);
        }
        $isroot = Base::judgeIsRoot($my_aid);
        unset($info->isdel);
        unset($info->bkimage);
        unset($info->bkvideo);
        unset($info->isusemusic);
        if (!$isroot) {
            unset($info->musiclovelistid);
            unset($info->musicuid);
            unset($info->password);
            $info->password = '******';
            $info->grade = '******';
            $info->email = '******';
            $info->money = '******';
        } else {
            $info->password = '';
        }
        Base::dataToSafe($info);
        if ($info) {
            return json(['code' => 1, 'data' => $info, 'msg' => '用户信息加载成功']);
        }
        return json(['code' => -1, 'data' => $info, 'msg' => '用户信息加载失败']);
    }

    /**
     * 加载个人信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadSelfData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $dbuser = Base::getUserData($my_aid);
        if (!$dbuser) {
            return \json(['code' => -1, 'msg' => '用户不存在', 'data' => []]);
        }
        unset($dbuser->id);
        $dbuser->password = '';
        $dbuser->money = rtrim(rtrim($dbuser->money, '0'), '.');
        Base::dataToSafe($dbuser);
        if ($dbuser && !empty($dbuser)) {
            return \json(['code' => 1, 'data' => $dbuser, 'msg' => "加载个人信息成功"]);
        }
        return \json(['code' => -1, 'msg' => '用户错误', 'data' => []]);
    }

    /**
     * 查看用户AC的题目列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookUserAcList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        $aclist = Db::table('solveproblem')
            ->where('userid', $user_id)
            ->where('isdel', 0)
            ->select('problemid')
            ->distinct()
            ->get();
        $res = [];
        $isroot = Base::judgeIsRoot($my_aid);
        $set = [];
        foreach ($aclist as &$tem) {
            $tem_problem_id = $tem->problemid;
            if (isset($set[$tem_problem_id])) {
                continue;
            }
            $set[$tem_problem_id] = 1;
            $temdb = null;
            if ($isroot) {
                $temdb = Db::table('oj')
                    ->where('id', $tem->problemid)
                    ->where('isdel', 0)
                    ->select('problemName')
                    ->first();
            } else {
                $temdb = Db::table('oj')
                    ->where('id', $tem->problemid)
                    ->where('public', 1)
                    ->where('isdel', 0)
                    ->select('problemName')
                    ->first();
            }
            $obj = new stdClass;
            if ($temdb) {
                $obj->id = $tem->problemid;
                $obj->problemName = $temdb->problemName;
                $res[] = $obj;
            }
        }
        Base::dataToSafe($res);
        return \json(['code' => 1, 'data' => $res]);
    }
};
