<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 11:23:37
 * @FilePath: \LTPP-CODE\app\queue\redis\UpdateCode.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
use app\controller\Robot;
use Exception;
use support\Db;
use Webman\RedisQueue\Consumer;

class UpdateCode implements Consumer
{
    // 要消费的队列名
    public $queue = 'update_code';

    // 消费
    public function consume($data)
    {
        try {
            $code_id = $data['code_id'] ?? 0;
            $code_data = $data['code_data'] ?? [];
            if (!$code_id || !$code_data) {
                return;
            }
            Db::table('codehistory')
                ->where('id', $code_id)
                ->update($code_data);
            Base::updateCodeDataRedis($code_id);
        } catch (Exception $e) {
            $title = 'UpdateCode消息队列异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsg(Base::getRootId(), '#### ' . $title . "\n" . $content);
        }
    }
}
