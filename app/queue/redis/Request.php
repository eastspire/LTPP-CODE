<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: ltpp-universe 1491579574@qq.com
 * @LastEditTime: 2023-11-13 14:38:46
 * @FilePath: \LTPP-CODE\app\queue\redis\BuySsh.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
use app\controller\Robot;
use Exception;
use Webman\RedisQueue\Consumer;

class Request implements Consumer
{
    // 要消费的队列名
    public $queue = 'request';

    // 消费
    public function consume($data)
    {
        try {
            $user_id = $data['user_id'] ?? 0;
            $url =  $data['url'] ?? '';
            $is_post =  $data['is_post'] ?? false;
            $data =  $data['data'] ?? [];
            $header =  $data['header'] ?? [];
            $body_type_is_json  = $data['body_type_is_json'] ?? false;
            if ($is_post) {
                $res = Base::postRequest($url, $header, $data, $body_type_is_json);
            } else {
                $res = Base::getRequest($url, $header);
            }
            $user_db = Base::getUserData($user_id);
            $user_name = Base::$unknow_user_name;
            if ($user_db) {
                $user_name = $user_db->name;
            }
            Robot::sendChatToOneUserMsg($user_id, '<h4>用户【' . $user_name . '】发起异步请求结果</h4><br><pre style="white-space:pre-wrap;word-wrap:break-word;font-size: 1.06rem;">'
                . $res . '</pre>');
            if (!Base::judgeIsRoot($user_id)) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '<h4>用户【' . $user_name . '】发起异步请求结果</h4><br><pre style="white-space:pre-wrap;word-wrap:break-word;font-size: 1.06rem;">'
                    . $res . '</pre>');
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }
}
