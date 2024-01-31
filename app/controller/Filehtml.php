<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-10-02 19:29:04
 * @FilePath: \LTPP-CODE\app\controller\Filehtml.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use Exception;

class Filehtml
{
    /**
     * 查看页面效果
     * @return string $rescode 内容
     */
    public function lookView()
    {
        try {
            $path = request()->input('path');
            $path = Base::Base64Decode($path);
            $txt = Base::getStaticFileData($path);
            $iscode = false;
            $language = 'cpp';
            foreach (Base::$map_language_file as $key => $val) {
                $iscode = str_ends_with($path, $key);
                if ($iscode) {
                    $language = $val;
                    break;
                }
            }
            // 对于HTML代码文件不拦截
            if ($iscode && $language != 'html') {
                return Base::codeToHTML($txt, $language);
            }
            if (str_ends_with($path, 'md')) {
                return Base::markdownToHTML($txt);
            }
            return Base::strToHTML($txt);
        } catch (Exception $e) {
        }
        return Base::notFoundPage();
    }
}
