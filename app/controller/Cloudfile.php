<?php

namespace app\controller;

use support\Request;
use Tinywan\Jwt\JwtToken;
use support\Db;

class Cloudfile
{
    /**
     * @var array $unsupport 不支持的文件类型
     */
    static public $unsupport = [
        'xlsx' => true,
        'exe' => true,
        'docx' => true,
        'mp4' => true,
        'avi' => true,
        'rmvb' => true,
        '3gp' => true,
        'mpeg' => true,
        'wmv' => true,
        'mov' => true,
        'mpv' => true,
        'flv' => true,
        'swf' => true,
        'rar' => true,
        'zip' => true,
        'tar' => true,
        'gz' => true,
        'tar.gz' => true,
        '7z' => true,
        'apz' => true,
        'ar' => true,
        'bz' => true,
        'car' => true,
        'dar' => true,
        'cpgz' => true,
        'f' => true,
        'ha' => true,
        'hbc' => true,
        'hbc2' => true,
        'hbe' => true,
        'hpk' => true,
        'hyp' => true,
        'mp3' => true,
        'aac' => true,
        'ac3' => true,
        'mp3adu' => true,
        'mp3adufloat' => true,
        'mp3float' => true,
        'mp3on4' => true,
        'mp3on4float' => true,
        'amrnb' => true,
        'amrwb' => true,
        'cook' => true,
        'ra_144' => true,
        'ra_288' => true,
        'sipr' => true,
        'wmav1' => true,
        'wmav2' => true,
        'wmavoice' => true,
        'wmapro' => true,
        'wamlossless' => true,
        'nellymoser' => true,
        'vorbis' => true,
        'jpg' => true,
        'png' => true,
        'jpeg' => true,
        'gif' => true,
        'svg' => true,
        'bmp' => true,
        'tif' => true,
        'pcx' => true,
        'tga' => true,
        'exif' => true,
        'fpx' => true,
        'psd' => true,
        'cdr' => true,
        'pcd' => true,
        'dxf' => true,
        'ufo' => true,
        'eps' => true,
        'ai' => true,
        'raw' => true,
        'WMF' => true,
        'webp' => true,
        'avif' => true,
        'apng' => true,
        'm3u8' => true
    ];

    /**
     * @var array $pgoto 图片的文件类型
     */
    static public $photo = [
        'jpg',
        'png',
        'jpeg',
        'gif',
        'svg',
        'bmp',
        'tif',
        'pcx',
        'tga',
        'exif',
        'fpx',
        'psd',
        'cdr',
        'pcd',
        'dxf',
        'ufo',
        'eps',
        'ai',
        'raw',
        'WMF',
        'webp',
        'avif',
        'apng'
    ];

    /**
     * 创建初始文件
     * @param string $path 文件路径
     */
    static public function creatFile($my_aid)
    {
        try {
            $data = Base::getSettingKeyData('cloud_file_readme_txt');
            $file_name = Base::getSettingKeyData('cloud_file_readme_txt_file_name');
            $file_size = mb_strlen($data, Base::$str_encoding);
            $new_path = Base::creatFilePath(Base::getDbFileExtion($file_name));
            $id = Base::insertToDb('file_data', [
                'data' => $data
            ]);
            Base::insertToDb('file_path', [
                'path' => $new_path,
                'file_id' => $id,
                'userid' => $my_aid,
                'time' => date('Y-m-d H:i:s', time())
            ]);
            Base::insertToDb('cloud_file_path', [
                'name' => $file_name,
                'path' => $new_path,
                'file_id' => $id,
                'userid' => $my_aid,
                'time' => date('Y-m-d H:i:s', time()),
                'size' => $file_size
            ]);
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
        } catch (\Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '**【creatFile】** 运行错误：' . $e->getMessage());
        }
    }

    /**
     * 返回字符集
     */
    public function loadCharset(Request $request)
    {
        return json(['code' => 1, 'msg' => '加载成功！', 'data' => Base::$char_set]);
    }

    /**
     * 加载文件
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        if (!$my_aid || $user_id == '') {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        $path_list = Db::table('cloud_file_path')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->select('name', 'path', 'time', 'size')
            ->get();
        $res = [];
        foreach ($path_list as &$one_data) {
            $temarray = [];
            $temarray[] = Base::Base64Encode($one_data->name);
            $path = Base::Base64Encode($one_data->path);
            $file_extion = Base::getDbFileExtion($one_data->path);
            $temarray[] = Base::fileExtionToNumberType($file_extion);
            $size = $one_data->size;
            Base::getChineseSize($size);
            $temarray[] = $size;
            $temarray[] = $one_data->time;
            $temarray[] = $path;
            $res[] = $temarray;
        }
        if (empty($res)) {
            return json(['code' => -1, 'msg' => '文件为空']);
        }
        return json(['code' => 1, 'msg' => '文件获取成功', 'data' => $res]);
    }

    /**
     * 查看一个文件内容
     * @param Request $request 请求
     * @return string $res json
     */
    public function lookCode(Request $request)
    {
        $path = $request->post('path');
        $path = Base::Base64Decode($path);
        if (isset(Cloudfile::$unsupport[Base::getDbFileExtion($path)])) {
            return json(['code' => -1, 'msg' => '该格式不支持访问']);
        }
        $data = Base::getStaticFileData($path);
        return json(['code' => 1, 'msg' => '文件获取成功', 'data' => $data]);
    }

    /**
     * 删除文件
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteFile(Request $request)
    {
        $path = $request->post('path');
        $path = Base::Base64Decode($path);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Base::deleteCloudFileData($my_aid, $path);
        return json(['code' => 1, 'msg' => '操作成功']);
    }

    /**
     * 更新代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function updataCode(Request $request)
    {
        $path = $request->post('path');
        $path = Base::Base64Decode($path);
        $code = $request->post('code');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        Base::updateCloudFileData($my_aid, $path, $code);
        return json(['code' => 1, 'msg' => '更新成功']);
    }

    /**
     * 获取普通用户cloudfile容量大小限制
     */
    protected function getAllFileSizeLimit()
    {
        $usercloudfilememory = Base::getSettingKeyData('usercloudfilememory');
        if (!$usercloudfilememory) {
            $usercloudfilememory = 50;
        }
        // 换算成字节
        $usercloudfilememory = $usercloudfilememory * 1024 * 1024;
        return $usercloudfilememory;
    }

    /**
     * 文件大小验证
     * @param string $my_uid 用户my_uid
     * @param string $path 文件路径
     * @return int $code code为1表示通过验证，0表示未通过验证
     */
    protected function judgeFileBig($my_aid, $file)
    {
        if (Base::judgeIsRoot($my_aid)) {
            return 1;
        }
        $db = Db::table('cloud_file_path')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select('size')
            ->get();
        $size = $file->getSize();
        foreach ($db as &$tem) {
            $size += (int)$tem->size;
        }
        $all = $this->getAllFileSizeLimit();
        if ($size > $all) {
            return 0;
        }
        return 1;
    }

    /**
     * 文件上传
     * @param Request $request 请求
     * @return string $res json
     */
    public function upFile(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $file = $request->file('file');
        // 容量验证
        if ($this->judgeFileBig($my_aid, $file) != 1) {
            return json(['code' => -1, 'msg' => '您的剩余容量不足！']);
        }
        Base::uploadCloudFileToDb($my_aid, $file);
        return json(['code' => 1, 'msg' => '上传成功']);
    }

    /**
     * 文件下载
     * @param Request $request 请求
     * @return string $res json
     */
    public function downloadFile(Request $request)
    {
        $path = $request->post('path');
        $path = Base::Base64Decode($path);
        if (!$path) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }
        $file_data = Base::getStaticFileData($path);
        $file_extion = Base::getDbFileExtion($path);
        return Response($file_data, 200, [
            'Content-Type' => Base::getContentType($file_extion),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($file_data),
            'File-Path' => $path,
            'File-Extion' => $file_extion,
            'File-Content-Type' => Base::getContentType($file_extion),
        ]);
    }

    /**
     * 新建文件
     * @param Request $request 请求
     * @return string $res json
     */
    public function newFile(Request $request)
    {

        $file_name = $request->post('name');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (Base::getStrLen($file_name) > Base::$file_name_length_limit) {
            return json(['code' => -1, 'msg' => '文件名字符数不能超过' . Base::$file_name_length_limit . '个']);
        }
        $data = '';
        $new_path = Base::creatFilePath(Base::getDbFileExtion($file_name));
        $id = Base::insertToDb('file_data', [
            'data' => $data
        ]);
        Base::insertToDb('file_path', [
            'path' => $new_path,
            'file_id' => $id,
            'userid' => $my_aid,
            'time' => date('Y-m-d H:i:s', time())
        ]);
        Base::insertToDb('cloud_file_path', [
            'name' => $file_name,
            'path' => $new_path,
            'file_id' => $id,
            'userid' => $my_aid,
            'time' => date('Y-m-d H:i:s', time()),
            'size' => 0
        ]);
        return json(['code' => 1, 'msg' => '创建成功！']);
    }

    /**
     * 获取已使用容量百分比
     * @return string $res json 
     */
    public function getcloudfilePercentage()
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('cloud_file_path')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->select('size')
            ->get();
        $size = 0;
        foreach ($db as &$tem) {
            $size += (int)$tem->size;
        }
        $all = $this->getAllFileSizeLimit();
        if ($all <= 0) {
            return json(['code' => 1, 'data' => 0]);
        }
        $percentage = $size / $all * 100;

        if (Base::judgeIsRoot($my_aid)) {
            $percentage = Base::getLinuxCanUseDiskSize() / Base::getLinuxAllDiskSize() * 100;
        }

        if ($percentage > 100) {
            $percentage = 100;
        }
        return json(['code' => 1, 'data' => $percentage]);
    }
}
