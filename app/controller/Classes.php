<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-03-16 20:10:51
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-21 14:48:11
 * @FilePath: \LTPP-CODE\app\controller\Classes.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\controller;


use support\Request;
use support\Db;

class Classes
{
    /**
     * 数量上限
     */
    static $limit = 50;

    /**
     * 关键字搜索
     */
    public function keySearch(Request $request)
    {
        $key = $request->post('key');
        $limit = 50;
        $db = Db::table('class')
            ->where('name', 'like', '%' . $key . '%')
            ->select('id', 'name')
            ->limit($limit)
            ->orderBy('id', 'asc')
            ->get();
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db]);
    }
}
