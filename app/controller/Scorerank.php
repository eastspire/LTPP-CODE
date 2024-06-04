<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: ltpp-universe 1491579574@qq.com
 * @LastEditTime: 2024-01-07 16:49:22
 * @FilePath: \LTPP-CODE\app\controller\Scorerank.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use support\Db;

class Scorerank
{
    /**
     * @var array $rank_db_key 数据库排名展示字段
     */
    static $rank_db_key = [
        'id',
        'name',
        'acnum',
        'fans',
        'lastlogin',
        'headimage',
        'email'
    ];

    /**
     * 获取排名列表
     * @param Request $request 请求
     * @return string $res json
     */
    public function getRankList(Request $request)
    {
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $userdb = Db::table('user')
            ->where('isdel', 0)
            ->select(Scorerank::$rank_db_key)
            ->orderBy('acnum', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $res = array();

        $i = ($page - 1) * $limit + 1;
        foreach ($userdb as &$tem) {
            $tem->index = $i++;
            $res[] = $tem;
        }
        $allnum = Db::table('user')
            ->where('isdel', 0)
            ->count();
        Base::userOnline($userdb, true, true, true);
        Base::dataToSafe($res);
        return json(['data' => $res, 'allnum' => $allnum]);
    }
};
