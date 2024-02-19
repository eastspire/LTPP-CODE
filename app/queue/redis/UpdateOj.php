<?php
/*
 * @Author: SQS 1491579574@qq.com
 * @Date: 2023-06-02 11:58:18
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-12-30 11:24:07
 * @FilePath: \LTPP-CODE\app\queue\redis\UpdateOj.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\queue\redis;

use app\controller\Base;
use Exception;
use support\Db;
use Webman\RedisQueue\Consumer;

class UpdateOj implements Consumer
{
    // 要消费的队列名
    public $queue = 'update_oj';

    // 消费
    public function consume($data)
    {
        try {
            $problem_id = $data['problem_id'] ?? 0;
            $problem_data = $data['problem_data'] ?? [];
            $is_ac = $data['is_ac'] ?? false;
            if (!$problem_id) {
                return;
            }
            // 缓存中的数据（总数已经正常）
            $db = Base::getOjData($problem_id);
            if ($is_ac) {
                Db::table('oj')
                    ->where('id', $problem_id)
                    ->where('isdel', 0)
                    ->increment('ACNum', 1);
                $problem_data['ACpoint'] = round((float) ($db->ACNum + 1) / ((float) $db->ALLSubmitNum), 2);
            } else {
                $problem_data['ACpoint'] = round((float) $db->ACNum / ((float) $db->ALLSubmitNum), 2);
            }
            Db::table('oj')
                ->where('id', $problem_id)
                ->update($problem_data);
            Base::updateOjDataRedis($problem_id);
        } catch (Exception $e) {
            $title = 'UpdateOj消息队列异常';
            $content = $e->getMessage();
            Base::sendErrorNotice($e->getTraceAsString(), '<h4>' . $title . "</h4>\n" . $content);
        }
    }
}
