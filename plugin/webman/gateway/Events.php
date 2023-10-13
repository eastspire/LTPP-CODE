<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-07-10 10:12:11
 * @FilePath: \LTPP-CODE\plugin\webman\gateway\Events.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use app\controller\Base;
use Exception;
use GatewayWorker\Lib\Gateway;
use support\Redis;
use plugin\webman\gateway\ClassMsg;
use plugin\webman\gateway\GlobalNotice;
use plugin\webman\gateway\GroupChat;
use plugin\webman\gateway\PrivateChat;

class Events extends ChatBase
{

    static public function onWorkerStart($worker)
    {
    }

    static public function onConnect($client_id)
    {
    }

    /**
     * 获取authorization和onekey
     * @param string $data
     * @return array $res
     */
    static public function getParamList($data = '')
    {
        try {
            $remove_string = '@ltpp@';
            // 将字符串根据分隔符拆分成数组
            $param_list = explode($remove_string, $data);
            if (!$param_list || empty($param_list)) {
                return [null, null];
            }
            // 去除指定的字符串
            $param_list = array_diff($param_list, [$remove_string]);
            if (!$param_list || empty($param_list) || sizeof($param_list) != 2) {
                return [null, null];
            }
        } catch (Exception $e) {
            return [null, null];
        }
        return $param_list;
    }

    static public function onWebSocketConnect($client_id, $data)
    {
        try {
            if (!Gateway::getUidByClientId($client_id)) {
                $all = $data['server']['QUERY_STRING'] ?? '';
                $param_list = Events::getParamList($all);
                $authorization = $param_list[0];
                $onekey = $param_list[1];
                if (!$authorization || !$onekey) {
                    return;
                }
                $uid = Base::getUidByToken($authorization);
                if (!$uid) {
                    return;
                }
                $aid = Base::getIdByUid($uid);
                if (!$aid) {
                    return;
                }
                $redis14 = Redis::connection('db14');
                // 判断单点登录
                if ($onekey != $redis14->get($aid . 'login')) {
                    return;
                }
                Gateway::bindUid($client_id, $aid);
            }
        } catch (Exception $e) {
            return;
        }
    }

    static public function onMessage($client_id, $message)
    {
        if (!$client_id || !$message) {
            return;
        }
        $message = json_decode($message);
        if (isset($message->user_id)) {
            $message->user_id = Base::getIdByUid($message->user_id);
        }

        if (isset($message->group_data) && isset($message->group_data->group_id)) {
            $message->group_data->group_id = Base::getIdByUid($message->group_data->group_id);
        }

        if (ChatBase::judgeIsHeart($client_id, $message)) {
            return;
        }
        $redis16 = Redis::connection('db16');
        $my_aid = Gateway::getUidByClientId($client_id);
        $db_my = Base::getUserData($my_aid);
        if (ChatBase::judgeUserIsSafe($client_id, $message, $my_aid, $db_my)) {
            if (ChatBase::judgeIsChat($message)) {
                // 向用户，群组发送聊天 或者 通知 相关
                if (ChatBase::judgeIsPrivateChat($message, $redis16, $my_aid, $db_my)) {
                    // 私聊
                    PrivateChat::privateChat($client_id, $message, $db_my, $db_user);
                } else if (ChatBase::judgeIsGroupChat($message, $redis16, $my_aid, $db_my)) {
                    // 群聊
                    GroupChat::groupChat(
                        $client_id,
                        $message,
                        $db_my,
                        $db_user
                    );
                } else if (ChatBase::judgeIsNoticeAndCanSendNotice($message, $db_my)) {
                    // 公告
                    GlobalNotice::globalNotice($client_id, $message, $db_my, $db_user);
                } else if (ChatBase::judgeIsClassChat($message)) {
                    // 课堂
                    ClassMsg::classMsg($client_id, $message, $db_my, $db_user);
                }
            } else if (ChatBase::judgeIsOperationGroup($message)) {
                // 群聊相关操作
                if (ChatBase::judgeCreatGroup($message)) {
                    // 新建群聊
                    GroupChat::createChat($client_id, $message, $db_my);
                } else if (ChatBase::judgeJoinGroup($message)) {
                    // 加入群聊
                    GroupChat::joinChat($client_id, $message, $db_my);
                } else if (ChatBase::judgeIsConnectChat($message, $redis16, $my_aid, $db_my)) {
                    // 连接群聊
                    GroupChat::connectChat($client_id, $message, $db_my);
                } else if ($message->msgtype == 'delete_group') {
                    // 解散群聊
                    GroupChat::deleteChat($client_id, $message, $db_my);
                } else if ($message->msgtype == 'exit_group') {
                    // 退出群聊
                    GroupChat::exitChat($client_id, $message, $db_my);
                }
            }
        }
    }

    static public function onClose($client_id)
    {
        if (Gateway::getUidByClientId($client_id)) {
            Gateway::sendToAll(json_encode(['msgtype' => 'class', 'name' => '系统提示', 'msg' => '在线人数：' . Gateway::getAllUidCount(), 'time' => date('Y-m-d H:i:s', time())]));
        }
    }
}