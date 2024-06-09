<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-07-25 08:06:43
 * @FilePath: \LTPP-CODE\plugin\webman\gateway\GroupChat.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use app\controller\Base;
use app\controller\Image;
use GatewayWorker\Lib\Gateway;
use support\Db;

/*
群管理权限
普通用户grade : 0
管理员grade : 1
群主grade : 2
*/

class GroupChat extends ChatBase
{
    // 发送群聊
    static public function groupChat(&$client_id, &$message, &$db_my, &$db_user)
    {
        $get_user_id = $db_my->id;
        $post_user_id = $message->user_id;
        // 即时通讯群聊名称
        $to_group = ChatBase::$group_chat__first_name . $post_user_id;

        $now = date('Y-m-d H:i:s', time());
        $msg = $message->msg;
        $db = Base::getGroupData($post_user_id);
        $msg_id = Base::insertToDb('groupchat', [
            'get_user_id' => $get_user_id,
            'post_user_id' => $post_user_id,
            'msg' => $msg,
            'time' => $now
        ]);
        Base::dataToSafe($db, true);
        try {
            Gateway::sendToGroup($to_group, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_group_chat_name,
                'msgtype' => ChatBase::$type_group_chat,
                'group_data' => $db,
                'post_user_id' => Base::getChatUserUidById($post_user_id),
                'get_user_id' => Base::getChatUserUidById($get_user_id),
                'name' => $db_my->name,
                'headimage' => $db_my->headimage,
                'msg' => $message->msg,
                'time' => $now
            ]));
        } catch (\Exception $e) {
            ChatBase::sendToOneError($client_id, '系统错误');
            return;
        }
        ChatBase::updateNoLookNum(ChatBase::$type_group_chat_name, $post_user_id, $get_user_id);
    }

    // 新建群聊
    static public function createChat(&$client_id, &$message, &$db_my)
    {
        $group_name = $message->group_data->name;
        $group_groupimage = '';
        if (isset($message->group_data->headimage)) {
            $group_groupimage = $message->group_data->headimage;
        }
        if (!filter_var($group_groupimage, FILTER_VALIDATE_URL) !== false) {
            $group_groupimage = Image::randImage();
        }
        $group_code = '';
        if (!isset($message->group_data->code) || !$message->group_data->code) {
            $group_code = '';
        } else {
            $group_code = $message->group_data->code;
        }

        if (strlen($group_name) >= Base::$name_len_limit) {
            $msg = '群聊名称长度限制 ' . Base::$name_len_limit . ' 字符以内！';
            ChatBase::sendToOneError($client_id, $msg);
            return;
        }

        $now = date('Y-m-d H:i:s', time());
        // 创建数据库 群组
        $group_id = Base::insertToDb('group', [
            'name' => $group_name,
            'code' => $group_code,
            'headimage' => $group_groupimage,
            'creatorid' => $db_my->id,
            'time' => $now,
            'total' => 1
        ]);

        // 加入数据库 群组
        Db::table('groupuser')->insert([
            'group_id' => $group_id,
            'user_id' => $db_my->id,
            'grade' => 2,
            'time' => $now
        ]);
        $msg = '大家好，我是 ' . $db_my->name . ' 。';
        $my_aid = $db_my->id;
        // 发送加群消息到群组
        $msg_id = Base::insertToDb('groupchat', [
            'post_user_id' => $group_id,
            'get_user_id' => $my_aid,
            'msg' => $msg,
            'time' => $now,
        ]);
        $db = Base::getGroupData($group_id);
        // 加入socket 群组
        $socket_group_name = ChatBase::$group_chat__first_name . $group_id;
        Base::dataToSafe($db, true);
        Gateway::joinGroup($client_id, $socket_group_name);
        Gateway::sendToGroup($socket_group_name, json_encode([
            'id' => Base::getChatUserUidById($msg_id),
            'type' => ChatBase::$type_group_chat_name,
            'group_data' => $db,
            'msgtype' => ChatBase::$type_create_group,
            'post_user_id' => Base::getChatUserUidById($group_id),
            'get_user_id' => Base::getChatUserUidById($my_aid),
            'name' => $db_my->name,
            'headimage' => $db_my->headimage,
            'msg' => $msg,
            'time' => $now
        ]));
        $msg = '恭喜您成功创建该群！';
        ChatBase::sendToOneSuccess($client_id, $msg);
    }

    // 加入群聊
    static public function joinChat(&$client_id, &$message, &$db_my)
    {
        $group_id = $message->group_data->group_id;
        $code = $message->group_data->code;
        $msg = '';
        // 判断是否已经加群
        $has_join = Db::table('groupuser')
            ->where('user_id', $db_my->id)
            ->where('group_id', $group_id)
            ->exists();
        if ($has_join) {
            $msg = '您已加群，无法重复加入';
            ChatBase::sendToOneError($client_id, $msg);
            return;
        }
        $now = date('Y-m-d H:i:s', time());
        $socket_group_name = ChatBase::$group_chat__first_name . $group_id;
        $db = Base::getGroupData($group_id);
        Base::dataToSafe($db, true);

        if (!$db) {
            $msg = '没有该群！';
            ChatBase::sendToOneError($client_id, $msg);
            return;
        }

        if ($db->total >= ChatBase::$group_user_num_limit) {
            $msg = '该群已满（该群现有' . ChatBase::$group_user_num_limit . '人）';
            ChatBase::sendToOneError($client_id, $msg);
            return;
        }

        if ($db->code == null || $db->code == '') {
            // 直接进群
            Db::table('groupuser')->insert([
                'group_id' => $group_id,
                'user_id' => $db_my->id,
                'grade' => 0,
                'time' => $now
            ]);
            Gateway::joinGroup($client_id, $socket_group_name);
            $msg = '大家好，我是 ' . $db_my->name . ' 。';
            $my_aid = $db_my->id;
            // 发送加群消息到群组
            $msg_id = Base::insertToDb('groupchat', [
                'post_user_id' => $group_id,
                'get_user_id' => $my_aid,
                'msg' => $msg,
                'time' => $now,
            ]);

            Gateway::sendToGroup($socket_group_name, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_group_chat_name,
                'group_data' => $db,
                'msgtype' => ChatBase::$type_group_chat,
                'post_user_id' => Base::getChatUserUidById($group_id),
                'get_user_id' => Base::getChatUserUidById($my_aid),
                'name' => $db_my->name,
                'headimage' => $db_my->headimage,
                'msg' => $msg,
                'time' => $now,
            ]));
            Db::table('group')
                ->where('id', $group_id)
                ->increment('total', 1);
            Base::updateGroupDataRedis($group_id);
            $msg = '恭喜您加入该群！';
            $res_json = json_encode([
                'msgtype' => 'success',
                'operate' => ChatBase::$type_join_group,
                'type' => ChatBase::$type_group_chat_name,
                'group_data' => $db,
                'msg' => $msg,
            ]);
            ChatBase::sendToOneJsonSuccess($client_id, $res_json);
            return;
        } else if ($db->code == $code) {
            $my_aid = $db_my->id;
            // 验证密码 正确
            Db::table('groupuser')->insert([
                'group_id' => $group_id,
                'user_id' => $my_aid,
                'grade' => 0,
                'time' => $now
            ]);
            Gateway::joinGroup($client_id, $socket_group_name);
            $msg = '大家好，我是 ' . $db_my->name . ' 。';

            // 发送加群消息到群组
            $msg_id = Base::insertToDb('groupchat', [
                'post_user_id' => $group_id,
                'get_user_id' => $my_aid,
                'msg' => $msg,
                'time' => $now,
            ]);
            Gateway::sendToGroup($socket_group_name, json_encode([
                'id' => Base::getChatUserUidById($msg_id),
                'type' => ChatBase::$type_group_chat_name,
                'group_data' => $db,
                'msgtype' => ChatBase::$type_group_chat,
                'post_user_id' => Base::getChatUserUidById($group_id),
                'get_user_id' => Base::getChatUserUidById($my_aid),
                'name' => $db_my->name,
                'headimage' => $db_my->headimage,
                'msg' => $msg,
                'time' => $now,
            ]));
            Db::table('group')
                ->where('id', $group_id)
                ->increment('total', 1);
            Base::updateGroupDataRedis($group_id);
            $msg = '恭喜您加入该群！';
            $res_json = json_encode([
                'msgtype' => 'success',
                'operate' => ChatBase::$type_join_group,
                'type' => ChatBase::$type_group_chat_name,
                'group_data' => $db,
                'msg' => $msg
            ]);
            ChatBase::sendToOneJsonSuccess($client_id, $res_json);
            return;
        }
        $msg = '入群密码错误！';
        ChatBase::sendToOneError($client_id, $msg);
        return;
    }

    // 连接群聊
    static public function connectChat(&$client_id, &$message, &$db_my)
    {
        // 获取该用户全部加入的群组
        $db = Db::table('groupuser')
            ->where('user_id', $db_my->id)
            ->select('group_id')
            ->get();
        foreach ($db as &$tem) {
            // 连接该群
            $socket_group_name = ChatBase::$group_chat__first_name . $tem->group_id;
            Gateway::joinGroup($client_id, $socket_group_name);
        }
        $msg = '您已上线！';
        $res_json = json_encode([
            'msgtype' => ChatBase::$type_connect_all_group_success,
            'operate' => '',
            'msg' => $msg
        ]);
        ChatBase::sendToOneJsonSuccess($client_id, $res_json);
    }

    // 解散群聊
    static public function deleteChat(&$client_id, &$message, &$db_my)
    {
    }

    // 退出群聊
    static public function exitChat(&$client_id, &$message, &$db_my)
    {
    }
}
