<?php

namespace app\controller;

use support\Db;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Login
{
    /**
     * 判断登录
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeLogin(Request $request)
    {
        $name = $request->post('name');
        $password = $request->post('password');
        if (!$name || !$password) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }

        $user_db = Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->select('id', 'name', 'password', 'email', 'grade')
            ->first();
        $redis9 = Redis::connection('db9');
        $loc = $request->getRealIp();
        if (!$user_db) {
            //缓存存在读取缓存
            if ($redis9->get('login' . $name)) {
                if ($redis9->get('login' . $name) >= 5) {
                    return \json(['code' => -1, 'msg' => '登录速度太快，1分钟后即可重新登录！']);
                } else {
                    $redis9->incr('login' . $name);
                }
            } else {
                $redis9->setEx('login' . $name, 60, 1);
            }
            return json(['code' => -1, 'msg' => '账号不存在', 'name' => $name]);
        }

        if ($name != 'root' && $user_db->grade != 3) {
            $canlogin = Base::getSettingKeyData('canlogin');
            if ($canlogin != 1) {
                return \json(['code' => -1, 'msg' => 'root关闭了登录通道！请联系root用户开放登录通道！']);
            }
        }
        $judge_pwd = md5(md5($password));
        //缓存存在
        if ($redis9->get('login' . $name)) {
            if ($redis9->get('login' . $name) >= 5) {
                return \json(['code' => -1, 'msg' => '登录速度太快，1分钟后即可重新登录！']);
            } else {
                $redis9->incr('login' . $name);
            }
        }

        if ($judge_pwd != $user_db->password) {
            if ($redis9->get('login' . $name)) {
                if ($redis9->get('login' . $name) >= 5) {
                    return \json(['code' => -1, 'msg' => '登录速度太快，1分钟后即可重新登录！']);
                }
            } else {
                $redis9->setEx('login' . $name, 60, 1);
            }
            return json(['code' => 0, 'msg' => '账号或密码错误']);
        }

        $user = [
            // 这里必须是一个全局抽象唯一id
            // 因为即时通讯列表用户ID要使用一致算法加密，所以得用Base::getChatUserUidById
            'id' => Base::getChatUserUidById($user_db->id)
        ];

        $authorization = JwtToken::generateToken($user)['access_token'];
        $useemail = Base::getSettingKeyData('useemail');
        if ($useemail == 1) {
            Verification::sendlogin($request, $name, $user_db->email);
        }

        $redis14 = Redis::connection('db14');
        $my_aid = $user_db->id;
        // 更新单点登录信息
        $onekey = md5($my_aid . '|' . time());
        $redis14->setEx($my_aid . 'login', 3600 * 24 * 30, $onekey);
        if (!Base::judgeIsRoot($my_aid)) {
            //ip黑名单判断
            $redis0 = Redis::connection('db0');
            if ($redis0->get('BlackIP' . $loc) || $redis0->get('BlackID' . $my_aid)) {
                return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
            } else {
                $black_aid_db = Db::table('blackip')
                    ->where('user_id', $my_aid)
                    ->where('isdel', 0)
                    ->exists();
                if ($black_aid_db) {
                    $redis0->set('BlackID' . $my_aid, 1);
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
                }
                $black_ip_db = Db::table('blackip')
                    ->where('ip', $loc)
                    ->where('isdel', 0)
                    ->exists();
                if ($black_ip_db) {
                    $redis0->set('BlackIP' . $loc, 1);
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
                }
            }
        }
        $now = date('Y-m-d H:i:s', time());
        //更新时间
        Db::table('user')
            ->where('name', $name)
            ->where('isdel', 0)
            ->update(['lastlogin' => $now]);
        Base::updateUserDataRedis($my_aid);
        Robot::sendChatToOneUserMsg(Base::getRootId(), '**【' . $now . '】** 用户 **【' . $name . '】** 于 **【' . $loc . '】** 登录成功');
        if ($user_db->grade == 2) {
            return json([
                'code' => 2,
                'msg' => '欢迎管理员登录',
                'email' => $user_db->email,
                'authorization' => $authorization,
                'key' => $onekey
            ]);
        }
        if ($user_db->grade == 3) {
            return json([
                'code' => 2,
                'msg' => '欢迎超级管理员登录',
                'email' => $user_db->email,
                'authorization' => $authorization,
                'key' => $onekey
            ]);
        }

        return json([
            'code' => 1,
            'msg' => '欢迎登录',
            'email' => $user_db->email,
            'authorization' => $authorization,
            'key' => $onekey
        ]);
    }
};
