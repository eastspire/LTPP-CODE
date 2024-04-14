<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
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
            Robot::sendChatToOneUserMsg(Base::getRootId(), '<h4>异步请求结果</h4><br><pre style="white-space:pre-wrap;word-wrap:break-word;">'
                . $res . '</pre>');
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }
}
