<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-22 18:30:18
 * @FilePath: \LTPP-CODE\app\queue\redis\ContestRank.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */
namespace app\queue\redis;

use Exception;
use Webman\RedisQueue\Consumer;
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
            Contest::contestIdGetRank($contest_id, true);
            Contest::contestIdGetRankEcharts($contest_id);
        } catch (Exception $e) {
            return;
        }
    }
}