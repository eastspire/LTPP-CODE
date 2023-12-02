<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-07-24 00:41:02
 * @FilePath: \LTPP-CODE\app\queue\redis\SendMail.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use Exception;
use Webman\RedisQueue\Consumer;

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
            \app\controller\Email::mailto($to, $title, $content);
        } catch (Exception $e) {
            return;
        }
    }
}
