<?php

namespace app\middleware;

use app\controller\Robot;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;
use Exception;
use support\Redis;
use app\controller\Base;

class AuthCheckTest extends Robot implements MiddlewareInterface
{
    /**
     * 公开方法
     */
    static $safe_func = [
        'judgeLogin',
        'judgeRegister',
        'lookView',
        'getMusicBkurl',
        'send',
        'sendPassword',
        'getVersion',
        'getClassUrl',
        'getSocketUrl',
        'getBackUrl',
        'getFrontUrl',
        'getMusicBkUrl',
        'publicContestRank',
        'loadCharset',
        'oneArticle',
        'lookContestProblemCode'
    ];

    /**
     * 私有方法
     */
    static $danger_func = [
        'contestIdGetRankEcharts',
        'contestIdGetRank'
    ];

    /**
     * 私有
     */
    static $danger_path = [
        'app\controller\Base',
        'app\controller\Robot',
        'app\controller\ChatBase',
        'app\controller\Robot',
        'app\controller\Image',
        'app\controller\Ssh',
        'plugin\webman\gateway\Events',
        'plugin\webman\gateway\ChatBase',
        'plugin\webman\gateway\ClassMsg',
        'plugin\webman\gateway\GlobalNotice',
        'plugin\webman\gateway\GroupChat',
        'plugin\webman\gateway\PrivateChat',
        'plugin\webman\gateway\PrivateRobot',
    ];

    /**
     * 内网IP
     */
    static $safe_ip = '127.0.0.1';

    public function process(Request $request, callable $handler): Response
    {
        //判断是否需要鉴权
        $func = $request->action;
        foreach (AuthCheckTest::$safe_func as &$tem) {
            if ($func === $tem) {
                return $handler($request);
            }
        }
        // 禁止访问的内容
        foreach (AuthCheckTest::$danger_func as &$tem) {
            if ($func === $tem) {
                return response(Base::notFoundPage(), 404);
            }
        }
        foreach (AuthCheckTest::$danger_path as &$tem) {
            if ($request->controller === $tem) {
                return response(Base::notFoundPage(), 404);
            }
        }

        //鉴权
        //获取authorization
        $header = $request->header();
        //判断authorization是否存在或为空
        if (!isset($header['authorization']) || empty($header['authorization'])) {
            return json(['code' => 500, 'msg' => '非法访问！']);
        }
        // 判断是否有单点登录信息
        if (!isset($header['key']) || empty($header['key'])) {
            return json(['code' => 500, 'msg' => '登录信息错误！请重新登录！']);
        }
        $my_uid = '';
        try {
            $my_uid = JwtToken::getCurrentId();
        } catch (Exception $e) {
            return \json(['code' => 500, 'msg' => '您已下线！请重新登录！']);
        }
        $my_aid = Base::getIdByUid($my_uid);
        $redis0 = Redis::connection('db0');
        $redis14 = Redis::connection('db14');
        $loc = $request->getRealIp($safe_mode = true);
        $onekey = $header['key'];
        // 判断单点登录
        if ($onekey != $redis14->get($my_aid . 'login')) {
            return \json(['code' => 500, 'msg' => '您已下线！请重新登录！']);
        }

        // 是root用户直接放行，不限速
        $root_id = Base::getRootId();

        if ($my_aid == $root_id) {
            return $handler($request);
        }

        if ($loc != AuthCheckTest::$safe_ip) {
            // 不是内网记录频率
            if ($redis0->get('BlackIP' . $loc) || $redis0->get('BlackID' . $my_aid)) {
                return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
            } else {
                $black_aid_db = Db::table('blackip')
                    ->where('user_id', $my_aid)
                    ->where('isdel', 0)
                    ->exists();
                if ($black_aid_db) {
                    $redis0->set('BlackID' . $my_aid, 1);
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
                }
                $black_ip_db = Db::table('blackip')
                    ->where('ip', $loc)
                    ->where('isdel', 0)
                    ->exists();
                if ($black_ip_db) {
                    $redis0->set('BlackIP' . $loc, 1);
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
                }
            }
        }

        $GLOBiplimit = Base::getSettingKeyData('GLOBiplimit');
        $GLOBiplimitTime = Base::getSettingKeyData('GLOBiplimitTime');
        $GLOBipblack = Base::getSettingKeyData('GLOBipblack');

        $redis1 = Redis::connection('db1');
        $redisip = 'ip' . $loc . 'id' . $my_aid;
        if ($redis1->get($redisip)) {
            $requestnum = $redis1->get($redisip);
            //频率过快超过限制先拉黑处理
            if ($requestnum >= $GLOBipblack) {
                $isblack = Db::table('blackip')
                    ->where('user_id', $my_aid)
                    ->where('ip', $loc)
                    ->where('isdel', 0)
                    ->exists();
                if (!$isblack) {
                    $user_db = Base::getUserData($my_aid);
                    $msg = '';
                    $now = date('Y-m-d H:i:s', time());
                    if (!$user_db) {
                        $msg = '非法用户（伪造id：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已拉黑，可在设置中删除该用户黑名单';
                    } else {
                        $msg = '用户' . $user_db->name . '（id：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已拉黑，可在设置中删除该用户黑名单';
                    }
                    Robot::sendChatToOneUserMsg($root_id, $msg);
                    Db::table('blackip')->insert([
                        'user_id' => $my_aid,
                        'ip' => $loc
                    ]);
                }
                $redis1->set('BlackIP' . $loc, 1);
                return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！']);
            } else {
                //频率过快，屏蔽
                if ($requestnum >= $GLOBiplimit) {
                    if ($requestnum == $GLOBiplimit) {
                        // 通知一次即可
                        $user_db = Base::getUserData($my_aid);
                        $msg = '';
                        $now = date('Y-m-d H:i:s', time());
                        if (!$user_db) {
                            $msg = '非法用户（伪造ID：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已对该用户限速处理';
                        } else {
                            $msg = '用户' . $user_db->name . '（ID：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已对该用户限速处理';
                        }
                        Robot::sendChatToOneUserMsg($root_id, $msg);
                    }
                    return \json(['code' => 400, 'msg' => '系统检测到访问异常！已拒绝该请求！']);
                }
                //自增
                $redis1->incr($redisip);
            }
        } else {
            //不存在就插入
            $redis1->setEx($redisip, $GLOBiplimitTime, 1);
        }
        // 若果想终止执行Action就直接返回Response对象，不想终止则无需return
        // return response('终止执行Action');
        // 请求继续穿越
        return $handler($request);
    }
}