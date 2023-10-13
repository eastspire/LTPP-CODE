<?php

namespace app\controller;


use support\Request;
use support\Db;


class Subject
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
        $db = Db::table('subject')
            ->where('name', 'like', '%' . $key . '%')
            ->select('id', 'name')
            ->limit($limit)
            ->get();
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db]);
    }
}