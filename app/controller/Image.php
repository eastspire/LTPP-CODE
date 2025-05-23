<?php
/*
 * @Author: 1491579574@qq.com
 * @Date: 2023-02-28 22:41:18
 * @LastEditors: 1491579574@qq.com
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
        // 无图片，生成图片成功
        if (!$table && Image::getImageList()) {
            $table = Db::table('image')
                ->where('isdel', 0)
                ->select('url')
                ->inRandomOrder()
                ->first();
        }
        if (!$table) {
            return '';
        }
        return $table->url;
    }

    /**
     * 有图片直接返回URL列表，无图片，生成图片返回URL列表
     * @return array res 图片url列表
     */
    static public function getImageList()
    {
        $image_list = Db::table('image')
            ->where('isdel', 0)
            ->pluck('url')
            ->toArray();
        if (!$image_list) {
            $testpath = Base::$LTPP_public_path . Base::$LTPP_public_static_path . '/dbimage/';
            // 清空数据库
            Db::table('image')
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            $data = [];
            $root_id = Base::getRootId();
            foreach (Cloudfile::$photo as &$t_img) {
                $file = glob($testpath . '*.' . $t_img);
                foreach ($file as &$tem) {
                    $path = Base::creatFilePath($t_img);
                    $id = Base::insertToDb(Base::getFileDataTableName($path), [
                        'data' => file_get_contents(realpath($tem)),
                    ]);
                    if (!$id) {
                        continue;
                    }
                    Base::insertToDb(Base::getFilePathTableName($path), [
                        'path' => $path,
                        'file_id' => $id,
                        'userid' => $root_id,
                        'time' => date('Y-m-d H:i:s', time())
                    ]);
                    $data[] = [
                        'url' => Base::$GLOBlinuxurl . $path
                    ];
                    if (sizeof($data) % 888 == 0) {
                        Db::table('image')->insert($data);
                        $data = [];
                    }
                }
                if (sizeof($data) >= 0) {
                    Db::table('image')->insert($data);
                    $data = [];
                }
            }
            $image_list = Db::table('image')
                ->where('isdel', 0)
                ->pluck('url')
                ->toArray();
        }
        return $image_list;
    }
};
