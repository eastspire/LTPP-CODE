<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-27 17:09:26
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-08 07:41:03
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
        'id',
        'userid',
        'port',
        'password',
        'buy_time'
    ];

    /**
     * port起始端口
     * @var int $port_begin
     */
    static $port_begin = 50000;

    /**
     * port缓存
     * @var int $port_key
     */
    static $port_key = 'port';

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
     * 判断是否购买过
     * @param int $user_id
     * @return {*} $res
     */
    static public function judgeHasBuy($user_id)
    {
        $db = Db::table('ssh')
            ->where('userid', $user_id)
            ->where('isdel', 0)
            ->first();
        return $db;
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
                return '参数错误！';
            }
            $my_data = Base::getUserData($my_aid);

            if (!$my_data || !isset($my_data->email)) {
                return '用户不存在！';
            }
            $has_buy = Ssh::judgeHasBuy($my_aid);
            if ($has_buy) {
                $url = Base::getSettingKeyData('ssh_back_url');
                $ssh_ip = Base::getIp($url);
                $msg = '您已购买过本产品！' . "\n" . '登录命令：ssh -p ' . ($has_buy->port ?? '') . ' ltpp@' . $ssh_ip . "\n" . '登陆密码：' . ($has_buy->password ?? '');
                return $msg;
            }
            if ($my_data->money < Ssh::$price) {
                $msg = '余额不足！购买失败！';
                return $msg;
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