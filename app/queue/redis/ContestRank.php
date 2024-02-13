<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 11:20:54
 * @FilePath: \LTPP-CODE\app\queue\redis\ContestRank.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use Exception;
use Webman\RedisQueue\Consumer;
use app\controller\Base;
use app\controller\Contest;

class ContestRank implements Consumer
{
    // 要消费的队列名
    public $queue = 'contest_rank';

    // 消费
    public function consume($data)
    {
        try {
            $contest_id = $data['contest_id'] ?? 0;
            Contest::contestIdGetRankEcharts($contest_id);
            Contest::contestIdGetRank($contest_id);
        } catch (Exception $e) {
            $title = 'ContestRank消息队列异常';
            $content = $e->getMessage();
            Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), '<h4>' . $title . "</h4>\n" . $content);
        }
    }
}
