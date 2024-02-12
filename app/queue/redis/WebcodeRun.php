<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-02 22:31:21
 * @FilePath: \LTPP-CODE\app\queue\redis\WebcodeRun.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
use app\controller\Robot;
use app\controller\Webcode;
use Exception;
use Webman\RedisQueue\Consumer;

class WebcodeRun implements Consumer
{
    // 要消费的队列名
    public $queue = 'webcode_run';

    // 消费
    public function consume($data)
    {
        try {
            $my_aid = $data['my_aid'] ?? 0;
            $code_id = $data['code_id'] ?? 0;
            $code = $data['code'] ?? '';
            $userlanguage = $data['userlanguage'] ?? 'C++';
            $testin = $data['testin'] ?? '';
            if (!$my_aid || !$code_id || !$userlanguage) {
                return;
            }
            $json = Webcode::run($my_aid, $code_id, $code, $userlanguage, $testin);
            Base::saveCodeJson($code_id, $json);
        } catch (Exception $e) {
            $title = 'WebcodeRun消息队列异常';
            $content = $e->getMessage();
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '#### ' . $title . "\n" . $content);
        }
    }
}
