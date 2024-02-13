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
use support\Db;
use support\Redis;
use app\controller\Robot;

class DeleteContest implements Consumer
{
    // 要消费的队列名
    public $queue = 'delete_contest';

    // 消费
    public function consume($data)
    {
        try {
            $contest_id = $data['contest_id'] ?? '';
            while (1) {
                try {
                    //删除竞赛
                    $db = Db::table('contest')
                        ->where('id', $contest_id)
                        ->where('isdel', 0)
                        ->update(['isdel' => 1]);

                    //删除竞赛缓存信息
                    $redis4 = Redis::connection('db4');
                    $redis4->del('Contest' . $contest_id . 'resarray');
                    $redis4->del('ContestRank' . $contest_id . 'echartsrank');
                    $redis4->del('ContestRank' . $contest_id . 'peopledata');
                    $redis4->del('ContestRank' . $contest_id . 'timedata');
                    $redis4->del('Contest' . $contest_id . 'problemIndex');
                    $redis4->del('Contest' . $contest_id . 'HtmlRank');

                    //竞赛删除清空该竞赛全部用户缓存
                    $joinuser = Db::table('joincontest')
                        ->where('contestid', $contest_id)
                        ->where('isdel', 0)
                        ->select('userid')
                        ->get();
                    foreach ($joinuser as &$tem) {
                        $redis4->del('Contest' . $contest_id . 'problemdata' . $tem->userid);
                    }

                    //删除该竞赛题目
                    Db::table('contestproblem')
                        ->where('contestid', $contest_id)
                        ->where('isdel', 0)
                        ->update(['isdel' => 1]);

                    //删除该竞赛所有提交记录
                    Db::table('contestrank')
                        ->where('contestid', $contest_id)
                        ->where('isdel', 0)
                        ->update(['isdel' => 1]);

                    //删除该竞赛参加用户
                    Db::table('joincontest')
                        ->where('contestid', $contest_id)
                        ->where('isdel', 0)
                        ->update(['isdel' => 1]);
                    Base::updateContestDataRedis($contest_id);

                    // 删除机器人完成竞赛缓存
                    $redis27 = Redis::connection('db27');
                    $key = Base::$robot_contest_redis_front . $contest_id;
                    $redis27->del($key);
                    // 删除查重缓存
                    $redis31 = Redis::connection('db31');
                    $key = Base::$contest_similarity_id_redis_front . $contest_id;
                    $redis_key = $redis31->get($key);
                    if ($redis_key) {
                        $redis31->del($key);
                    }
                    // 删除ContestRank代码缓存
                    $redis29 = Redis::connection('db29');
                    $redis30 = Redis::connection('db30');
                    $old_id_list = $redis30->get(Base::$redis_contest_code_list_key_name . $contest_id);
                    if ($old_id_list) {
                        try {
                            $old_id_list = json_decode($old_id_list, true);
                        } catch (Exception $e) {
                            $old_id_list = [];
                        }
                        foreach ($old_id_list as &$tem_one_old) {
                            $redis29->del($tem_one_old[0]);
                        }
                    }
                    $redis30->del(Base::$redis_contest_code_list_key_name . $contest_id);
                    if ($db) {
                        $title = 'DeleteContest消息队列运行结果';
                        $content = '竞赛ID为' . $contest_id . '的竞赛已删除！';
                        Robot::sendChatToOneUserMsg(Base::getRootId(), '<h4>' . $title . "</h4>\n" . $content);
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        } catch (Exception $e) {
            $title = 'DeleteContest消息队列异常';
            $content = $e->getMessage();
            Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), '<h4>' . $title . "</h4>\n" . $content);
        }
    }
}
