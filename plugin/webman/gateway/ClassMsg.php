<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-03-12 17:23:19
 * @FilePath: \LTPP\plugin\webman\gateway\ClassMsg.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use GatewayWorker\Lib\Gateway;

class ClassMsg extends ChatBase
{
    static public function classMsg(&$client_id, &$message, &$db_my, &$db_user)
    {
        Gateway::joinGroup($client_id, ChatBase::$class_join_name);
        if (mb_strlen($message->msg) > ChatBase::$send_txt_limit_length) {
            $msg = "字数不能超过" . ChatBase::$send_txt_limit_length . "请修改后重试！";
            ChatBase::sendToOneError($client_id, $msg);
            return;
        }
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