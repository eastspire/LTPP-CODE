<?php

namespace app\controller;

use Exception;
use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

class Setting extends Image
{
    static $blackip_key = [
        'id',
        'user_id',
        'ip'
    ];
    /**
     * 批量生成用户
     * @param Request $request 请求
     * @return string $res json
     */
    public function largeAddUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $userbeginnum = $request->post('userbeginnum');
        $userendnum = $request->post('userendnum');
        if (!is_numeric($userbeginnum) || !is_numeric($userendnum)) {
            return json(['code' => -1, 'msg' => '必须是数字']);
        }
        if (strlen($userbeginnum) > 20 || strlen($userendnum) > 20) {
            return json(['code' => -1, 'msg' => '数字长度能不大于20']);
        }
        if ($userbeginnum > $userendnum) {
            return json(['code' => -1, 'msg' => '起始数字不能小于结束数字']);
        }
        if ($userbeginnum < 0) {
            return json(['code' => -1, 'msg' => '起始数字不能小于0']);
        }
        if ($userendnum - $userbeginnum + 1 > 1000) {
            return json(['code' => -1, 'msg' => '最多一次性导入不能超过1000个用户']);
        }

        $email = Base::getRobotEmail();

        $one_user_data = [
            'name' => '',
            'password' => '',
            'sex' => '男',
            'registertime' => date('Y-m-d H:i:s', time()),
            'headimage' => 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $email . '&spec=640',
            'fans' => 0,
            'follow' => 0,
            'online' => 0,
            'grade' => 1,
            'email' => $email,
            'isdel' => 0
        ];
        $successnum = 0;
        $all_user_data = [];
        for ($i = $userbeginnum; $i <= $userendnum; $i = Base::bigIntAdd($i, '1')) {
            $ishas = Db::table('user')->where('name', $i)->exists();
            if ($ishas) {
                continue;
            }
            $one_user_data["name"] = $i;
            $one_user_data["password"] = $i;

            $all_user_data[] = $one_user_data;
            ++$successnum;
        }

        $save = Db::table('user')->insert($all_user_data);
        if ($save) {
            return json([
                'code' => 1,
                'msg' => '需要导入：' . ($userendnum - $userbeginnum + 1) . '个用户，成功导入：' . $successnum . '个用户，成功率：' . round($successnum / ($userendnum - $userbeginnum + 1), 2) * 100 . '%'
            ]);
        }
        return json([
            'code' => -1,
            'msg' => '插入失败'
        ]);
    }

    /**
     * 安装题库测试样例
     * @param Request $request 请求
     * @return string $res json
     */
    public function installTest()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $res = '';
        $out = array();
        exec('cp -r -f ' . Base::$LTPP_path . 'InstallMust/testdata /home/LTPP/ 2>&1', $out);
        if (!empty($out)) {
            foreach ($out as $tem) {
                $res .= $tem . "\n";
            }
            return json(['code' => -1, 'msg' => '安装出错' . $res]);
        }
        Base::chmodFile(Base::$LTPP_path . 'InstallMust', 0555);
        return json(['code' => 1, 'msg' => '测试样例安装成功']);
    }

    /**
     * 安装判题机
     * @param Request $request 请求
     * @return string $res json
     */
    public function installJudgeSever()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $res = '';
        $out = array();
        Base::deleteAllFile(Base::$judge_install_path);
        Base::judgeCreatPath(Base::$judge_install_path);
        exec('cp -f /home/LTPP/InstallMust/JudgeServer/judge ' . Base::$judge_install_path . ' 2>&1', $out);
        Base::chmodFile('/JudgeServer', 0555);
        if (!empty($out)) {
            foreach ($out as $tem) {
                $res .= $tem . "\n";
            }
            Robot::sendChatToOneUserMsg($my_aid, '判题机安装出错' . $res);
            return json(['code' => -1, 'msg' => '安装出错' . $res]);
        }
        Robot::sendChatToOneUserMsg($my_aid, '判题机安装完成');
        // 沙箱
        try {
            Base::installSandboxEnv();
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg($my_aid, '沙箱环境安装出错' . $e->getMessage());
            return json(['code' => -1, 'msg' => '沙箱环境安装出错' . $e->getMessage()]);
        }
        Robot::sendChatToOneUserMsg($my_aid, '沙箱安装完成');
        return json(['code' => 1, 'msg' => '判题机安装成功']);
    }

    /**
     * 更新数据库博客图片链接
     * @param Request $request 请求
     */
    public function articleImage($id)
    {
        $isroot = Base::judgeIsRoot($id);
        if (!$isroot) {
            return;
        }
        $err = Db::table('article')
            ->select('id')
            ->get();
        foreach ($err as &$tem) {
            $resurl = Image::randimage();
            Db::table('article')
                ->where('id', $tem->id)
                ->update(['Image' => $resurl]);
        }
    }

    /**
     * 本地图片重命名
     * @param Request $request 请求
     */
    protected function renameImage($id)
    {
        $isroot = Base::judgeIsRoot($id);
        if (!$isroot) {
            return;
        }
        $testpath = Base::$LTPP_public_path . 'static/dbimage/';
        foreach (Cloudfile::$photo as &$t_img) {
            $file = glob($testpath . '*.' . $t_img);
            foreach ($file as &$tem) {
                do {
                    $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $t_img;
                } while (file_exists($testpath . $newName));
                rename($tem, $testpath . $newName);
            }
        }
    }

    /**
     * 将本地图片导入数据库
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateImage(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $testpath = Base::$LTPP_public_path . 'static/dbimage/';
        // 清空数据库
        Db::table('image')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        // 重命名
        $this->renameImage($my_aid);
        Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
        $data = [];
        foreach (Cloudfile::$photo as &$t_img) {
            $file = glob($testpath . '*.' . $t_img);
            foreach ($file as &$tem) {
                $loc = strpos($tem, 'dbimage/') + 8;
                $ts = '';
                for ($i = $loc; $i < strlen($tem); ++$i) {
                    $ts .= $tem[$i];
                }
                $data[] = [
                    'url' => Base::$GLOBlinuxurl . '/static/dbimage/' . $ts
                ];
            }
        }
        Db::table('image')
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        Db::table('image')->insert($data);
        $this->articleImage($my_aid);
        return json(['code' => 1, 'msg' => '网站图片更新完成']);
    }

    /**
     * 清空redis缓存
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteRedis(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        for ($i = 0; $i <= 36; ++$i) {
            $redis = Redis::connection('db' . $i);
            $redis->flushdb();
        }
        return json(['code' => 1, 'msg' => '缓存清理成功！']);
    }

    /**
     * 清空除了用户单点登录之外的redis缓存
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteSomeRedis(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        for ($i = 0; $i <= 36; ++$i) {
            if ($i == 14) {
                // 用户单点登录缓存
                continue;
            }
            $redis = Redis::connection('db' . $i);
            $redis->flushdb();
        }
        return json(['code' => 1, 'msg' => '缓存清理成功！']);
    }

    /**
     * 清空限速用户和IP的缓存
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteRedisIdIp(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $redis = Redis::connection('db1');
        $redis->flushdb();
        return json(['code' => 1, 'msg' => '限速用户和IP的缓存清空成功！']);
    }

    /**
     * 获取服务器协议+ip
     * @param Request $request 请求
     * @return string $res json
     */
    public function getLinuxurl(Request $request)
    {
        $redis5 = Redis::connection('db5');
        if ($redis5->get('GLOBlinuxurl')) {
            return json(['code' => 1, 'data' => $redis5->get('GLOBlinuxurl'), 'msg' => '']);
        } else {
            $db = Db::table('setting')
                ->where('isdel', 0)
                ->select('GLOBlinuxurl')
                ->orderBy('id', 'desc')
                ->first();
            if ($db) {
                $db = $db->GLOBlinuxurl;
                $redis5->set('GLOBlinuxurl', $db);
                return json(['code' => 1, 'data' => $db, 'msg' => '']);
            }
        }
        return json(['code' => 1, 'data' => '', 'msg' => '服务器地址获取失败！']);
    }

    /**
     * 后端音乐程序
     * @param Request $request 请求
     * @return string $res json
     */
    public function runMusic(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $port = '3000';
        // 获取音乐后端端口
        $musicbkurl = Base::getSettingKeyData('musicbkurl');
        $port = Base::getPort($musicbkurl);
        if (!$port) {
            $port = '3000';
        }
        $out = [];
        exec('pkill -9 node 2>&1', $out);
        $out = [];
        exec('PORT=' . $port . ' node /home/LTPP/Music/app.js > /home/LTPP/Music/music.log 2>&1 &', $out);
        $out = [];
        return json(['code' => 1, 'msg' => '重启成功！']);
    }

    /**
     * 更新设置
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateSetting(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $data = $request->post('data');
        $db = Db::table('setting')
            ->where('isdel', 0)
            ->select('id')
            ->orderBy('id', 'desc')
            ->first();
        if ($db) {
            $redis5 = Redis::connection('db5');

            if ($data['default_contest_content'] != $redis5->get('default_contest_content')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_content' => $data['default_contest_content']]);
                $redis5->del('default_contest_content');
                $redis5->set('default_contest_content', $data['default_contest_content']);
            }

            if ($data['default_contest_duration'] != $redis5->get('default_contest_duration')) {
                if (!is_numeric($data['default_contest_duration'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_duration' => $data['default_contest_duration']]);
                $redis5->del('default_contest_duration');
                $redis5->set('default_contest_duration', $data['default_contest_duration']);
            }

            if ($data['default_contest_begin_time'] != $redis5->get('default_contest_begin_time')) {
                if (!is_numeric($data['default_contest_begin_time'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_begin_time' => $data['default_contest_begin_time']]);
                $redis5->del('default_contest_begin_time');
                $redis5->set('default_contest_begin_time', $data['default_contest_begin_time']);
            }

            if ($data['default_contest_problem_num'] != $redis5->get('default_contest_problem_num')) {
                if (!is_numeric($data['default_contest_problem_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_problem_num' => $data['default_contest_problem_num']]);
                $redis5->del('default_contest_problem_num');
                $redis5->set('default_contest_problem_num', $data['default_contest_problem_num']);
            }

            if ($data['default_contest_min_people_num'] != $redis5->get('default_contest_min_people_num')) {
                if (!is_numeric($data['default_contest_min_people_num']) || !is_numeric($data['default_contest_max_people_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_min_people_num'] > $data['default_contest_max_people_num']) {
                    return json(['code' => -1, 'msg' => '数据错误！竞赛默认最小参赛人数不能大于竞赛默认最大参赛人数！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_min_people_num' => $data['default_contest_min_people_num']]);
                $redis5->del('default_contest_min_people_num');
                $redis5->set('default_contest_min_people_num', $data['default_contest_min_people_num']);
            }

            if ($data['default_contest_max_people_num'] != $redis5->get('default_contest_max_people_num')) {
                if (!is_numeric($data['default_contest_min_people_num']) || !is_numeric($data['default_contest_max_people_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_min_people_num'] > $data['default_contest_max_people_num']) {
                    return json(['code' => -1, 'msg' => '数据错误！竞赛默认最小参赛人数不能大于竞赛默认最大参赛人数！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['default_contest_max_people_num' => $data['default_contest_max_people_num']]);
                $redis5->del('default_contest_max_people_num');
                $redis5->set('default_contest_max_people_num', $data['default_contest_max_people_num']);
            }

            if ($data['usercloudfilememory'] != $redis5->get('usercloudfilememory')) {
                if (!is_numeric($data['usercloudfilememory'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['usercloudfilememory' => $data['usercloudfilememory']]);
                $redis5->del('usercloudfilememory');
                $redis5->set('usercloudfilememory', $data['usercloudfilememory']);
            }

            $data['offline'] = (int) $data['offline'];
            if ($data['offline'] != (int) $redis5->get('offline')) {
                if ($data['offline'] != 0 && $data['offline'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['offline' => $data['offline']]);
                $redis5->del('offline');
                $redis5->set('offline', $data['offline']);
            }

            $data['useqqmail'] = (int) $data['useqqmail'];
            if ($data['useqqmail'] != (int) $redis5->get('useqqmail')) {
                if ($data['useqqmail'] != 0 && $data['useqqmail'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['useqqmail' => $data['useqqmail']]);
                $redis5->del('useqqmail');
                $redis5->set('useqqmail', $data['useqqmail']);
            }

            if ($data['chatgpt_api_url'] != $redis5->get('chatgpt_api_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['chatgpt_api_url' => $data['chatgpt_api_url']]);
                $redis5->del(Base::$chat_gpt_api_url_key);
                $redis5->set(Base::$chat_gpt_api_url_key, $data['chatgpt_api_url']);
            }

            if ($data['chatgpt_keys'] != $redis5->get('chatgpt_keys')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['chatgpt_keys' => $data['chatgpt_keys']]);
                $redis5->del(Base::$chatgpt_keys_key);
                $redis5->set(Base::$chatgpt_keys_key, $data['chatgpt_keys']);
            }

            if ($data['idemaxtime'] != $redis5->get('idemaxtime')) {
                if (!is_numeric($data['idemaxtime'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['idemaxtime' => $data['idemaxtime']]);
                $redis5->del('idemaxtime');
                $redis5->set('idemaxtime', $data['idemaxtime']);
            }

            if ($data['idemaxmemory'] != $redis5->get('idemaxmemory')) {
                if (!is_numeric($data['idemaxmemory'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['idemaxmemory' => $data['idemaxmemory']]);
                $redis5->del('idemaxmemory');
                $redis5->set('idemaxmemory', $data['idemaxmemory']);
            }

            if ($data['GLOBfronturl'] != $redis5->get('GLOBfronturl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['GLOBfronturl' => $data['GLOBfronturl']]);
                $redis5->del('GLOBfronturl');
                $redis5->set('GLOBfronturl', $data['GLOBfronturl']);
            }

            if ($data['ssh_back_url'] != $redis5->get('ssh_back_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['ssh_back_url' => $data['ssh_back_url']]);
                $redis5->del('ssh_back_url');
                $redis5->set('ssh_back_url', $data['ssh_back_url']);
            }

            if ($data['mysmtpurl'] != $redis5->get('mysmtpurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['mysmtpurl' => $data['mysmtpurl']]);
                $redis5->del('mysmtpurl');
                $redis5->set('mysmtpurl', $data['mysmtpurl']);
            }

            if ($data['mysmtpname'] != $redis5->get('mysmtpname')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['mysmtpname' => $data['mysmtpname']]);
                $redis5->del('mysmtpname');
                $redis5->set('mysmtpname', $data['mysmtpname']);
            }

            if ($data['mysmtppassword'] != $redis5->get('mysmtppassword')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['mysmtppassword' => $data['mysmtppassword']]);
                $redis5->del('mysmtppassword');
                $redis5->set('mysmtppassword', $data['mysmtppassword']);
            }

            if ($data['musicbkurl'] != $redis5->get('musicbkurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['musicbkurl' => $data['musicbkurl']]);
                $redis5->del('musicbkurl');
                $redis5->set('musicbkurl', $data['musicbkurl']);
                // 后端音乐地址更改，后端音乐服务重启
                $this->runMusic($request);
            }
            if ($data['classurl'] != $redis5->get('classurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['classurl' => $data['classurl']]);
                $redis5->del('classurl');
                $redis5->set('classurl', $data['classurl']);
            }
            if ($data['GLOBlinuxurl'] != $redis5->get('GLOBlinuxurl')) {
                // 更改服务器ip自动更新socket地址
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update([
                        'GLOBlinuxurl' => $data['GLOBlinuxurl'],
                    ]);
                $redis5->del('GLOBlinuxurl');
                $redis5->set('GLOBlinuxurl', $data['GLOBlinuxurl']);
            }
            if ($data['socketurl'] != $redis5->get('socketurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update([
                        'socketurl' => $data['socketurl']
                    ]);
                $redis5->del('socketurl');
                $redis5->set('socketurl', $data['socketurl']);
            }
            if ($data['GLOBiplimit'] != $redis5->get('GLOBiplimit')) {
                if (!is_numeric($data['GLOBiplimit'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['GLOBiplimit' => $data['GLOBiplimit']]);
                $redis5->del('GLOBiplimit');
                $redis5->set('GLOBiplimit', $data['GLOBiplimit']);
            }
            if ($data['GLOBiplimitTime'] != $redis5->get('GLOBiplimitTime')) {
                if (!is_numeric($data['GLOBiplimitTime'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['GLOBiplimitTime' => $data['GLOBiplimitTime']]);
                $redis5->del('GLOBiplimitTime');
                $redis5->set('GLOBiplimitTime', $data['GLOBiplimitTime']);
            }
            if ($data['GLOBipblack'] != $redis5->get('GLOBipblack')) {
                if (!is_numeric($data['GLOBipblack'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['GLOBipblack' => $data['GLOBipblack']]);
                $redis5->del('GLOBipblack');
                $redis5->set('GLOBipblack', $data['GLOBipblack']);
            }
            if ($data['canregister'] != $redis5->get('canregister')) {
                if ($data['canregister'] != 0 && $data['canregister'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['canregister' => $data['canregister']]);
                $redis5->del('canregister');
                $redis5->set('canregister', $data['canregister']);
            }
            if ($data['canlogin'] != $redis5->get('canlogin')) {
                if ($data['canlogin'] != 0 && $data['canlogin'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['canlogin' => $data['canlogin']]);
                $redis5->del('canlogin');
                $redis5->set('canlogin', $data['canlogin']);
            }
            if ($data['useemail'] != $redis5->get('useemail')) {
                if ($data['useemail'] != 0 && $data['useemail'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['useemail' => $data['useemail']]);
                $redis5->del('useemail');
                $redis5->set('useemail', $data['useemail']);
            }
            if ($data['smtp'] != $redis5->get('smtp')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['smtp' => $data['smtp']]);
                $redis5->del('smtp');
                $redis5->set('smtp', $data['smtp']);
            }
            if ($data['smtpkey'] != $redis5->get('smtpkey')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->lockForUpdate()
                    ->update(['smtpkey' => $data['smtpkey']]);
                $redis5->del('smtpkey');
                $redis5->set('smtpkey', $data['smtpkey']);
            }
            return json(['code' => 1, 'msg' => '设置更新成功！']);
        }
        return json(['code' => -1, 'msg' => '设置更新失败！']);
    }

    /**
     * 获取设置列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getSettingList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $ishas = Db::table('setting')
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->first();
        if ($ishas) {
            return json(['code' => 1, 'msg' => '获取设置成功', 'data' => $ishas]);
        }
        return json(['code' => -1, 'msg' => '请刷新后重试']);
    }

    /**
     * 加载ip黑名单
     * @param Request $request 请求
     * @return string $res json
     */
    public function getIpBlackList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        $db = Db::table('blackip')
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->select(Setting::$blackip_key)
            ->paginate($limit, '*', 'page', $page)
            ->items();
        foreach ($db as &$tem) {
            $temdb = Base::getUserData($tem->user_id);
            if ($temdb) {
                $tem->username = $temdb->name;
            } else {
                $tem->username = '';
            }
        }
        $allnum = Db::table('blackip')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'allnum' => $allnum, 'msg' => 'ip黑名单获取成功']);
    }

    /**
     * 添加ip和用户黑名单
     * @param Request $request 请求
     * @return string $res json
     */
    public function addBlackIpUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $ip = $request->post('ip');
        $user_id = $request->post('user_id');
        $redis0 = Redis::connection('db0');
        $redis0->set('BlackIP' . $ip, 1);
        if ($user_id && $user_id != '') {
            $redis0->set('BlackID' . $user_id, 1);
        }
        if ($ip) {
            $redis0->set('BlackIP' . $ip, 1);
        }
        if (!$user_id) {
            $user_id = 0;
        }
        if (!is_numeric($user_id)) {
            return json(['code' => -1, 'msg' => '用户ID应为数字']);
        }
        if (!$ip) {
            $ip = '0.0.0.0';
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return json(['code' => -1, 'msg' => 'IP不合法']);
        }
        $db = Db::table('blackip')
            ->where('ip', $ip)
            ->where('user_id', $user_id)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => -1, 'msg' => '该ip和用户已经拉黑']);
        }
        Base::insertToDb('blackip', [
            'ip' => $ip,
            'user_id' => $user_id
        ]);
        return json(['code' => 1, 'msg' => '添加成功']);
    }

    /**
     * 移除黑名单
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteBlack(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $delete_uid = $request->post('delete_id');
        $delete_id = Base::getIdByUid($delete_uid);
        $db = Db::table('blackip')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->select(Setting::$blackip_key)
            ->first();
        Db::table('blackip')
            ->where('id', $delete_id)
            ->where('isdel', 0)
            ->update(['isdel' => 1]);
        $redis0 = Redis::connection('db0');
        $blackip = $db->ip;
        $redis0->del('BlackIP' . $blackip);
        $blackid = $db->user_id;
        $redis0->del('BlackID' . $blackid);
        return json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 搜索ip
     * @param Request $request 请求
     * @return string $res json
     */
    public function searchIp(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $page = $request->post('page');
        $limit = $request->post('limit');
        $ip = $request->post('ip');
        $db = Db::table('blackip')
            ->orderBy('id', 'desc')
            ->where('isdel', 0)
            ->where('ip', 'like', '%' . $ip . '%')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        foreach ($db as &$tem) {
            $temdb = Db::table('user')
                ->where('id', $tem->user_id)
                ->where('isdel', 0)
                ->select('name')
                ->first();
            if ($temdb) {
                $tem->username = $temdb->name;
            } else {
                $tem->username = '未知用户名';
            }
        }
        $allnum = Db::table('blackip')
            ->where('ip', $ip)
            ->where('isdel', 0)
            ->count();
        return json(['code' => 1, 'data' => $db, 'allnum' => $allnum, 'msg' => '搜索到' . $allnum . '条结果']);
    }

    /**
     * 显示服务器信息
     * @param Request $request 请求
     * @return string $res json
     */
    public function linuxData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $do = $request->post('dowhat');
        $res = array();
        $out = [];
        if ($do == 'lookbase') {
            $out = [];
            $name = '服务器信息：';
            $res[] = array($name);
            exec('lsb_release -a 2>&1', $out);
            $res[] = $out;
            $out = [];
            exec('uname -a 2>&1', $out);
            $res[] = $out;
            $out = [];
            $name = 'CPU型号：';
            $res[] = array($name);
            exec("grep 'model name' /proc/cpuinfo |uniq", $out);
            $res[] = $out;
            $out = [];
            $name = 'CPU物理个数 ：';
            $res[] = array($name);
            exec("grep 'physical id' /proc/cpuinfo |sort |uniq |wc -l", $out);
            $res[] = $out;
            $out = [];
            $name = 'CPU核心数 ：';
            $res[] = array($name);
            exec("grep 'cpu cores' /proc/cpuinfo |uniq", $out);
            $res[] = $out;
            $name = 'CPU使用情况：';
            $res[] = array($name);
            exec("mpstat", $out);
            $res[] = $out;
            $name = '内存信息：';
            $res[] = array($name);
            $out = [];
            exec('free -h 2>&1', $out);
            $res[] = $out;
            $out = [];
            exec('free -m 2>&1', $out);
            $res[] = $out;
            $out = [];
            exec('vmstat 2>&1', $out);
            $res[] = $out;
            $out = [];
            exec('cat /proc/meminfo 2>&1', $out);
            $res[] = $out;
            $out = [];
            $name = '磁盘信息：';
            $res[] = array($name);
            $out = [];
            exec('df -h 2>&1', $out);
            $res[] = $out;
            $out = [];
            $name = '当前进程：';
            $res[] = array($name);
            $out = [];
            exec('ps -ef 2>&1', $out);
            $res[] = $out;
            $out = [];
        }
        return json(['data' => $res]);
    }
}
