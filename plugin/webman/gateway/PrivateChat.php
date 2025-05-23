<?php
/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: 1491579574@qq.com
 * @LastEditTime: 2023-09-16 12:04:00
 * @FilePath: \LTPP-CODE\plugin\webman\gateway\PrivateChat.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use GatewayWorker\Lib\Gateway;
use app\controller\Base;
use support\Db;

class PrivateChat extends PrivateRobot
{
    // 发送私聊
    static public function privateChat(&$client_id, &$message, &$db_my, &$db_user)
    {
        $post_user_id = $db_my->id;
        $get_user_id = $message->user_id;
        $msg_id = 0;
        $now = date('Y-m-d H:i:s', time());

        $msg_id = Base::insertToDb('privatechat', [
            'post_user_id' => $post_user_id,
            'get_user_id' => $get_user_id,
            'msg' => $message->msg,
            'isdel' => 0,
            'time' => $now
        ]);

        if (Gateway::getUidByClientId($client_id) != $message->user_id) {
            // 发给其他人
            Gateway::sendToUid($message->user_id, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_private_chat_name,
                'msgtype' => 'private_chat',
                'post_user_id' => Base::getChatUserUidById($post_user_id),
                'get_user_id' => Base::getChatUserUidById($get_user_id),
                'name' => $db_my->name,
                'headimage' => $db_my->headimage,
                'msg' => $message->msg,
                'time' => $now,
            ]));
            $has = Db::table('privateuser')
                ->where('get_user_id', $get_user_id)
                ->where('post_user_id', $post_user_id)
                ->where('isdel', 0)
                ->exists();
            if (!$has) {
                Base::insertToDb('privateuser', [
                    'post_user_id' => $post_user_id,
                    'get_user_id' => $get_user_id,
                    'isdel' => 0,
                    'time' => $now
                ]);
            }
        }
        // 自己窗口也得发一次
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'id' => Base::getChatUserUidById($msg_id),
            'type' => ChatBase::$type_private_chat_name,
            'msgtype' => 'private_chat',
            'post_user_id' => Base::getChatUserUidById($get_user_id),
            'get_user_id' => Base::getChatUserUidById($post_user_id),
            'name' => $db_my->name,
            'headimage' => $db_my->headimage,
            'msg' => $message->msg,
            'time' => $now,
        ]));

        PrivateRobot::isSendToRobot($get_user_id, $db_my, $client_id, $message->msg);
        ChatBase::updateNoLookNum(ChatBase::$type_private_chat_name, $post_user_id, $get_user_id);
    }
}
