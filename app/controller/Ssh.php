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
            ->orderBy('end_port', 'asc')
            ->get();
        $msg = '';
        $public_ssh_domain = Base::getSettingKeyData('public_ssh_domain');
        foreach ($db as $key => &$tem) {
            $msg .= '<h5>' . ($key + 1) . '号LTPP-SSH服务器</h5>' . "<details>\n\n" . '> 登录命令：ssh -p ' . ($tem->begin_port ?? '') . ' ltpp@' . $public_ssh_domain . "\n\n" . '> 登陆密码：' . ($tem->password ?? '') . "\n\n" .
                '> [点击打开在线版本VSCODE](http://' . $public_ssh_domain . ':' . ($tem->begin_port + 1) . ")\n\n" .
                '<summary>一共可用' . ($tem->end_port - $tem->begin_port + 1) . '个公网端口【' . $tem->begin_port . '-' . $tem->end_port . '】</summary></details>';
        }
        return $msg;
    }
}
