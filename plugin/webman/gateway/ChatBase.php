<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-14 18:23:04
 * @LastEditors: SQS 1491579574@qq.com
 * @LastEditTime: 2023-05-24 15:48:31
 * @FilePath: \LTPP\plugin\webman\gateway\ChatBase.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use app\controller\Base;
use app\controller\Robot;
use GatewayWorker\Lib\Gateway;
use support\Db;
use support\Redis;

/*
msgtypeObj: {
private_chat: "private_chat",
group_chat: "group_chat",
create_group: "create_group",
join_group: "join_group",
delete_group: "delete_group",
exit_group: "exit_group",
},
*/

class ChatBase extends Robot
{
    // 切勿修改以下所有变量
    static $send_txt_limit_length = 10000;
    static $group_chat__first_name = 'group_chat';
    static $private_chat__first_name = 'private_chat';
    static $class_join_name = 'ClassTeach';
    static $group_name_limit_length = 11;
    static $type_notice_chat_name = 'notice_chat'; //公告消息
    static $type_class_chat_name = 'class_chat'; //课堂消息
    static $type_create_group = 'create_group'; // 建群成功
    static $type_join_group = 'join_group'; // 加群成功
    static $type_connect_all_group_success = 'connect_all_group_success'; //该用户所有群聊连接成功
    static $type_private_chat = 'private_chat'; //私聊消息
    static $type_group_chat = 'group_chat'; //群聊消息

    // 前端用户用户列表存储的类型判断，是否是群聊或者私聊
    static $type_private_chat_name = 'private_chat'; //私聊消息
    static $type_group_chat_name = 'group_chat'; //群聊消息

    static $type_error = "error"; //错误消息
    static $type_success = "success"; //成功消息
    static $heart_name = 'heart'; //心跳包名称
    static $group_user_num_limit = 600; //一个群聊用户数目上限

    // 客户端ID获取用户真实ID
    static protected function clientidToId(&$client_id)
    {
        return Gateway::getUidByClientId($client_id);
    }

    // 客户端ID获取用户信息
    static protected function clientIdToUserData(&$client_id)
    {
        $id = ChatBase::clientidToId($client_id);
        if (!$id) {
            return json_encode(['id' => 0, 'name' => '', 'headimage' => '']);
        }
        $db = Db::table('user')
            ->where('id', $id)
            ->select('id', 'name', 'headimage')
            ->first();
        if (!$db) {
            return json_encode(['id' => 0, 'name' => '', 'headimage' => '']);
        }
        return json_encode(['id' => $db->id, 'name' => $db->name, 'headimage' => $db->headimage]);
    }

    // 判断该用户是否合法
    static protected function judgeUserIsSafe(&$client_id, &$message, &$my_aid, &$db_my)
    {
        return Gateway::getUidByClientId($client_id) &&
            $my_aid &&
            $db_my;
    }

    // 判断聊天相关功能是否合法
    static protected function judgeIsChat(&$message)
    {
        return isset($message->msg) && strlen($message->msg) > 0;
    }

    // 判断创建的群信息是否合法
    static protected function judgeCreatGroup(&$message)
    {
        return $message->msgtype == 'create_group' &&
            isset($message->group_data->name) &&
            mb_strlen($message->group_data->name) > 0 &&
            mb_strlen($message->group_data->name) <= ChatBase::$group_name_limit_length;
    }

    // 判断加群的群信息是否合法
    static protected function judgeJoinGroup(&$message)
    {
        return $message->msgtype == 'join_group' &&
            isset($message->group_data->group_id) &&
            isset($message->group_data->code);
    }

    // 判断是否是私聊 且 私聊的身份已经信息 合法
    static protected function judgeIsPrivateChat(&$message, &$redis16, &$my_aid, &$db_my)
    {
        if ($message->msgtype != 'private_chat' || !isset($message->user_id) || !$db_my) {
            return false;
        }
        $db_user = Base::getUserData($message->user_id);
        if (!$db_user) {
            return false;
        }
        return true;
    }

    // 判断是否是群聊 且 群聊的身份已经信息 合法
    static protected function judgeIsGroupChat(&$message, &$redis16, &$my_aid, &$db_my)
    {
        if ($message->msgtype != 'group_chat' || !isset($message->user_id) || !$db_my) {
            return false;
        }
        $db_user = Base::getGroupData($message->user_id);
        if (!$db_user) {
            return false;
        }
        return true;
    }

    // 判断是否是发公告 以及 是否有权限发公告
    static protected function judgeIsNoticeAndCanSendNotice(&$message, &$db_my)
    {
        return $message->msgtype == 'notice' && $db_my->grade == 3;
    }

    // 判断是否是课堂
    static protected function judgeIsClassChat(&$message)
    {
        return $message->msgtype == 'class';
    }

    static protected function judgeIsOperationGroup(&$message)
    {
        return isset($message->group_data);
    }

    // 发送失败消息
    static protected function sendToOneError($client_id, $msg)
    {
        if (!$msg || strlen($msg) <= 0) {
            return;
        }
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'msgtype' => ChatBase::$type_error,
            'msg' => $msg,
        ]));
    }

    // 发送成功消息
    static protected function sendToOneSuccess(&$client_id, &$msg)
    {
        if (!$msg || strlen($msg) <= 0) {
            return;
        }
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
            'msgtype' => ChatBase::$type_success,
            'msg' => $msg,
        ]));
    }

    // 发送成功json消息
    static protected function sendToOneJsonSuccess(&$client_id, &$json)
    {
        if (!$json) {
            return;
        }
        Gateway::sendToUid(Gateway::getUidByClientId($client_id), $json);
    }

    // 判断是否是心跳包
    static protected function judgeIsHeart(&$client_id, &$message)
    {
        if (!$client_id || !$message) {
            return false;
        }
        if ($message->msgtype == 'heart') {
            Gateway::sendToUid(Gateway::getUidByClientId($client_id), json_encode([
                'msgtype' => ChatBase::$heart_name,
            ]));
            return true;
        }
        return false;
    }

    // 判断是否 是 连接群聊
    static protected function judgeIsConnectChat(&$message, &$redis16, &$my_uid, &$db_my)
    {
        return $message->msgtype == 'connect_group';
    }

    /**
     * 更新未读消息数目
     * @param string $type 模式
     * @param string $post_user_id 发送者id
     * @param string $get_user_id 接收者id
     */
    static public function updateNoLookNum($type = 'private_chat', $post_user_id = 0, $get_user_id = 0)
    {
        if ($type == 'private_chat') {
            $redis16 = Redis::connection('db16');
            $name = $post_user_id . 'TO' . $get_user_id;
            if ($redis16->get($name)) {
                $redis16->incr($name);
            } else {
                $redis16->set($name, 1);
            }
        } else if ($type == 'group_chat' && $get_user_id != '') {
            $redis17 = Redis::connection('db17');
            $group_db = Db::table('groupuser')
                ->where('group_id', $get_user_id)
                ->select('user_id')
                ->get();
            foreach ($group_db as &$tem) {
                $name = $get_user_id . 'TO' . $tem->user_id;
                if ($redis17->get($name)) {
                    $redis17->incr($name);
                } else {
                    $redis17->set($name, 1);
                }
            }
        }
    }
}
