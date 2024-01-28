<?php

namespace app\controller;

use support\Request;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Redis as RedisQueue;

class Verification extends Email
{
    /**
     * 发送验证码
     * @param Request $request 请求
     * @return string $res json
     */
    public function send(Request $request)
    {
        $name = $request->post('name');
        $to = $request->post('to');
        if (!$name || !$to) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }

        $canregister = Base::getSettingKeyData('canregister');
        if ($canregister != 1) {
            return \json(['code' => -1, 'msg' => 'root关闭了注册通道！请联系root用户申请账号！']);
        }

        //判断用户名是否为空
        $nullname = $name;
        $tem = preg_replace('/ /', '', $nullname);
        if ($tem == '') {
            return \json(['code' => -1, 'msg' => '用户名不能为空']);
        }
        if (strripos($to, '@qq.com') === false) {
            return \json(['code' => -1, 'msg' => '邮箱请填写QQ邮箱']);
        }
        // 判断用户名是否存在
        $hasname = Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->exists();
        if ($hasname) {
            return \json(['code' => -1, 'msg' => '用户名已存在']);
        }

        $redis2 = Redis::connection('db2');
        //缓存存在
        if ($redis2->get('verification' . $name . $to)) {
            return json(['code' => -1, 'msg' => '发送验证码速度过快！60秒后可重新发送验证码！']);
        }
        $redis2->setEx('verification' . $name . $to, 60, 1);

        $num = rand(1000000, 9999999); //闭区间1000000-9999999随机数
        $redis13 = Redis::connection('db13');

        // 有效期5分钟
        $redis13->setEx($name, 300, $num);
        $offline = (int) Base::getSettingKeyData('offline');
        if ($offline == 0) {
            $content = "来自LTPP(QQ:1491579574):<br/>
            您的注册账号：$name<br/> 
            该账号的验证码是(此验证码5分钟内有效)";
            RedisQueue::send(Base::$redis_queue_send_mail_name, [
                'to' => $to,
                'title' => '验证码',
                'content' => "$content : $num"
            ]);
            return json(['code' => 1, 'msg' => "验证码发送到 $to 邮箱成功,请注意查收"]);
        }
        return json(['code' => 0, 'msg' => "验证码为（有效期5分钟）：" . $num]);
    }

    /**
     * 忘记密码发送重置密码
     * @param Request $request 请求
     * @return string $res json
     */
    public function sendPassword(Request $request)
    {
        $name = $request->post('name');
        $to = $request->post('to');
        if (!$name || !$to) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }

        $judge = Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->select('id', 'email')
            ->first();

        if (!$judge) {
            return json(['code' => -1, 'msg' => '用户不存在']);
        }
        if ($judge->email != $to) {
            return json(['code' => -1, 'msg' => '用户邮箱不正确']);
        }

        $redis2 = Redis::connection('db2');
        //缓存存在
        if ($redis2->get('resetpasswd' . $name . $to)) {
            return \json(['code' => -1, 'msg' => '重置密码速度太快，10分钟后即可重新尝试！']);
        }
        $redis2->setEx('resetpasswd' . $name . $to, 600, 1);
        $loc = $request->getRealIp($safe_mode = true);
        $num = rand(100000, 999999);
        $offline = (int) Base::getSettingKeyData('offline');
        if ($offline == 0) {
            $content = Base::$app_name . "检测到您的账号正在进行重置密码操作<br>
            如不是本人操作请使用该邮件密码进行登录并改密<br/>
            操作者ip地址：$loc<br/>
            您的新密码是(此密码请勿泄露给他人)";
            RedisQueue::send(Base::$redis_queue_send_mail_name, [
                'to' => $to,
                'title' => Base::$app_name . '【新密码】',
                'content' => "$content : $num"
            ]);
        }
        Db::table('user')
            ->where('id', $judge->id)
            ->update(['password' => Base::passwordEncryption($num)]);
        Base::updateUserDataRedis($judge->id);
        return json(['code' => 1, 'msg' => '密码更新成功']);
    }

    /**
     * 登录发送
     * @param Request $request 请求
     * @param string $name 用户名
     * @param string $to 需要接受邮件的邮箱
     * @return string $res json
     */
    static public function sendLogin(Request $request, $name, $to)
    {
        $offline = (int) Base::getSettingKeyData('offline');
        if ($offline == 1) {
            return json(['code' => 1, 'msg' => '']);
        }
        $judge = Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->select('email')
            ->first();

        if (!$judge) {
            return json(['code' => -1, 'msg' => '用户不存在']);
        }
        if ($judge->email != $to) {
            return json(['code' => -1, 'msg' => '邮箱错误']);
        }
        $time = date('Y-m-d H:i:s', time());
        $loc = $request->getRealIp($safe_mode = true);
        $content = "LTPP账号登陆提示<br>
                    您的账号：$name <br>
                    于北京时间：$time <br>
                    在 $loc 登录<br>
                    如不是本人登录请尽快修改密码,<br>
                    如是本人登录请忽略该邮件";
        RedisQueue::send(Base::$redis_queue_send_mail_name, [
            'to' => $to,
            'title' => '您的帐号登录提示',
            'content' => $content
        ]);
        return json(['code' => 1, 'msg' => '登录邮件发送成功']);
    }
}
