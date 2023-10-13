<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 16:40:55
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-16 12:07:36
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
use support\Redis;
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
        $redis5 = Redis::connection('db5');
        $user_db = Base::getUserData(Base::getRobotId());
        if (!$user_db) {
            // 机器人账号不存在，立即发送root邮件通知
            $root_id = Base::getRootId();
            $user_db = Base::getUserData($root_id);
            $email = '1491579574@qq.com';
            $data = [
                'name' => '机器人',
                'password' => Base::passwordEncryption(rand(1, 100000)),
                'sex' => '男',
                'registertime' => date('Y-m-d H:i:s', time()),
                'headimage' => 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $email . '&spec=640',
                'fans' => 0,
                'follow' => 0,
                'online' => 0,
                'grade' => 1,
                'email' => $email
            ];
            $res_id = Base::insertToDb('user', $data);
            if (!$user_db) {
                return;
            }
            $title = '紧急通知';
            $content = '系统机器人的账号不存在，系统已自动重新生成！机器人账号最新id:' . $res_id;
            $redis5->set('robotid', $res_id);
            $offline = (int) Base::getSettingKeyData('offline');
            if ($offline == 0) {
                RedisQueue::send(Base::$redis_queue_send_mail_name, [
                    'to' => $user_db->email,
                    'title' => $title,
                    'content' => $content
                ]);
            }
            return;
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
        $redis5 = Redis::connection('db5');
        $user_db = Base::getUserData(Base::getRobotId());
        if (!$user_db) {
            // 机器人账号不存在，立即发送root邮件通知
            $root_id = Base::getRootId();
            $user_db = Base::getUserData($root_id);
            $email = '1491579574@qq.com';
            $data = [
                'name' => '机器人',
                'password' => Base::passwordEncryption(rand(1, 100000)),
                'sex' => '男',
                'registertime' => date('Y-m-d H:i:s', time()),
                'headimage' => 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $email . '&spec=640',
                'fans' => 0,
                'follow' => 0,
                'online' => 0,
                'grade' => 1,
                'email' => $email
            ];
            $res_id = Base::insertToDb('user', $data);
            if (!$user_db) {
                return;
            }
            $redis5->set('robotid', $res_id);
            return;
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
}
;