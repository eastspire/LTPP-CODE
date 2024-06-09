<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-14 18:26:54
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-28 12:10:32
 * @FilePath: \LTPP-CODE\plugin\webman\gateway\GlobalNotice.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace plugin\webman\gateway;

use Exception;
use app\controller\Base;
use GatewayWorker\Lib\Gateway;

class GlobalNotice extends ChatBase
{
    static public function globalNotice(&$client_id, &$message, &$db_my, &$db_user)
    {
        $send_msg = '来自LTPP用户' . $db_my->name . '的提醒：' . "\n" . $message->msg;
        $now = date('Y-m-d H:i:s', time());
        try {
            Gateway::sendToAll(json_encode([
                'msgtype' => 'notice',
                'name' => $db_my->name,
                'msg' => $send_msg,
                'time' => $now
            ]));
        } catch (Exception $e) {
            ChatBase::sendToOneError($client_id, '系统错误');
            Base::sendErrorNotice($e->getTraceAsString(), '全局通知下发错误：' . $e->getMessage());
            return;
        }
    }
}
