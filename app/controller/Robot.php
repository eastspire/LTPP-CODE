<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 16:40:55
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 14:32:53
 * @FilePath: \LTPP-CODE\app\controller\Robot.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;
use GatewayWorker\Lib\Gateway;
use plugin\webman\gateway\ChatBase;
use support\Db;
use app\controller\Base;
use Webman\RedisQueue\Redis as RedisQueue;

class Robot extends Email
{
    /**
     * 机器人发送私聊给用户（含邮件）
     * @param string $id 用户id
     * @param string $msg 消息
     */
    static public function sendChatToOneUserMsgAndEmail($get_user_id = 0, $msg = '')
    {
        if (!$get_user_id || !$msg) {
            return;
        }
        $user_db = Base::getUserData(Base::getRobotId());
        if (!$user_db) {
            $post_user_id = Base::creatRobot();
        }
        $post_user_id = $user_db->id ?? 0;
        $now = date('Y-m-d H:i:s', time());
        $get_user_data = Base::getUserData($get_user_id);
        $msg_id = Base::insertToDb('privatechat', [
            'post_user_id' => $post_user_id,
            'get_user_id' => $get_user_id,
            'msg' => $msg,
            'time' => $now
        ]);
        RedisQueue::send(Base::$redis_queue_send_mail_name, [
            'to' => $get_user_data->email,
            'title' => 'LTPP机器人通知',
            'content' => $msg
        ]);
        try {
            Gateway::sendToUid($get_user_id, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_private_chat_name,
                'msgtype' => 'private_chat',
                'post_user_id' => Base::getChatUserUidById($post_user_id),
                'get_user_id' => Base::getChatUserUidById($get_user_id),
                'name' => $user_db->name,
                'headimage' => $user_db->headimage,
                'msg' => $msg,
                'time' => $now
            ]));
            ChatBase::updateNoLookNum(ChatBase::$type_private_chat_name, $post_user_id, $get_user_id);
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
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * 机器人发送私聊给用户（不含邮件）
     * @param string $id 用户id
     * @param string $msg 消息
     */
    static public function sendChatToOneUserMsg($get_user_id = 0, $msg = '')
    {
        if (!$get_user_id || !$msg) {
            return;
        }
        $user_db = Base::getUserData(Base::getRobotId());
        if (!$user_db) {
            $post_user_id = Base::creatRobot();
        }
        $post_user_id = $user_db->id ?? 0;
        $now = date('Y-m-d H:i:s', time());
        $msg_id = Base::insertToDb('privatechat', [
            'post_user_id' => $post_user_id,
            'get_user_id' => $get_user_id,
            'msg' => $msg,
            'time' => $now
        ]);
        try {
            Gateway::sendToUid($get_user_id, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_private_chat_name,
                'msgtype' => 'private_chat',
                'post_user_id' => Base::getChatUserUidById($post_user_id),
                'get_user_id' => Base::getChatUserUidById($get_user_id),
                'name' => $user_db->name,
                'headimage' => $user_db->headimage,
                'msg' => $msg,
                'time' => $now
            ]));
            ChatBase::updateNoLookNum(ChatBase::$type_private_chat_name, $post_user_id, $get_user_id);
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
        } catch (Exception $e) {
            return;
        }
    }
};
