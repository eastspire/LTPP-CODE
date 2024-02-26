<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;

class Register extends Email
{
    /**
     * 注册判断
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeRegister(Request $request)
    {
        try {

            $canregister = Base::getSettingKeyData('canregister');
            if ($canregister != 1) {
                return \json(['code' => -1, 'msg' => 'root关闭了注册通道！请联系root用户申请账号！']);
            }

            $name = $request->post('name');
            if (strlen($name) > 18) {
                return \json(['code' => -1, 'msg' => '用户名长度不能大于18']);
            }
            $nullname = $name;
            $temname = preg_replace('/ /', '', $nullname);
            if ($temname == '') {
                return \json(['code' => -1, 'msg' => '用户名不能为空']);
            }
            $email = $request->post('email');
            $nullemail = $request->post('email');
            $tememail = preg_replace('/ /', '', $nullemail);
            if ($tememail == '') {
                return \json(['code' => -1, 'msg' => '邮箱不能为空']);
            }
            if (strripos($email, '@qq.com') === false) {
                return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
            }
            $tempassword = $request->post('password');
            $tempasswd = preg_replace('/ /', '', $tempassword);
            if ($tempasswd == '') {
                return \json(['code' => -1, 'msg' => '密码不能为空']);
            }
            $redis2 = Redis::connection('db2');
            //缓存存在
            if ($redis2->get('ErrorVerification' . $name . $tememail)) {
                return json(['code' => -1, 'msg' => '30秒后可重新验证！']);
            }
            $judge_name = Db::table('user')
                ->where('name', $name)
                ->where('isdel', 0)
                ->exists();

            if ($judge_name) { //用户名已存在，无法注册
                return json(['code' => -1, 'msg' => '用户名已存在，无法注册']);
            }

            $epasswd = $request->post('password');
            $password = Base::passwordEncryption($epasswd); //密码加密两次

            $UserVerification = $request->post("code");

            $sex = $request->post("sex");
            $now = date('Y-m-d H:i:s', time());

            $data = [
                'name' => $name,
                'password' => $password,
                'sex' => $sex,
                'registertime' => $now,
                'headimage' => Base::getEmailImageToLtppUrl($email),
                'fans' => 0,
                'follow' => 0,
                'grade' => 1,
                'email' => $email,
                'school' => $request->post("school") ?? '无',
                'enrollment_year' => $request->post("enrollmentyear") ?? 0,
                'subject' => $request->post("subject") ?? '无',
                'class' => $request->post("class") ?? '无',
                'money' => 1
            ];

            $redis13 = Redis::connection('db13');

            //获取数据库验证码
            $JudgeVerification = $redis13->get($name);

            if (!$JudgeVerification) {
                return json(['code' => -3, 'msg' => '验证码不存在或已过期！']);
            }
            if ($UserVerification != $redis13->get($name)) {
                $redis2->setEx('ErrorVerification' . $name . $tememail, 30, 1);
                return json(['code' => -3, 'msg' => '验证码错误！30秒后可再次尝试！']);
            }
            $my_aid = Base::insertToDb('user', $data);
            if ($my_aid) {
                Base::updateUserDataRedis($my_aid);
                Cloudfile::creatFile($my_aid);
                $offline = (int) Base::getSettingKeyData('offline');
                if ($offline == 0) {
                    // 公网服务器
                    $Zcontent = '您的账号：';
                    $Mcontent = '您的密码：';
                    $cloud_file_readme_txt = Base::getSettingKeyData('cloud_file_readme_txt');
                    $content = $Zcontent . $name . '<br>' .
                        $Mcontent . $epasswd . '<br>' .
                        $cloud_file_readme_txt;
                    RedisQueue::send(Base::$redis_queue_send_mail_name, [
                        'to' => $email,
                        'title' => 'LTPP账号注册成功',
                        'content' => $content
                    ]);
                }
                Robot::sendChatToOneUserMsg($my_aid, '<h2>LTPP账号注册成功</h2>' . "\n" . $content);
                Robot::sendChatToOneUserMsg(Base::getRobotId(), '<strong>【' . $now . '】</strong>用户<strong>【' . $name . '】</strong>注册成功');
                return json(['code' => 1, 'msg' => '账号注册成功']);
            }
            return json(['code' => 0, 'msg' => '账号注册失败']);
        } catch (\Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return json(['code' => 0, 'msg' => '账号注册失败']);
    }
};
