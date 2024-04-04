<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-27 17:09:26
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-14 14:43:15
 * @FilePath: \LTPP-CODE\app\controller\Ssh.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use support\Db;
use Webman\RedisQueue\Redis as RedisQueue;

class Ssh
{
    /**
     * 后台展示数据库字段
     * @var array $version 软件版本
     */
    static $db_key = [
        'name',
        'userid',
        'begin_port',
        'end_port',
        'password',
        'buy_time'
    ];

    /**
     * port起始端口
     * @var int $port_begin
     */
    static $port_begin = 60000;

    /**
     * 用户购买锁的key
     * @var int $port_user_buy_cache_key
     */
    static $port_user_buy_cache_key = 'buy_cache';

    /**
     * ssh价格
     * @var double $price
     */
    static $price = 9.9;

    /**
     * 获取购买过的信息合集
     * @param int $user_id
     * @return {*} $res
     */
    static public function getHasBuyMsg($user_id)
    {
        $db = Db::table('ssh')
            ->where('userid', $user_id)
            ->where('isdel', 0)
            ->select('begin_port', 'end_port', 'password')
            ->get();
        $msg = '';
        $url = Base::getSettingKeyData('ssh_back_url');
        $ssh_ip = Base::getIp($url);
        foreach ($db as &$tem) {
            $msg .= '<h5>您已购买过本产品！</h5>' . "\n\n" . '> 登录命令：ssh -p ' . ($tem->begin_port ?? '') . ' ltpp@' . $ssh_ip . "\n\n" . '> 登陆密码：' . ($tem->password ?? '') . "\n\n" .
                '> 在线版本VSCODE访问地址：http://' . $ssh_ip . ($tem->begin_port ? (':' . $tem->begin_port + 1) : '') . "\n\n" .
                '> 一共可用' . Base::$ssh_default_open_ports_num . '个公网端口【' . $tem->begin_port . '-' . $tem->end_port . '】' . "\n\n";
        }
        return $msg;
    }

    /**
     * 购买
     * @param int $my_aid
     * @param string $my_password
     * @return $res 结果
     */
    static public function buy($my_aid, $my_password = null)
    {
        try {
            if (!$my_aid || !is_numeric($my_aid)) {
                return 'Base::$param_error_msg！';
            }
            $my_data = Base::getUserData($my_aid);
            if (!$my_data || !isset($my_data->email)) {
                return '用户不存在！';
            }
            RedisQueue::send(Base::$redis_queue_buy_ssh_name, [
                'my_aid' => $my_aid,
                'my_password' => $my_password
            ]);
        } catch (Exception $e) {
            return '系统错误';
        }
        return '系统正在购买！请耐心等待机器人通知购买结果！';
    }
}
