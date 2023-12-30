<?php
/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-26 12:20:24
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-21 14:54:09
 * @FilePath: \LTPP-CODE\app\controller\Chatfile.php
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;

class Chatfile
{
    /**
     * 获取聊天文件根目录
     * @param string $my_aid 我的ID
     * @param string $user_aid 另一个用户或者群聊的UID
     * @param string $res 聊天根目录名称
     * @param bool $is_private = true 是否是私聊
     */
    static private function getChatFileRoot($my_uid, $user_uid, $is_private = true)
    {
        if (!$my_uid || !$user_uid) {
            return [];
        }
        $my_md5_path = Base::getPathMd5($my_uid);
        $user_md5_path = Base::getPathMd5($user_uid);
        if ($is_private) {
            // 私聊存储路径
            if (Base::getIdByUid($my_uid) == Base::getIdByUid($user_uid)) {
                $chat_path = ['PRIVATE' . $my_md5_path . $user_md5_path];
            } else {
                $chat_path = ['PRIVATE' . $my_md5_path . $user_md5_path, 'PRIVATE' . $user_md5_path . $my_md5_path];
            }
        } else {
            $chat_path = ['GROUP' . Base::getPathMd5($user_uid)];
        }
        return $chat_path;
    }

    /**
     * 文件上传
     * @param Request $request 请求
     * @param bool $is_private = true 是否是私聊
     * @return string $res json
     */
    public function upFile(Request $request, $is_private = true)
    {
        $my_uid = JwtToken::getCurrentId();
        $postpath = $request->post('path');
        if (!$postpath || $postpath == '') {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        $isexist = strripos($postpath, '..');
        if ($isexist !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $file = $request->file('file');
        if (!$file->isValid()) {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }
        $chat_path_list = Chatfile::getChatFileRoot($my_uid, $postpath, $is_private);
        $chat_path = $chat_path_list[0];
        $savepath = Base::$LTPP_public_static_path . '/chatfile/' . $chat_path;
        // 容量验证
        if (!$this->judgeFileBig($chat_path, $file)) {
            return json(['code' => -1, 'msg' => '云文件剩余容量不足！']);
        }
        $up_full_name = $file->getUploadName();
        if (Base::getStrLen($up_full_name) > Base::$file_name_length_limit) {
            return json(['code' => -1, 'msg' => '文件名字符数不能超过' . Base::$file_name_length_limit . '个']);
        }
        Base::judgeCreatPath($savepath);
        $name_json = Base::first_last_name($up_full_name);
        $first_last_json = json_decode($name_json);
        $first_name = $first_last_json->first_name;
        $last_name = $first_last_json->last_name;
        $name = Base::Base64Encode($first_name) . $last_name;
        $file->move($savepath . '/' . $name);
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return json(['code' => 1, 'msg' => '上传成功', 'filename' => $name]);
    }

    /**
     * 私聊文件上传
     * @param Request $request 请求
     * @return string $res json
     */
    public function privateUpFile(Request $request)
    {
        return $this->upFile($request, true);
    }

    /**
     * 群聊文件上传
     * @param Request $request 请求
     * @return string $res json
     */
    public function groupUpFile(Request $request)
    {
        return $this->upFile($request, false);
    }

    /**
     * 文件大小验证
     * @param string $path 用户path
     * @param string $path 文件路径
     * @return int $code code为1表示通过验证，0表示未通过验证
     */
    protected function judgeFileBig($path, $file)
    {
        // 换算成字节
        $userchatfilememory = $this->getAllFileSizeLimit();
        $tempath = Base::$LTPP_public_static_path . '/chatfile/' . $path;
        if (!file_exists($tempath)) {
            return true;
        }
        if (is_dir($tempath)) {
            $size = Base::getFileSize($tempath);
        } else {
            $size = filesize($tempath);
        }
        $size += $file->getSize();
        if ($size > $userchatfilememory) {
            Base::deleteAllFile($file->getRealPath());
            return false;
        }
        return true;
    }

    /**
     * 加载文件夹和文件
     * @param Request $request 请求
     * @param bool $is_private = true 是否是私聊
     * @return string $res json
     */
    public function loadList(Request $request)
    {
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        $is_private = $request->post('is_private');
        if (!$postpath) {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $chat_path_list = Chatfile::getChatFileRoot($my_uid, $postpath, $is_private);
        $res = array();
        foreach ($chat_path_list as &$chat_path) {
            $path = Base::$LTPP_public_static_path . '/chatfile/' . $chat_path;
            Base::judgeCreatPath($path);
            if (!is_dir($path)) {
                continue;
            }
            $dirs = scandir($path);
            foreach ($dirs as &$tem) {
                if ($tem == '.' || $tem == '..') {
                    continue;
                }
                $temarray = array();
                $tempath = $path . '/' . $tem;
                $tem = str_replace($path, '', $tem);
                $temarray[] = $tem;
                $end = '';
                $len = strlen($tem);
                for ($i = $len - 1; $i >= 0; --$i) {
                    if ($tem[$i] == '.') {
                        break;
                    }
                    $end = $tem[$i] . $end;
                }
                if ($end == $tem && is_dir($path . '/' . $tem) === true) {
                    //文件夹为1
                    $temarray[] = 1;
                } else {
                    // music为2
                    // video为3
                    // code为4
                    // pdf为5
                    // 压缩包为6
                    // 图片为7
                    // 应用程序为8
                    // 其他文件为9
                    $ispush = false;
                    if (!$ispush) {
                        foreach (Cloudfile::$code as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 4;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        foreach (Cloudfile::$photo as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 7;
                                $ispush = true;
                                break;
                            }
                        }
                    }

                    if (!$ispush) {
                        foreach (Cloudfile::$pdf as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 5;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        foreach (Cloudfile::$compressed as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 6;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        foreach (Cloudfile::$video as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 3;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        foreach (Cloudfile::$music as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 2;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        foreach (Cloudfile::$exe as $ttem) {
                            if ($ttem == $end) {
                                $temarray[] = 8;
                                $ispush = true;
                                break;
                            }
                        }
                    }
                    if (!$ispush) {
                        $temarray[] = 9;
                    }
                }
                $time = date('Y-m-d H:i:s', filemtime($tempath));
                if (is_dir($tempath)) {
                    $size = Base::getFileSize($tempath);
                } else {
                    $size = filesize($tempath);
                }

                if ($size < 1024) {
                    $size .= 'B';
                } else if ($size < 1048576) {
                    $size = sprintf("%.1f", $size / 1024);
                    $size .= 'KB';
                } else if ($size < 1073741824) {
                    $size = sprintf("%.1f", $size / 1048576);
                    $size .= 'MB';
                } else {
                    $size = sprintf("%.1f", $size / 1073741824);
                    $size .= 'GB';
                }
                $temarray[] = $size;
                $temarray[] = $time;
                $res[] = $temarray;
            }
        }
        if (empty($dirs)) {
            return json(['code' => -1, 'msg' => '文件为空']);
        }
        return json(['code' => 1, 'msg' => '文件获取成功', 'data' => $res]);
    }

    /**
     * 文件下载
     * @param Request $request 请求
     * @return string $res json
     */
    public function downloadFile(Request $request)
    {
        $postpath = $request->post('path');
        $name = $request->post('name');
        $my_uid = JwtToken::getCurrentId();
        $is_private = $request->post('is_private');
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $chat_path_list = Chatfile::getChatFileRoot($my_uid, $postpath, $is_private);
        foreach ($chat_path_list as &$chat_path) {
            $path = Base::$LTPP_public_static_path . '/chatfile/' . $chat_path . '/' . $name;
            //判断文件是否存在
            if (file_exists($path)) {
                //原始文件名称
                $name = '';
                for ($i = strlen($path) - 1; $i >= 0; --$i) {
                    if ($path[$i] == '/') {
                        break;
                    }
                    $name = $path[$i] . $name;
                }
                if ($name == '') {
                    $name = md5($my_uid . time());
                }
                //以只读和二进制模式打开文件
                return response('')->download($path);
            }
        }
        return json(['code' => -1, 'msg' => '文件不存在']);
    }

    /**
     * 获取聊天云文件容量大小限制
     */
    protected function getAllFileSizeLimit()
    {
        $userchatfilememory = Base::$chat_file_size_limit;
        // 换算成字节
        $userchatfilememory = $userchatfilememory * 1024 * 1024;
        return $userchatfilememory;
    }

    /**
     * 获取已使用容量百分比
     * @return string $res json 
     */
    public function getCloudfilePercentage(Request $request)
    {
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        $is_private = $request->post('is_private');
        if (!$postpath || $postpath == '') {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $chat_path_list = Chatfile::getChatFileRoot($my_uid, $postpath, $is_private);
        $chat_path = $chat_path_list[0];
        $path = Base::$LTPP_public_static_path . '/chatfile/' . $chat_path;
        Base::judgeCreatPath($path);
        $size = Base::getFileSize($path);
        $all = $this->getAllFileSizeLimit();
        if ($all <= 0) {
            return json(['code' => 1, 'data' => 0]);
        }

        $percentage = $size / $all * 100;

        if ($percentage > 100) {
            $percentage = 100;
        }
        return json(['code' => 1, 'data' => $percentage]);
    }
};
