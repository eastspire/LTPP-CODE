<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: eastspire 1491579574@qq.com
 * @LastEditTime: 2023-12-30 13:30:51
 * @FilePath: \LTPP-CODE\app\queue\redis\Monitor.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use Exception;
use Webman\RedisQueue\Consumer;
use app\controller\Base;

class Monitor implements Consumer
{
    // 要消费的队列名
    public $queue = 'monitor';

    // 消费
    public function consume($data)
    {
        try {
            $path = $data['path'] ?? '';
            $function = $data['function'] ?? '';
            $user_id = Base::getIdByUid($data['user_uid'] ?? '');
            Base::insertToDb('monitor', [
                'path' => $path,
                'function' => $function,
                'userid' => $user_id
            ]);
        } catch (Exception $e) {
            $title = 'Monitor消息队列异常';
            $content = $e->getMessage();
            Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . "</h4>\n\n" . $content);
        }
    }
}
