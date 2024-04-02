<?php

namespace app\controller;

use Exception;
use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use support\Redis;

class Setting extends Image
{
    static $setting_db_key = [
        'canregister',
        'canlogin',
        'offline',
        'useqqmail',
        'useemail',
        'chatgpt_api_url',
        'chatgpt_keys',
        'classurl',
        'smtp',
        'smtpkey',
        'mysmtpurl',
        'mysmtpname',
        'mysmtppassword',
        'GLOBlinuxurl',
        'GLOBiplimit',
        'GLOBiplimitTime',
        'GLOBipblack',
        'musicbkurl',
        'socketurl',
        'idemaxtime',
        'idemaxmemory',
        'usercloudfilememory',
        'GLOBfronturl',
        'ssh_back_url',
        'default_contest_content',
        'default_contest_duration',
        'default_contest_begin_time',
        'default_contest_problem_num',
        'default_contest_min_people_num',
        'default_contest_max_people_num',
        'douyin_listcollection_url',
        'douyin_cookie',
        'douyin_save_limit',
        'douyin_save_file',
        'douyin_noupdate_limit_seconds',
        'default_contest_submit_sleep_time',
        'cloud_file_readme_txt'
    ];

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
            'headimage' => Image::randImage(),
            'fans' => 0,
            'follow' => 0,
            'grade' => 1,
            'email' => $email,
            'isdel' => 0
        ];
        $successnum = 0;
        $all_user_data = [];
        $image_list = Image::getImageList();
        $image_length = sizeof($image_list);
        for ($i = $userbeginnum; $i <= $userendnum; $i = Base::bigIntAdd($i, '1')) {
            $ishas = Db::table('user')->where('name', $i)->exists();
            if ($ishas) {
                continue;
            }
            $one_user_data["name"] = $i;
            $one_user_data["password"] = $i;
            $one_user_data['headimage'] = $image_list[rand(0, $image_length - 1)];
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
        Base::runExec('cp -f /home/LTPP/InstallMust/JudgeServer/judge ' . Base::$judge_install_path, $out);
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
     */
    private function articleImage()
    {
        $image_db = Db::table('image')
            ->where('isdel', 0)
            ->limit(1000)
            ->pluck('url')
            ->toArray();
        $len = sizeof($image_db);
        for ($i = 0; $i < $len; ++$i) {
            Db::table('article')
                ->whereRaw('id % ? = ?', [$len, $i])
                ->update([
                    'image' => $image_db[$i]
                ]);
        }
        // 删除缓存
        $redis25 = Redis::connection('db25');
        $redis25->flushdb();
    }

    /**
     * 将本地图片导入数据库
     * @param Request $request 请求
     * @return string $res json
     */
    public function updateImage(Request $request)
    {
        try {
            $my_uid = JwtToken::getCurrentId();
            $my_aid = Base::getIdByUid($my_uid);
            $isroot = Base::judgeIsRoot($my_aid);
            if (!$isroot) {
                return json(['code' => -1, 'msg' => '无权限']);
            }
            $testpath = Base::$LTPP_public_path . Base::$LTPP_public_static_path . '/dbimage/';
            // 清空数据库
            Db::table('image')
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            $data = [];
            $has_image = false;
            foreach (Cloudfile::$photo as &$t_img) {
                $file = glob($testpath . '*.' . $t_img);
                foreach ($file as &$tem) {
                    $path = Base::creatFilePath($t_img);
                    $id = Base::insertToDb('file_data', [
                        'data' => file_get_contents(realpath($tem)),
                    ]);
                    if (!$id) {
                        continue;
                    }
                    Base::insertToDb('file_path', [
                        'path' => $path,
                        'file_id' => $id,
                        'userid' => $my_aid,
                        'time' => date('Y-m-d H:i:s', time())
                    ]);
                    $data[] = [
                        'url' => Base::$GLOBlinuxurl . $path
                    ];
                    if (sizeof($data) % 888 == 0) {
                        Db::table('image')->insert($data);
                        $data = [];
                    }
                    $has_image = true;
                }
                if (sizeof($data) >= 0) {
                    $has_image = true;
                    Db::table('image')->insert($data);
                    $data = [];
                }
            }
            // 有图片再更新
            if ($has_image) {
                $this->articleImage();
                return json(['code' => 1, 'msg' => '网站图片更新完成']);
            }
        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => '图片更新失败']);
        }
        return json(['code' => -1, 'msg' => '暂无图片已跳过更新']);
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
        for ($i = 0; $i < Base::$redis_db_num; ++$i) {
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
        for ($i = 0; $i < Base::$redis_db_num; ++$i) {
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
        Base::runExec('pkill -f node');
        Base::runExec('PORT=' . Base::$music_port . ' node /home/LTPP/Music/app.js > /home/LTPP/Music/music.log 2>&1 &');
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

            $old_email = $redis5->get('robot_email');
            if ($data['robot_email'] != $old_email) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['robot_email' => $data['robot_email']]);
                $redis5->del('robot_email');
                $redis5->set('robot_email', $data['robot_email']);
                Base::updateRobotUsersEmail($old_email);
            }

            if ($data['compiler_time_limit'] != $redis5->get('compiler_time_limit')) {
                if (!is_numeric($data['compiler_time_limit'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['compiler_time_limit'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['compiler_time_limit' => $data['compiler_time_limit']]);
                $redis5->del('compiler_time_limit');
                $redis5->set('compiler_time_limit', $data['compiler_time_limit']);
            }

            if ($data['code_check_similarity_one_page_limit'] != $redis5->get('code_check_similarity_one_page_limit')) {
                if (!is_numeric($data['code_check_similarity_one_page_limit'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['code_check_similarity_one_page_limit'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['code_check_similarity_one_page_limit' => $data['code_check_similarity_one_page_limit']]);
                $redis5->del('code_check_similarity_one_page_limit');
                $redis5->set('code_check_similarity_one_page_limit', $data['code_check_similarity_one_page_limit']);
            }

            if ($data['ltpp_win_download_url'] != $redis5->get('ltpp_win_download_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['ltpp_win_download_url' => $data['ltpp_win_download_url']]);
                $redis5->del('ltpp_win_download_url');
                $redis5->set('ltpp_win_download_url', $data['ltpp_win_download_url']);
            }

            if ($data['ltpp_mac_download_url'] != $redis5->get('ltpp_mac_download_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['ltpp_mac_download_url' => $data['ltpp_mac_download_url']]);
                $redis5->del('ltpp_mac_download_url');
                $redis5->set('ltpp_mac_download_url', $data['ltpp_mac_download_url']);
            }

            if ($data['ltpp_apk_download_url'] != $redis5->get('ltpp_apk_download_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['ltpp_apk_download_url' => $data['ltpp_apk_download_url']]);
                $redis5->del('ltpp_apk_download_url');
                $redis5->set('ltpp_apk_download_url', $data['ltpp_apk_download_url']);
            }

            if ($data['cloud_file_readme_txt_file_name'] != $redis5->get('cloud_file_readme_txt_file_name')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['cloud_file_readme_txt_file_name' => $data['cloud_file_readme_txt_file_name']]);
                $redis5->del('cloud_file_readme_txt_file_name');
                $redis5->set('cloud_file_readme_txt_file_name', $data['cloud_file_readme_txt_file_name']);
            }

            if ($data['cloud_file_readme_txt'] != $redis5->get('cloud_file_readme_txt')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['cloud_file_readme_txt' => $data['cloud_file_readme_txt']]);
                $redis5->del('cloud_file_readme_txt');
                $redis5->set('cloud_file_readme_txt', $data['cloud_file_readme_txt']);
            }

            if ($data['douyin_listcollection_url'] != $redis5->get('douyin_listcollection_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['douyin_listcollection_url' => $data['douyin_listcollection_url']]);
                $redis5->del('douyin_listcollection_url');
                $redis5->set('douyin_listcollection_url', $data['douyin_listcollection_url']);
            }

            if ($data['douyin_cookie'] != $redis5->get('douyin_cookie')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['douyin_cookie' => $data['douyin_cookie']]);
                $redis5->del('douyin_cookie');
                $redis5->set('douyin_cookie', $data['douyin_cookie']);
            }

            if ($data['douyin_save_limit'] != $redis5->get('douyin_save_limit')) {
                if (!is_numeric($data['douyin_save_limit'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['douyin_save_limit'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['douyin_save_limit' => $data['douyin_save_limit']]);
                $redis5->del('douyin_save_limit');
                $redis5->set('douyin_save_limit', $data['douyin_save_limit']);
            }

            if ($data['douyin_noupdate_limit_seconds'] != $redis5->get('douyin_noupdate_limit_seconds')) {
                if (!is_numeric($data['douyin_noupdate_limit_seconds'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['douyin_noupdate_limit_seconds'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['douyin_noupdate_limit_seconds' => $data['douyin_noupdate_limit_seconds']]);
                $redis5->del('douyin_noupdate_limit_seconds');
                $redis5->set('douyin_noupdate_limit_seconds', $data['douyin_noupdate_limit_seconds']);
            }

            if ($data['douyin_save_file'] != $redis5->get('douyin_save_file')) {
                if ($data['douyin_save_file'] != 0 && $data['douyin_save_file'] != 1) {
                    return json(['code' => -1, 'msg' => '选项只能选择是或否对应数值为1或0']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['douyin_save_file' => $data['douyin_save_file']]);
                $redis5->del('douyin_save_file');
                $redis5->set('douyin_save_file', $data['douyin_save_file']);
            }

            if ($data['default_contest_content'] != $redis5->get('default_contest_content')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_content' => $data['default_contest_content']]);
                $redis5->del('default_contest_content');
                $redis5->set('default_contest_content', $data['default_contest_content']);
            }

            if ($data['default_contest_duration'] != $redis5->get('default_contest_duration')) {
                if (!is_numeric($data['default_contest_duration'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_duration'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_duration' => $data['default_contest_duration']]);
                $redis5->del('default_contest_duration');
                $redis5->set('default_contest_duration', $data['default_contest_duration']);
            }

            if ($data['default_contest_submit_sleep_time'] != $redis5->get('default_contest_submit_sleep_time')) {
                if (!is_numeric($data['default_contest_submit_sleep_time'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_submit_sleep_time'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_submit_sleep_time' => $data['default_contest_submit_sleep_time']]);
                $redis5->del('default_contest_submit_sleep_time');
                $redis5->set('default_contest_submit_sleep_time', $data['default_contest_submit_sleep_time']);
            }

            if ($data['default_contest_begin_time'] != $redis5->get('default_contest_begin_time')) {
                if (!is_numeric($data['default_contest_begin_time'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_begin_time'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_begin_time' => $data['default_contest_begin_time']]);
                $redis5->del('default_contest_begin_time');
                $redis5->set('default_contest_begin_time', $data['default_contest_begin_time']);
            }

            if ($data['default_contest_problem_num'] != $redis5->get('default_contest_problem_num')) {
                if (!is_numeric($data['default_contest_problem_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_problem_num'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_problem_num' => $data['default_contest_problem_num']]);
                $redis5->del('default_contest_problem_num');
                $redis5->set('default_contest_problem_num', $data['default_contest_problem_num']);
            }

            if ($data['default_contest_min_people_num'] != $redis5->get('default_contest_min_people_num')) {
                if (!is_numeric($data['default_contest_min_people_num']) || !is_numeric($data['default_contest_max_people_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_min_people_num'] < 0 || $data['default_contest_max_people_num'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                if ($data['default_contest_min_people_num'] > $data['default_contest_max_people_num']) {
                    return json(['code' => -1, 'msg' => '数据错误！竞赛默认最小参赛人数不能大于竞赛默认最大参赛人数！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_min_people_num' => $data['default_contest_min_people_num']]);
                $redis5->del('default_contest_min_people_num');
                $redis5->set('default_contest_min_people_num', $data['default_contest_min_people_num']);
            }

            if ($data['default_contest_max_people_num'] != $redis5->get('default_contest_max_people_num')) {
                if (!is_numeric($data['default_contest_min_people_num']) || !is_numeric($data['default_contest_max_people_num'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['default_contest_min_people_num'] < 0 || $data['default_contest_max_people_num'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                if ($data['default_contest_min_people_num'] > $data['default_contest_max_people_num']) {
                    return json(['code' => -1, 'msg' => '数据错误！竞赛默认最小参赛人数不能大于竞赛默认最大参赛人数！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['default_contest_max_people_num' => $data['default_contest_max_people_num']]);
                $redis5->del('default_contest_max_people_num');
                $redis5->set('default_contest_max_people_num', $data['default_contest_max_people_num']);
            }

            if ($data['usercloudfilememory'] != $redis5->get('usercloudfilememory')) {
                if (!is_numeric($data['usercloudfilememory'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['usercloudfilememory'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
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
                    ->update(['useqqmail' => $data['useqqmail']]);
                $redis5->del('useqqmail');
                $redis5->set('useqqmail', $data['useqqmail']);
            }

            if ($data['chatgpt_api_url'] != $redis5->get('chatgpt_api_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['chatgpt_api_url' => $data['chatgpt_api_url']]);
                $redis5->del(Base::$chat_gpt_api_url_key);
                $redis5->set(Base::$chat_gpt_api_url_key, $data['chatgpt_api_url']);
            }

            if ($data['chatgpt_keys'] != $redis5->get('chatgpt_keys')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['chatgpt_keys' => $data['chatgpt_keys']]);
                $redis5->del(Base::$chatgpt_keys_key);
                $redis5->set(Base::$chatgpt_keys_key, $data['chatgpt_keys']);
            }

            if ($data['idemaxtime'] != $redis5->get('idemaxtime')) {
                if (!is_numeric($data['idemaxtime'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['idemaxtime'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['idemaxtime' => $data['idemaxtime']]);
                $redis5->del('idemaxtime');
                $redis5->set('idemaxtime', $data['idemaxtime']);
            }

            if ($data['idemaxmemory'] != $redis5->get('idemaxmemory')) {
                if (!is_numeric($data['idemaxmemory'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['idemaxmemory'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['idemaxmemory' => $data['idemaxmemory']]);
                $redis5->del('idemaxmemory');
                $redis5->set('idemaxmemory', $data['idemaxmemory']);
            }

            if ($data['GLOBfronturl'] != $redis5->get('GLOBfronturl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['GLOBfronturl' => $data['GLOBfronturl']]);
                $redis5->del('GLOBfronturl');
                $redis5->set('GLOBfronturl', $data['GLOBfronturl']);
            }

            if ($data['ssh_back_url'] != $redis5->get('ssh_back_url')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['ssh_back_url' => $data['ssh_back_url']]);
                $redis5->del('ssh_back_url');
                $redis5->set('ssh_back_url', $data['ssh_back_url']);
            }

            if ($data['mysmtpurl'] != $redis5->get('mysmtpurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['mysmtpurl' => $data['mysmtpurl']]);
                $redis5->del('mysmtpurl');
                $redis5->set('mysmtpurl', $data['mysmtpurl']);
            }

            if ($data['mysmtpname'] != $redis5->get('mysmtpname')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['mysmtpname' => $data['mysmtpname']]);
                $redis5->del('mysmtpname');
                $redis5->set('mysmtpname', $data['mysmtpname']);
            }

            if ($data['mysmtppassword'] != $redis5->get('mysmtppassword')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['mysmtppassword' => $data['mysmtppassword']]);
                $redis5->del('mysmtppassword');
                $redis5->set('mysmtppassword', $data['mysmtppassword']);
            }

            if ($data['musicbkurl'] != $redis5->get('musicbkurl')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
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
                    ->update(['classurl' => $data['classurl']]);
                $redis5->del('classurl');
                $redis5->set('classurl', $data['classurl']);
            }
            if ($data['GLOBlinuxurl'] != $redis5->get('GLOBlinuxurl')) {
                // 更改服务器ip自动更新socket地址
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
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
                if ($data['GLOBiplimit'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['GLOBiplimit' => $data['GLOBiplimit']]);
                $redis5->del('GLOBiplimit');
                $redis5->set('GLOBiplimit', $data['GLOBiplimit']);
            }
            if ($data['GLOBiplimitTime'] != $redis5->get('GLOBiplimitTime')) {
                if (!is_numeric($data['GLOBiplimitTime'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['GLOBiplimitTime'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['GLOBiplimitTime' => $data['GLOBiplimitTime']]);
                $redis5->del('GLOBiplimitTime');
                $redis5->set('GLOBiplimitTime', $data['GLOBiplimitTime']);
            }
            if ($data['GLOBipblack'] != $redis5->get('GLOBipblack')) {
                if (!is_numeric($data['GLOBipblack'])) {
                    return json(['code' => -1, 'msg' => '类型错误！请填写数字！']);
                }
                if ($data['GLOBipblack'] < 0) {
                    return json(['code' => -1, 'msg' => '数字不能小于0！']);
                }
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
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
                    ->update(['useemail' => $data['useemail']]);
                $redis5->del('useemail');
                $redis5->set('useemail', $data['useemail']);
            }
            if ($data['smtp'] != $redis5->get('smtp')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
                    ->update(['smtp' => $data['smtp']]);
                $redis5->del('smtp');
                $redis5->set('smtp', $data['smtp']);
            }
            if ($data['smtpkey'] != $redis5->get('smtpkey')) {
                Db::table('setting')
                    ->where('id', $db->id)
                    ->where('isdel', 0)
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
     * 添加i用户黑名单
     * @param Request $request 请求
     * @return string $res json
     */
    public function addBlackUser(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot) {
            return json(['code' => -1, 'msg' => '无权限']);
        }
        $user_name = $request->post('user_name');
        $user_db = Db::table('user')
            ->where('name', $user_name)
            ->where('isdel', 0)
            ->select('id')
            ->first();
        if (!$user_db) {
            return json(['code' => -1, 'msg' => '用户不存在']);
        }
        $user_id = $user_db->id;
        $redis0 = Redis::connection('db0');
        $redis0->set('BlackID' . $user_id, 1);
        $ip = '0.0.0.0';
        $db = Db::table('blackip')
            ->where('user_id', $user_id)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => -1, 'msg' => '该用户已经拉黑']);
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
            ->where('ip', 'like', '%' . $ip . '%')
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
        Base::loadLinuxData($res);
        return json(['data' => $res]);
    }
}
