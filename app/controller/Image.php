<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-03-27 14:16:42
 * @FilePath: \LTPP\app\controller\Image.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use support\Db;

class Image
{
    static $image_db_key = [
        'id',
        'url'
    ];

    /**
     * 随机一张图片
     * @param Request $request 请求
     * @return string $res json
     */
    static public function randImage()
    {

        $table = Db::table('image')
            ->where('isdel', 0)
            ->select('url')
            ->inRandomOrder()
            ->first();
        if (!$table) {
            return '';
        }
        return $table->url;
    }
}
;