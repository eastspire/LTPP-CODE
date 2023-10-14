<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-14 14:46:54
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
            $redis22->setNx($key, $port + 2);
            return (int) $port;
        }
        $db = Db::table('ssh')
            ->orderBy('id', 'desc')
            ->select('port')
            ->first();
        if ($db) {
            // 当前端口
            $port = $db->port + 2;
        } else {
            // 当前端口
            $port = Ssh::$port_begin;
        }
        // 更新下一个端口
        $redis22->set($key, $port + 2);
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
            if (!$my_data) {
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
                Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                return;
            }

            $redis22->set($key, true);

            $password = $data['my_password'];
            if (!$password) {
                $password = rand(100000, 999999);
            }
            // 余额不足
            if ($my_data->money < Ssh::$price) {
                $redis22->del($key);
                $msg = '余额不足！购买失败！';
                Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                return;
            }

            while (1) {
                try {
                    $port = $this->getPort();
                    $res = Base::sendRequest(Base::getSettingKeyData('ssh_back_url'), [], [
                        'user_id' => $my_aid,
                        'port' => $port,
                        'password' => $password
                    ]);

                    if (!$res) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                        return;
                    }

                    $res = json_decode($res, false);

                    if (!isset($res->code) || !isset($res->title)) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
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
                        Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                        return;
                    }

                    if ($res->code == 1) {
                        break;
                    } else if ($res->code != 0) {
                        $redis22->del($key);
                        $msg = 'LTPP-SSH服务错误！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                        return;
                    }
                } catch (Exception $e) {
                    $title = '用户【' . $my_data->name . '】购买LTPP-SSH异常';
                    $content = $e->getMessage();
                    Robot::sendChatToOneUserMsg(Base::getRootId(), '#### ' . $title . "\n" . $content);
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
                Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $msg);
                Robot::sendChatToOneUserMsg(Base::getRootId(), '【' . $now . '】用户【' . $my_data->name . '】购买LTPP-SSH失败' . "\n" . $msg);
                return;
            }

            Robot::sendChatToOneUserMsg(Base::getRootId(), '【' . $now . '】用户【' . $my_data->name . '】购买LTPP-SSH成功' . "\n" . json_encode($data));

            $res = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->lockForUpdate()
                ->decrement('money', Ssh::$price);
            Base::updateUserDataRedis($my_aid);

            $url = Base::getSettingKeyData('ssh_back_url');
            $ssh_ip = Base::getIp($url);
            $content = '> 您的SSH登录命令为：ssh -p ' . $port . ' ltpp@' . $ssh_ip . "\n" .
                '> ltpp用户登录密码：' . $password . "\n" . '> root用户（默认关闭root用户远程登陆）密码：ltpp' . "\n\n" .
                '> 在线版本VSCODE访问地址：http://' . $ssh_ip . ':' . ($port + 1) . "\n" . '> 在线版本VSCODE访问密码：' . $password;
            Robot::sendChatToOneUserMsg($my_aid, '#### ' . $title . "\n" . $content);
            $redis22->del($key);
        } catch (Exception $e) {
            $title = '用户【' . ($my_data->name ?? '') . '】购买LTPP-SSH异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsg(Base::getRootId(), '#### ' . $title . "\n" . $content);
            $redis22->del($key);
        }
    }
}