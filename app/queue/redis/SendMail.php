<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 11:22:39
 * @FilePath: \LTPP-CODE\app\queue\redis\SendMail.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use Exception;
use Webman\RedisQueue\Consumer;
use app\controller\Base;
use app\controller\Email;

class SendMail implements Consumer
{
    // 要消费的队列名
    public $queue = 'send_mail';

    // 消费
    public function consume($data)
    {
        try {
            $to = $data['to'] ?? '';
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            Email::mailto($to, $title, $content);
        } catch (Exception $e) {
            $title = 'SendMail消息队列异常';
            $content = $e->getMessage();
            Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . "</h4>\n\n" . $content);
        }
    }
}
