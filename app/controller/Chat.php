<?php
/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-01-14 11:10:10
 * @LastEditors: SQS 1491579574@qq.com
 * @LastEditTime: 2023-06-02 18:02:46
 * @FilePath: \LTPP-CODE\app\controller\Chat.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use plugin\webman\gateway\ChatBase;
use support\Redis;
use GatewayWorker\Lib\Gateway;

class Chat
{
    /**
     * 聊天每次最多拉取消息数目
     * @var int $chat_list_limit 聊天每次最多拉取消息数目
     */
    static $chat_list_limit = 50;

    /**
     * 聊天搜索最大拉取用户数目或者群聊数目
     * @var int $chat_user_limit 聊天每次最多拉取消息数目
     */
    static $chat_user_limit = 50;

    /**
     * 获取历史消息
     * @param Request $request 请求
     * @return string $res json
     */
    public function getHistoryChatData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $type = $request->post('type');
        $msg_uid = $request->post('msg_id');
        $msg_id = Base::getIdByUid($msg_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$type || !$user_uid || !$user_id) {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $limit = Chat::$chat_list_limit;
        $data = [];

        if ($type == ChatBase::$type_private_chat_name) {
            // 私聊
            $is_exies = Base::getUserData($user_id);
            if (empty($is_exies)) {
                return json(['code' => -1, 'msg' => Base::$param_error_msg]);
            }
            if ($msg_id > 0) {
                $data = Db::table('privatechat')
                    ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                        $query
                            ->where('id', '<', $msg_id)
                            ->where('post_user_id', $my_aid)
                            ->where('get_user_id', $user_id)
                            ->where('isdel', 0);
                    })
                    ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                        $query
                            ->where('id', '<', $msg_id)
                            ->where('post_user_id', $user_id)
                            ->where('get_user_id', $my_aid)
                            ->where('isdel', 0);
                    })
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $data = Db::table('privatechat')
                    ->orWhere(function ($query) use ($my_aid, $user_id) {
                        $query
                            ->where('post_user_id', $my_aid)
                            ->where('get_user_id', $user_id)
                            ->where('isdel', 0);
                    })
                    ->orWhere(function ($query) use ($my_aid, $user_id) {
                        $query
                            ->where('post_user_id', $user_id)
                            ->where('get_user_id', $my_aid)
                            ->where('isdel', 0);
                    })
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            }
            foreach ($data as &$tem) {
                $tem->msg = Base::removeImgAlt($tem->msg);
            }
            $this->getAllChatHeadimage($type, $data);
        } else if ($type == ChatBase::$type_group_chat_name) {
            // 群聊
            $is_exies = Db::table('groupuser')
                ->where('user_id', $my_aid)
                ->where('group_id', $user_id)
                ->where('isdel', 0)
                ->exists();
            if (!$is_exies) {
                return json(['code' => -1, 'msg' => '您没有加入该群！']);
            }
            if ($msg_id > 0) {
                $data = Db::table('groupchat')
                    ->where('id', '<', $msg_id)
                    ->where('post_user_id', $my_aid)
                    ->where('get_user_id', $user_id)
                    ->where('isdel', 0)
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $data = Db::table('groupchat')
                    ->where('post_user_id', $my_aid)
                    ->where('get_user_id', $user_id)
                    ->where('isdel', 0)
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            }
            foreach ($data as &$tem) {
                $tem->msg = Base::removeImgAlt($tem->msg);
            }
            $this->getAllChatHeadimage($type, $data);
        } else {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        Base::dataToSafe($data, true);
        return json(['code' => 1, 'msg' => '加载成功', 'data' => $data]);
    }

    /**
     * 获取全部聊天头像，用户名(data为orm从数据库中获得的数据)
     * @param Request $request 请求
     * @param array $data
     */
    public function getAllChatHeadimage(&$type, &$data)
    {
        if (!$type || !$data || empty($data)) {
            Base::dataToSafe($data, true);
            return;
        }
        if ($type == ChatBase::$type_private_chat_name) {
            $get_user_id = 0;
            $post_user_id = 0;
            foreach ($data as &$t) {
                $get_user_id = $t->get_user_id;
                $post_user_id = $t->post_user_id;
                break;
            }
            $post_user_db = Base::getUserData($post_user_id);
            $get_user_db = Base::getUserData($get_user_id);
            foreach ($data as &$t) {
                // 谁发的消息显示谁的信息
                if ($post_user_db && $t->post_user_id == $post_user_id) {
                    $t->headimage = $post_user_db->headimage;
                    $t->name = $post_user_db->name;
                    $t->msgtype = $type;
                    $t->type = $type;
                }
                if ($get_user_db && $t->post_user_id == $get_user_id) {
                    $t->headimage = $get_user_db->headimage;
                    $t->name = $get_user_db->name;
                    $t->msgtype = $type;
                    $t->type = $type;
                }
            }
        } else if ($type == ChatBase::$type_group_chat_name) {
            foreach ($data as &$t) {
                $db = Base::getUserData($t->get_user_id);
                if ($db) {
                    $t->headimage = $db->headimage;
                    $t->name = $db->name;
                    $t->msgtype = $type;
                    $t->type = $type;
                }
            }
        }
    }

    /**
     * 获取最新消息
     * @param Request $request 请求
     * @return string $res json
     */
    public function getLatestChatData(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $type = $request->post('type');
        $msg_uid = $request->post('msg_id');
        $msg_id = Base::getIdByUid($msg_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$type || !$user_uid || !$user_id) {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        $limit = Chat::$chat_list_limit;
        $data = [];

        if ($type == ChatBase::$type_private_chat_name) {
            // 私聊
            $db = Base::getUserData($user_id);
            if (empty($db)) {
                return json(['code' => -1, 'msg' => Base::$param_error_msg]);
            }
            if ($msg_id > 0) {
                // 获取新消息的数目
                $num = Db::table('privatechat')
                    ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                        $query
                            ->where('post_user_id', $my_aid)
                            ->where('get_user_id', $user_id)
                            ->where('id', '>', $msg_id)
                            ->where('isdel', 0);
                    })
                    ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                        $query
                            ->where('post_user_id', $user_id)
                            ->where('get_user_id', $my_aid)
                            ->where('id', '>', $msg_id)
                            ->where('isdel', 0);
                    })
                    ->count();
                if ($num > $limit) {
                    //太多新消息 则 告诉前端删除旧缓存， 保存该缓存数据
                    $data = Db::table('privatechat')
                        ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                            $query
                                ->where('post_user_id', $my_aid)
                                ->where('get_user_id', $user_id)
                                ->where('id', '>', $msg_id)
                                ->where('isdel', 0);
                        })
                        ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                            $query
                                ->where('post_user_id', $user_id)
                                ->where('get_user_id', $my_aid)
                                ->where('id', '>', $msg_id)
                                ->where('isdel', 0);
                        })
                        ->orderBy('id', 'desc')
                        ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                        ->limit($limit)
                        ->get();
                    foreach ($data as &$tem) {
                        $tem->msg = Base::removeImgAlt($tem->msg);
                    }
                    $this->getAllChatHeadimage($type, $data);
                    Base::dataToSafe($data, true);
                    return json([
                        'code' => 0,
                        'msg' => '新数据过多，删除旧数据，缓存新数据',
                        'data' => $data
                    ]);
                } else {
                    // 新消息不太多 则 告诉前端缓存
                    $data = Db::table('privatechat')
                        ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                            $query
                                ->where('post_user_id', $my_aid)
                                ->where('get_user_id', $user_id)
                                ->where('id', '>', $msg_id)
                                ->where('isdel', 0);
                        })
                        ->orWhere(function ($query) use ($my_aid, $user_id, $msg_id) {
                            $query
                                ->where('post_user_id', $user_id)
                                ->where('get_user_id', $my_aid)
                                ->where('id', '>', $msg_id)
                                ->where('isdel', 0);
                        })
                        ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                        ->orderBy('id', 'desc')
                        ->get();
                }
            } else {
                // 没有msg_id , 缓存最新数据
                $data = Db::table('privatechat')
                    ->orWhere(function ($query) use ($my_aid, $user_id) {
                        $query
                            ->where('post_user_id', $my_aid)
                            ->where('get_user_id', $user_id)
                            ->where('isdel', 0);
                    })
                    ->orWhere(function ($query) use ($my_aid, $user_id) {
                        $query
                            ->where('post_user_id', $user_id)
                            ->where('get_user_id', $my_aid)
                            ->where('isdel', 0);
                    })
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            }
        } else if ($type == ChatBase::$type_group_chat_name) {
            $db = Db::table('groupuser')
                ->where('user_id', $my_aid)
                ->where('group_id', $user_id)
                ->where('isdel', 0)
                ->exists();
            if (!$db) {
                return json(['code' => -1, 'msg' => '您没有加入该群！']);
            }
            if ($msg_id > 0) {
                $num = Db::table('groupchat')
                    ->where('post_user_id', $user_id)
                    ->where('id', '>', $msg_id)
                    ->where('isdel', 0)
                    ->count();

                if ($num > $limit) {
                    // 新消息太多 则 告诉前端 删除旧缓存 存储该数据
                    $data = Db::table('groupchat')
                        ->where('post_user_id', $user_id)
                        ->where('id', '>', $msg_id)
                        ->where('isdel', 0)
                        ->orderBy('id', 'desc')
                        ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                        ->limit($limit)
                        ->get();
                    foreach ($data as &$tem) {
                        $tem->msg = Base::removeImgAlt($tem->msg);
                    }
                    $this->getAllChatHeadimage($type, $data);
                    Base::dataToSafe($data, true);
                    return json([
                        'code' => 0,
                        'msg' => '新数据过多，删除旧数据，缓存新数据',
                        'data' => $data
                    ]);
                } else {
                    // 新消息不多 则 告诉前端 缓存该数据
                    $data = Db::table('groupchat')
                        ->where('post_user_id', $user_id)
                        ->where('id', '>', $msg_id)
                        ->where('isdel', 0)
                        ->orderBy('id', 'desc')
                        ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                        ->get();
                }
            } else {
                // 没有msg_id , 缓存最新数据
                $data = Db::table('groupchat')
                    ->where('post_user_id', $user_id)
                    ->where('isdel', 0)
                    ->orderBy('id', 'desc')
                    ->select('id', 'post_user_id', 'get_user_id', 'msg', 'time')
                    ->limit($limit)
                    ->get();
            }
        } else {
            return json(['code' => -1, 'msg' => Base::$param_error_msg]);
        }
        foreach ($data as &$tem) {
            $tem->msg = Base::removeImgAlt($tem->msg);
        }
        $this->getAllChatHeadimage($type, $data);
        Base::dataToSafe($data, true);
        return json(['code' => 1, 'msg' => '加载成功', 'data' => $data]);
    }

    /**
     * 聊天界面查找用户和群聊
     * @param Request $request 请求
     * @return string $res json
     */
    public function ChatFindUser(Request $request)
    {
        $key = $request->post('key');
        $limit = Chat::$chat_user_limit;
        $info = [];
        $info_user = [];
        $info_group = [];
        if (!$key) {
            $info_user = Db::table('user')
                ->where('isdel', 0)
                ->select('id', 'name', 'headimage')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
            $info_group = Db::table('group')
                ->where('isdel', 0)
                ->select('id', 'name', 'headimage')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        } else {
            $info_user = Db::table('user')
                ->where('name', $key)
                ->where('isdel', 0)
                ->select('id', 'name', 'headimage')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
            $info_group = Db::table('group')
                ->where('name', $key)
                ->where('isdel', 0)
                ->select('id', 'name', 'headimage')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        }

        foreach ($info_user as &$tem) {
            $tem->type = ChatBase::$type_private_chat_name;
            $tem->no_look_num = 0;
            if (Gateway::isUidOnline($tem->id) || $tem->name == '机器人') {
                $tem->online = 1;
            } else {
                $tem->online = 0;
            }
            $info[] = $tem;
        }

        foreach ($info_group as &$tem) {
            $tem->type = ChatBase::$type_group_chat_name;
            $tem->no_look_num = 0;
            $tem->online = 1;
            $info[] = $tem;
        }
        Base::dataToSafe($info, true);
        return json(['code' => 1, 'data' => $info, 'msg' => "查找完成，最多显示 $limit * 2 个结果"]);
    }

    /**
     * 判断用户是否加群
     * @param Request $request 请求
     * @return string $res json
     */
    public function judgeIsJoinGroup(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $group_uid = $request->post('group_id');
        $group_id = Base::getIdByUid($group_uid);
        $db = Db::table('groupuser')
            ->where('user_id', $my_aid)
            ->where('group_id', $group_id)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            $db = Db::table('group')
                ->where('id', $group_id)
                ->where('isdel', 0)
                ->first();
            Base::dataToSafe($db, true);
            if ($db) {
                return json(['code' => 0, 'msg' => '您没有加入该群！', 'data' => $db]);
            }
            return json(['code' => -1, 'msg' => '没有该群！']);
        }
        return json(['code' => 1, 'msg' => '您已经加入该群！']);
    }

    /**
     * 添加用户对话
     */
    public function addUserChat(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        $has = Db::table('privateuser')
            ->where('get_user_id', $my_aid)
            ->where('post_user_id', $user_id)
            ->where('isdel', 0)
            ->exists();
        if (!$has) {
            Base::insertToDb('privateuser', [
                'get_user_id' => $my_aid,
                'post_user_id' => $user_id
            ]);
        }
        return json(['code' => 1, 'msg' => '操作成功']);
    }

    /**
     * 移除用户对话
     */
    public function removeUserChat(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        Db::table('privateuser')
            ->where('get_user_id', $my_aid)
            ->where('post_user_id', $user_id)
            ->where('isdel', 0)
            ->update([
                'isdel' => 1
            ]);
        return json(['code' => 1, 'msg' => '操作成功']);
    }

    /**
     * 获取全部列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getUserAndGroupList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $res = [];
        // 获取有记录用户列表
        $user_chat = Db::table('privateuser')
            ->where('get_user_id', $my_aid)
            ->where('isdel', 0)
            ->distinct()
            ->select('post_user_id', 'get_user_id')
            ->distinct()
            ->get();
        $redis16 = Redis::connection('db16');
        $redis17 = Redis::connection('db17');
        $user_set = new \stdClass;
        foreach ($user_chat as &$tem) {
            $tem_db = Base::getUserData($tem->post_user_id);
            if ($tem_db) {
                Base::removeUserUnSafeData($tem_db);
                $tem_userid = $tem->post_user_id;
                if (!isset($user_set->$tem_userid)) {
                    $user_set->$tem_userid = true;
                    $name = $tem->post_user_id . 'TO' . $my_aid;
                    $tem_db->type = ChatBase::$type_private_chat_name;
                    $no_look_num = $redis16->get($name);
                    $tem_db->no_look_num = $no_look_num ? $no_look_num : 0;
                    if (Gateway::isUidOnline($tem->post_user_id) || $tem_db->name == '机器人') {
                        $tem_db->online = 1;
                    } else {
                        $tem_db->online = 0;
                    }
                    $res[] = $tem_db;
                }
            }
            $tem_db = Base::getUserData($tem->get_user_id);
            if ($tem_db) {
                Base::removeUserUnSafeData($tem_db);
                $tem_userid = $tem->get_user_id;
                if (!isset($user_set->$tem_userid)) {
                    $user_set->$tem_userid = true;
                    $name = $tem->get_user_id . 'TO' . $my_aid;
                    $tem_db->type = ChatBase::$type_private_chat_name;
                    $no_look_num = $redis16->get($name);
                    $tem_db->no_look_num = $no_look_num ? $no_look_num : 0;
                    if (Gateway::isUidOnline($tem->get_user_id) || $tem_db->name == '机器人') {
                        $tem_db->online = 1;
                    } else {
                        $tem_db->online = 0;
                    }
                    $res[] = $tem_db;
                }
            }
        }

        // 获取有记录的群列表
        $group_chat = Db::table('groupuser')
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->select('group_id')
            ->distinct()
            ->get();
        foreach ($group_chat as &$tem) {
            $tem_db = Db::table('group')
                ->where('id', $tem->group_id)
                ->where('isdel', 0)
                ->select('id', 'name', 'code', 'headimage', 'creatorid', 'time')
                ->first();
            if ($tem_db) {
                $name = $tem->group_id . 'TO' . $my_aid;
                $tem_db->type = ChatBase::$type_group_chat_name;
                $no_look_num = $redis17->get($name);
                $tem_db->no_look_num = $no_look_num ? $no_look_num : 0;
                $tem_db->group_data = [
                    'id' => $tem_db->id,
                    'name' => $tem_db->name,
                    'code' => $tem_db->code,
                    'headimage' => $tem_db->headimage,
                    'creatorid' => $tem_db->creatorid,
                    'time' => $tem_db->time,
                ];
                $tem_db->online = 1;
                $res[] = $tem_db;
            }
        }
        Base::dataToSafe($res, true);
        return json(['code' => 1, 'msg' => '加载完成', 'data' => $res]);
    }

    /**
     * 已阅消息
     * @param Request $request 请求
     */
    public function clearNolookNum(Request $request)
    {
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        $type = $request->post('type');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if ($type == 'private_chat') {
            $redis16 = Redis::connection('db16');
            $name = $user_id . 'TO' . $my_aid;
            $redis16->del($name, 0);
        } else if ($type == 'group_chat') {
            $redis17 = Redis::connection('db17');
            $name = $user_id . 'TO' . $my_aid;
            $redis17->del($name, 0);
        }
    }

    /**
     * 加载群成员列表
     * @param Request $request 请求
     */
    public function getGroupUserList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $group_uid = $request->post('group_id');
        $group_id = Base::getIdByUid($group_uid);
        $group_db = Db::table('group')
            ->where('id', $group_id)
            ->where('isdel', 0)
            ->exists();
        if (!$group_db) {
            return json(['code' => -1, 'msg' => '该群聊不存在', 'data' => []]);
        }
        $group_db = Db::table('groupuser')
            ->where('group_id', $group_id)
            ->where('user_id', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if (!$group_db) {
            return json(['code' => -1, 'msg' => '您未进群', 'data' => []]);
        }
        $user = Db::table('groupuser')
            ->where('group_id', $group_id)
            ->where('isdel', 0)
            ->select('user_id', 'grade')
            ->get();
        $res = [];
        foreach ($user as &$tem) {
            $tem_user = Base::getUserData($tem->user_id);
            Base::removeUserUnSafeData($tem_user);
            if ($tem_user) {
                $tem_user->id = $tem->user_id;
                $tem_user->grade = $tem->grade;
                if (Gateway::isUidOnline($tem_user->id) || $tem_user->name == '机器人') {
                    $tem->online = 1;
                } else {
                    $tem->online = 0;
                }
                $res[] = $tem_user;
            }
        }
        Base::dataToSafe($res, true);
        return json(['code' => 1, 'msg' => '加载完成', 'data' => $res]);
    }
}
