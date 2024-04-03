<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use Webman\RedisQueue\Redis as RedisQueue;

class Webcode
{
    /**
     * 提交代码
     */
    public function runCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!$my_aid) {
            return json(['code' => -1, 'code_id' => '', 'msg' => '请登录']);
        }
        if (!Base::judgeJudgeInstall()) {
            return json(['code' => -1, 'code_id' => '', 'msg' => '判题机未安装']);
        }
        $code = $request->post('code');
        $testin = $request->post('testin');
        $userlanguage = $request->post('userlanguage');
        //代码检测
        $check_safe_json = Base::judgeCodeSafe($code, $userlanguage);
        if (!isset($check_safe_json['code']) || $check_safe_json['code'] != 1) {
            return json($check_safe_json);
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
            $code_uid = Base::getUidById($code_id);
            return json(['code' => 1, 'code_id' => $code_uid, 'msg' => Base::$code_up_success_msg]);
        }
        return json(['code' => -1, 'code_id' => '', 'msg' => Base::$code_up_fail_msg]);
    }

    /**
     * 查询代码
     */
    public function queryCode(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!$my_aid) {
            return json(['code' => -1, 'result' => '请登录', 'usememory' => 0, 'usetime' => 0]);
        }
        $code_uid = $request->post('code_id');
        $code_id = Base::getIdByUid($code_uid);
        $code_db = Base::getCodeData($code_id);
        if (!$code_db) {
            return json(['code' => -1, 'result' => '代码不存在', 'usememory' => 0, 'usetime' => 0]);
        }
        if ($code_db->userid != $my_aid) {
            return json(['code' => -1, 'result' => '用户身份错误', 'usememory' => 0, 'usetime' => 0]);
        }
        $json = Base::getCodeJson($code_id);
        if ($json) {
            Base::deleteCodeCache($my_aid, $code_id);
            return json($json);
        }
        // 这里code得是0，状态只能从缓存读取信息
        return json(['code' => 0, 'result' => $code_db->status, 'usememory' => $code_db->usememory, 'usetime' => $code_db->usetime]);
    }

    /**
     * 更新状态
     */
    static private function updateCodeStatus($code_id = 0, $status = '', $time_used = 0, $memory_used = 0)
    {
        if (!$code_id || !$status) {
            return;
        }
        $code_data = [
            'status' => $status,
            'usetime' => $time_used,
            'usememory' => $memory_used,
        ];
        RedisQueue::send(Base::$redis_queue_update_code_name, [
            'code_id' => $code_id,
            'code_data' => $code_data
        ]);
    }

    /**
     * 代码运行
     * @param int $my_aid
     * @param int $code_id
     * @param string $code
     * @param string $userlanguage
     * @param string $testin
     * @return array $json
     */
    static public function run($my_aid = 0, $code_id = 0, $code = '', $userlanguage = 'C++', $testin = '')
    {
        Webcode::updateCodeStatus($code_id, Base::$code_up_running, 0, 0);
        if (!$my_aid || !$code_id || !Base::getCodeData($code_id)) {
            return ['code' => -1, 'result' => '参数错误', 'usememory' => 0, 'usetime' => 0];
        }
        $limittime = (int) Base::getSettingKeyData('idemaxtime');
        $limitmemory = ((int) Base::getSettingKeyData('idemaxmemory') ?? 0) << 20;
        $dir_res = Base::creatCodeRunDirFile($my_aid, $testin);
        $mainfile = $dir_res['mainfile'];
        $filepath = $dir_res['filepath'];
        $runcodefilepath = $dir_res['runcodefilepath'];
        $inpath = $dir_res['inpath'];
        $outpath = $dir_res['outpath'];
        $errpath = $dir_res['errpath'];
        // 写入文件
        $compiler_res_json = Base::writeCodeToFile($userlanguage, $code, $filepath, $runcodefilepath);
        if (!isset($compiler_res_json['code']) || $compiler_res_json['code'] != 1) {
            return json($compiler_res_json);
        }
        $out = '';
        // 运行
        $out = Base::run($userlanguage, $filepath, $inpath, $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory);

        $run_resource_consumption = Base::getCodeTimeMemory($out);

        if (!$run_resource_consumption || !isset($run_resource_consumption['status'])) {
            Base::deleteAllFile($filepath);
            Webcode::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
            return ['code' => -1, 'result' => Base::$judge_error_msg . '！', 'usememory' => 0, 'usetime' => 0];
        }
        $status = $run_resource_consumption['status'] ?? 0;
        $time_used = $run_resource_consumption['time_used'] ?? 0;
        $memory_used = $run_resource_consumption['memory_used'] ?? 0;
        $msg = $run_resource_consumption['msg'] ?? '';
        Base::deleteAllFile($filepath);
        // 去除路径信息
        $msg = Base::removeMsgSandboxPath($filepath, $msg);
        switch ($status) {
            case Base::$judge_code_compiler_error:
                Webcode::updateCodeStatus($code_id, Base::$code_run_compiler_wrong, $time_used, $memory_used);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ];
            case Base::$judge_server_error:
                Webcode::updateCodeStatus($code_id, Base::$code_run_running_wrong, 0, 0);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usememory' => $time_used,
                    'usetime' => $memory_used
                ];
            case Base::$judge_code_error:
                Webcode::updateCodeStatus($code_id, Base::$code_run_running_wrong, $time_used, $memory_used);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ];
            case Base::$judge_code_tle:
                Webcode::updateCodeStatus($code_id, Base::$code_run_tle, $time_used, $memory_used);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ];
            case Base::$judge_code_mle:
                Webcode::updateCodeStatus($code_id, Base::$code_run_mle, $time_used, $memory_used);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ];
            case Base::$judge_code_re:
                Webcode::updateCodeStatus($code_id, Base::$code_run_re, $time_used, $memory_used);
                return [
                    'code' => -1,
                    'result' => $msg,
                    'usetime' => $time_used,
                    'usememory' => $memory_used
                ];
            default:
                Webcode::updateCodeStatus($code_id, Base::$code_run_success, $time_used, $memory_used);
                break;
        }
        return ['code' => 1, 'result' => $msg, 'usetime' => $time_used, 'usememory' => $memory_used];
    }
}
