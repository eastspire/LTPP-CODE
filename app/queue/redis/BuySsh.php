<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: eastspire 1491579574@qq.com
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
            ->where('isdel', 0)
            ->orderBy('end_port', 'desc')
            ->select('end_port')
            ->first();
        if ($db) {
            // 当前端口
            $port = $db->end_port + 1;
        } else {
            // 当前端口
            $port = Ssh::$port_begin;
        }
        return (int) $port;
    }

    // 消费
    public function consume($data)
    {
        $title = 'LTPP-SSH购买详情';
        $my_aid = 0;
        $msg = '';
        try {
            $my_aid = (int) $data['my_aid'];
            $cpu = (float)$data['cpu'];
            $memory = (float)$data['memory'];
            $port_num = (int)$data['port_num'];
            if (!$my_aid || !$cpu || !$memory || !$port_num) {
                $msg = '参数错误！购买失败！';
                Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                return;
            }

            $my_data = Base::getUserData($my_aid);
            if (!$my_data) {
                $msg = '用户不存在！购买失败！';
                Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
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
            $port = $this->getPort();
            while (1) {
                try {
                    $name = Base::creatDockerName($port);
                    $res = Base::postRequest(Base::getSettingKeyData('private_ssh_url') . '/SSH/buy', [], [
                        'name' => $name,
                        'port' => (int)$port,
                        'password' => (string)$password,
                        'port_num' => max(2, $port_num),
                        'cpu' =>  $cpu,
                        'memory' => $memory
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
                    ++$port;
                } catch (Exception $e) {
                    $title = '用户【' . $my_data->name . '】购买LTPP-SSH异常';
                    $content = $e->getMessage();
                    Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . "</h4>\n\n" . $content);
                    return;
                }
            }

            $now = date('Y-m-d H:i:s', time());
            $data = [
                'name' => $name,
                'userid' => $my_aid,
                'begin_port' => (int) $port,
                'end_port' => (int) ($port + $port_num - 1),
                'password' => (string) $password,
                'buy_time' => $now,
                'cpu' =>  $cpu,
                'memory' => $memory
            ];

            $res = Base::insertToDb('ssh', $data);

            if ($res === null) {
                $msg = '购买失败！请重试！';
                Robot::sendChatToOneUserMsg($my_aid, '<h4>' . $title . "</h4>\n\n" . $msg);
                Robot::sendChatToOneUserMsg(Base::getRootId(), '<strong>【' . $now . '】</strong>用户<strong>【' . $my_data->name . '】</strong>购买LTPP-SSH失败' . "\n" . $msg);
                return;
            }

            Robot::sendChatToOneUserMsg(Base::getRootId(), '<strong>【' . $now . '】</strong>用户<strong>【' . $my_data->name . '】</strong>购买LTPP-SSH成功' . "\n" . json_encode($data));

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
