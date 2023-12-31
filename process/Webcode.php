<?php

namespace process;

use app\controller\Robot;
use Workerman\Crontab\Crontab;
use app\controller\Base;
use app\controller\Codehistory;
use Exception;
use support\Db;
use Webman\RedisQueue\Redis as RedisQueue;

class Webcode
{
    static $user_list = [];

    static $user_num = 1000;

    static $user_idx = 0;

    /**
     * 获取用户数组
     */
    private function getUserList()
    {
        Webcode::$user_list = Db::table('user')
            ->where('email', Base::$robot_email)
            ->where('isdel', 0)
            ->limit(Webcode::$user_num)
            ->pluck('id')
            ->toArray();
    }

    /**
     * 获取一个用户
     */
    private function getOneUser()
    {
        if (!Webcode::$user_list || !is_array(Webcode::$user_list) || !sizeof(Webcode::$user_list)) {
            $this->getUserList();
        }
        if (Webcode::$user_idx < 0 || Webcode::$user_idx >= sizeof(Webcode::$user_list)) {
            Webcode::$user_idx = 0;
        }
        return Webcode::$user_list[Webcode::$user_idx];
    }

    /**
     * 获取一个代码
     * @param int $user_id
     * @param string $userlanguage
     */
    private function getOneCode($user_id, $userlanguage = 'C++')
    {
        $db = Db::table('codehistory')
            ->where('language', $userlanguage)
            ->where('isdel', 0)
            ->orderBy('id', 'asc')
            ->select('code')
            ->first();
        if (!$db) {
            $db = Db::table('codehistory')
                ->where('status', '!=', Base::$code_up_waiting)
                ->where('status', '!=', Base::$code_up_running)
                ->where('isdel', 0)
                ->orderBy('id', 'asc')
                ->select(Codehistory::$code_history_db_key_has_code)
                ->first();
            if (!$db) {
                return '';
            }
            Base::insertToDb('codehistory', [
                'userid' => $user_id,
                'status' => $db->status,
                'time' => date('Y-m-d H:i:s', time()),
                'usetime' => $db->usetime,
                'usememory' => $db->usememory,
                'code' => $db->code,
                'language' => $db->language,
                'contestid' => $db->contestid,
                'problemid' => $db->problemid
            ]);
        }
        if ($db) {
            return $db->code;
        }
        return '';
    }

    /**
     * 获取一个语言
     */
    private function getOneLanguage()
    {
        $len = sizeof(Base::$oj_judge_language);
        $idx = rand(0, $len - 1);
        if ($idx < 0) {
            return 'C++';
        }
        return Base::$oj_judge_language[$idx];
    }

    /**
     * 运行任务
     */
    private function runTask()
    {
        if (!Base::judgeJudgeInstall()) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【Webcode】** 判题机检测异常：判题机未安装！');
            return;
        }
        $testin = '';
        $userlanguage = $this->getOneLanguage();
        $my_aid = $this->getOneUser();
        $code = $this->getOneCode($my_aid, $userlanguage);
        //代码检测
        $check_safe_json = Base::judgeCodeSafe($code, $userlanguage);
        if (!isset($check_safe_json['code']) || $check_safe_json['code'] != 1) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【Webcode】** 用户代码校验未通过：' . json($check_safe_json));
            return;
        }
        $code_id = Base::insertToDb('codehistory', [
            'userid' => $my_aid,
            'status' => Base::$code_up_waiting,
            'time' => date('Y-m-d H:i:s', time()),
            'usetime' => 0,
            'usememory' => 0,
            'code' => $code,
            'language' => $userlanguage,
            'contestid' => 0,
            'problemid' => 0
        ]);
        if ($code_id) {
            // 发送给消息队列
            RedisQueue::send(Base::$redis_queue_webcode_run_name, [
                'my_aid' => $my_aid,
                'code_id' => $code_id,
                'code' => $code,
                'userlanguage' => $userlanguage,
                'testin' => $testin
            ]);
        }
    }

    public function onWorkerStart()
    {
        // 每秒执行一次
        new Crontab('*/1 * * * * *', function () {
            try {
                $this->runTask();
            } catch (Exception $e) {
                Robot::sendChatToOneUserMsg(Base::getRootId(), '定时任务进程 **【Webcode】** 运行错误：' . $e->getMessage());
            }
        });
    }
}
