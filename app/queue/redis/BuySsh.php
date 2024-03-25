<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-11-13 14:38:46
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
        $db = Db::table('ssh')
            ->orderBy('id', 'desc')
            ->select('port')
            ->first();
        if ($db) {
            // 当前端口
            $port = $db->port + Base::$ssh_default_open_ports_num;
        } else {
            // 当前端口
            $port = Ssh::$port_begin;
        }
        return (int) $port;
    }

    // 消费
    public function consume($data)
    {
        $my_aid = 0;
        try {
            $my_aid = (int) $data['my_aid'];
            if (!$my_aid) {
                return;
            }

            $title = 'LTPP-SSH购买详情';

            $msg = '';

            $my_data = Base::getUserData($my_aid);
            if (!$my_data) {
                return;
            }

            $password = $data['my_password'];
            if (!$password) {
                $password = rand(100000, 999999);
            }
            $is_root = Base::judgeIsRoot($my_aid);
            // 非root用户并且余额不足
            if ($my_data->money < Ssh::$price && !$is_root) {
                $msg = '余额不足！购买失败！';
                Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                return;
            }
            $port = 0;
            while (1) {
                try {
                    $port = $this->getPort();
                    $res = Base::postRequest('http://' . Base::$ssh_ip . ':' . Base::$ssh_port, [], [
                        'port' => (int)$port,
                        'password' => (string)$password,
                        'port_num' => (int)Base::$ssh_default_open_ports_num
                    ]);
                    if (!$res) {
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                        return;
                    }

                    $res = json_decode($res, false);

                    if (!isset($res->code) || !isset($res->title)) {
                        $msg = 'LTPP-SSH服务未启动！购买失败！请重试！';
                        Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                        return;
                    }
                    $content = '';

                    if ($res->code == 1) {
                        break;
                    }
                    if ($res->code == -1) {
                        $msg = $res->content;
                        Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                        return;
                    }
                } catch (Exception $e) {
                    $title = '用户【' . $my_data->name . '】购买LTPP-SSH异常';
                    $content = $e->getMessage();
                    Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . "</h4>\n\n" . $content);
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
                $msg = '购买失败！请重试！';
                Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【' . $now . '】</strong>用户<strong>【' . $my_data->name . '】</strong>购买LTPP-SSH失败' . "\n" . $msg);
                return;
            }

            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<strong>【' . $now . '】</strong>用户<strong>【' . $my_data->name . '】</strong>购买LTPP-SSH成功' . "\n" . json_encode($data));

            if (!$is_root) {
                // 非 root 用户 扣钱
                $res = Db::table('user')
                    ->where('id', $my_aid)
                    ->where('isdel', 0)
                    ->decrement('money', Ssh::$price);
                Base::updateUserDataRedis($my_aid);
            }
            $content = Ssh::getHasBuyMsg($my_aid);
            Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $content);
        } catch (Exception $e) {
            $title = '用户【' . ($my_data->name ?? '') . '】购买LTPP-SSH异常';
            Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . '</h4>');
        }
    }
}
