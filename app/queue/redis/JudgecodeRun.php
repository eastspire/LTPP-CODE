<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-16 18:00:48
 * @FilePath: \LTPP-CODE\app\queue\redis\JudgecodeRun.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */
namespace app\queue\redis;

use app\controller\Base;
use app\controller\Ojjudge;
use app\controller\Robot;
use Exception;
use support\Db;
use Webman\RedisQueue\Consumer;

class JudgecodeRun implements Consumer
{
    // 要消费的队列名
    public $queue = 'judgecode_run';

    // 消费
    public function consume($data)
    {
        try {
            $my_aid = $data['my_aid'] ?? 0;
            $code_id = $data['code_id'] ?? 0;
            $code = $data['code'] ?? '';
            $userlanguage = $data['userlanguage'] ?? 'C++';
            $contest_id = $data['contest_id'];
            $problem_id = $data['problem_id'];
            if (!$my_aid || !$problem_id || !$code || !$userlanguage) {
                return;
            }
            Db::table('oj')
                ->where('id', $problem_id)
                ->where('isdel', 0)
                ->increment('ALLSubmitNum', 1);
            Base::updateOjDataRedis($problem_id);
            $json = Ojjudge::run($my_aid, $code_id, $code, $userlanguage, $problem_id, $contest_id);
            Base::saveCodeJson($code_id, $json);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), 'JudgecodeRun消息队列运行出错:' . $e->getMessage());
            return;
        }
    }
}