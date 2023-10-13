<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-08 12:08:52
 * @FilePath: \LTPP-CODE\app\queue\redis\BuySsh.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */
namespace app\queue\redis;

use app\controller\Base;
use app\controller\Robot;
use app\controller\Ssh;
use Exception;
use support\Db;
use support\Redis;
use Webman\RedisQueue\Consumer;

class BuySsh implements Consumer
{
    // 要消费的队列名
    public $queue = 'buy_ssh';

    /**
     * 获取下一个地址
     */
    public function getPort()
    {
        $key = Ssh::$port_key;
        $redis22 = Redis::connection('db22');
        $port = $redis22->get($key);
        if ($port) {
            $redis22->setNx($key, $port + 1);
            return (int) $port;
        }
        $db = Db::table('ssh')
            ->orderBy('id', 'desc')
            ->select('port')
            ->first();
        if ($db) {
            $port = $db->port;
            $redis22->set($key, $port);
            ++$port;
        } else {
            $port = Ssh::$port_begin;
            $redis22->set($key, $port);
        }
        return (int) $port;
    }

    // 消费
    public function consume($data)
    {
        $redis22 = null;
        $my_aid = 0;
        $key = '';
        try {
            $redis22 = Redis::connection('db22');
            $my_aid = (int) $data['my_aid'];
            if (!$my_aid) {
                return;
            }
            // 缓存存在
            $key = Ssh::$port_user_buy_cache_key . $my_aid;

            $title = 'LTPP-SSH购买详情';
            $msg = '';

            $my_data = Base::getUserData($my_aid);
            if (!$my_data || !isset($my_data->email)) {
                return;
            }
            $has_buy = Ssh::judgeHasBuy($my_aid);

            if ($has_buy) {
                // 已经购买过
                $url = Base::getSettingKeyData('ssh_back_url');
                $ssh_ip = Base::getIp($url);
                $msg = '您已购买过本产品！' . "\n" . '登录命令：ssh -p ' . ($has_buy->port ?? '') . ' ltpp@' . $ssh_ip . "\n" . '登陆密码：' . ($has_buy->password ?? '');
                return;
            }

            if ($redis22->exists($key)) {
                $msg = '请耐心等待购买结束！';
                Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                return;
            }

            $redis22->set($key, true);
            $my_email = $my_data->email ?? '';
            $password = $data['my_password'];
            if (!$password) {
                $password = rand(100000, 999999);
            }
            // 余额不足
            if ($my_data->money < Ssh::$price) {
                $redis22->del($key);
                $msg = '余额不足！购买失败！';
                Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                return;
            }

            while (1) {
                try {
                    $port = $this->getPort();
                    $res = Base::sendRequest(Base::getSettingKeyData('ssh_back_url'), [], [
                        'user_id' => $my_aid,
                        'port' => $port,
                        'password' => $password,
                        'email' => $my_email,
                        'email_url' => Base::getSettingKeyData('mysmtpurl'),
                        'email_mail_name' => Base::getSettingKeyData('mysmtpname'),
                        'email_mail_password' => Base::getSettingKeyData('mysmtppassword'),
                    ]);
                    if (!$res) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                        return;
                    }

                    $res = json_decode($res, false);

                    if (!isset($res->code) || !isset($res->title)) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                        return;
                    }
                    $content = '';
                    if (isset($res->port)) {
                        $port = (int) $res->port;
                    } else {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务错误！购买失败！请重试！';
                        if (isset($res->content)) {
                            $msg = $res->content;
                        }
                        Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                        return;
                    }

                    if ($res->code == 1) {
                        break;
                    } else if ($res->code != 0) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务错误！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                        return;
                    }
                } catch (Exception $e) {
                    $title = '用户【' . $my_data->name . '】购买LTPP-SSH异常';
                    $content = $e->getMessage();
                    Robot::sendChatToOneUserMsg(Base::getRootId(), $title . "<br/>" . $content);
                    return;
                }
            }

            $now = date('Y-m-d H:i:s', time());
            $data = [
                'userid' => $my_aid,
                'port' => (int) $port,
                'password' => (string) $password,
                'buy_time' => $now
            ];

            $res = Base::insertToDb('ssh', $data);

            if (!$res) {
                $redis22->del($key);
                $msg = '购买失败！请重试！';
                Robot::sendChatToOneUserMsg($my_aid, $title . "<br/>" . $msg);
                Robot::sendChatToOneUserMsg(Base::getRootId(), '【' . $now . '】用户【' . $my_data->name . '】购买LTPP-SSH失败' . "<br/>" . $msg);
                return;
            }

            Robot::sendChatToOneUserMsg(Base::getRootId(), '【' . $now . '】用户【' . $my_data->name . '】购买LTPP-SSH成功' . "<br/>" . json_encode($data));

            $res = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->decrement('money', Ssh::$price);
            Base::updateUserDataRedis($my_aid);

            $url = Base::getSettingKeyData('ssh_back_url');
            $ssh_ip = Base::getIp($url);
            $content = '您的登录命令为：ssh -p ' . $port . ' ltpp@' . $ssh_ip . '<br>' .
                '登录密码：' . $password . '<br>root用户（默认关闭root用户远程登陆）密码：ltpp<br>' .
                '如需使用本产品，请在控制台中运行该命令并输入密码';
            Robot::sendChatToOneUserMsgAndEmail($my_aid, $title . "<br/>" . $content);
            $redis22->del($key);
        } catch (Exception $e) {
            $title = '用户【' . ($my_data->name ?? '') . '】购买LTPP-SSH异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsg(Base::getRootId(), $title . "<br/>" . $content);
            $redis22->del($key);
        }
    }
}