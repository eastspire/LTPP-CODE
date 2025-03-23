<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-03-11 21:41:57
 * @FilePath: \LTPP\app\controller\Dayproblem.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Dayproblem extends Oj
{
    /**
     * 获取每日一题的题目列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getDayproblemList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $db = Db::table('dayproblem')
            ->where('isdel', 0)
            ->select('problemid', 'time')
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();

        $res = array();

        foreach ($db as &$tem) {
            $tdb = Db::table('oj')
                ->where('id', $tem->problemid)
                ->where('isdel', 0)
                ->select(OJ::$oj_list_db_key)
                ->first();
            //防止题目不存在或已经删除，不存在就不进行添加
            if ($tdb) {
                $s = '';
                for ($i = 0; $i < 10; $i++) {
                    $s .= $tem->time[$i];
                }
                $tdb->time = $s;
                $hassolve = Db::table('solveproblem')
                    ->where('userid', $my_aid)
                    ->where('problemid', $tdb->id)
                    ->where('isdel', 0)
                    ->exists();
                if ($hassolve) {
                    $tdb->hassolve = 1;
                } else {
                    $tdb->hassolve = 0;
                }
                $res[] = $tdb;
            }
        }
        $allnum = Db::table('dayproblem')
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($res);
        return json(['code' => 1, 'msg' => '加载成功', 'data' => $res, 'allnum' => $allnum]);
    }
}
;
