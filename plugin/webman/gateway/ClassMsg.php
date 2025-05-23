<?php
/*
 * @Author: root@ltpp.vip
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: root@ltpp.vip
 * @LastEditTime: 2023-03-12 17:23:19
 * @FilePath: \LTPP\plugin\webman\gateway\ClassMsg.php
 * @Description: Email:root@ltpp.vip
 * QQ:1491579574
 * Copyright (c) 2023 by root@ltpp.vip, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use GatewayWorker\Lib\Gateway;

class ClassMsg extends ChatBase
{
    static public function classMsg(&$client_id, &$message, &$db_my)
    {
        Gateway::joinGroup($client_id, ChatBase::$class_join_name);
        try {
            Gateway::sendToGroup(ChatBase::$class_join_name, json_encode([
                'msgtype' => 'class',
                'name' => $db_my->name,
                'msg' => $message->msg,
                'time' => date('Y-m-d H:i:s', time())
            ]));
        } catch (\Exception $e) {
            ChatBase::sendToOneError($client_id, '系统错误');
            return;
        }
    }
}
