<?php

namespace app\controller;

use Exception;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Cloudfile
{
    /**
     * cloudfile文件夹路径
     * @var string $cloudfile_root_path cloudfile文件夹路径
     */
    static $cloudfile_root_path = 'static/cloudfile/';

    /**
     * @var array $unsupport 不支持的文件类型
     */
    static public $unsupport = array(
        '.xlsx',
        '.exe',
        '.docx',
        '.mp4',
        '.avi',
        '.rmvb',
        '.mp4',
        '.3gp',
        '.mpeg',
        '.wmv',
        '.mov',
        '.mpv',
        '.flv',
        '.swf',
        '.rar',
        '.zip',
        '.tar',
        '.gz',
        '.tar.gz',
        '.7z',
        '.apz',
        '.ar',
        '.bz',
        '.car',
        '.dar',
        '.cpgz',
        '.f',
        '.ha',
        '.hbc',
        '.hbc2',
        '.hbe',
        '.hpk',
        '.hyp',
        '.mp3',
        '.aac',
        '.ac3',
        '.mp3adu',
        '.mp3adufloat',
        '.mp3float',
        '.mp3on4',
        '.mp3on4float',
        '.amrnb',
        '.amrwb',
        '.cook',
        '.ra_144',
        '.ra_288',
        '.sipr',
        '.wmav1',
        '.wmav2',
        '.wmavoice',
        '.wmapro',
        '.wamlossless',
        '.nellymoser',
        '.vorbis',
        '.jpg',
        '.png',
        '.jpeg',
        '.gif',
        '.svg',
        '.bmp',
        '.tif',
        '.pcx',
        '.tga',
        '.exif',
        '.fpx',
        '.psd',
        '.cdr',
        '.pcd',
        '.dxf',
        '.ufo',
        '.eps',
        '.ai',
        '.raw',
        '.WMF',
        '.webp',
        '.avif',
        '.apng',
        '.m3u8'
    );

    /**
     * @var array $exe 应用的文件类型
     */
    static public $exe = [
        'exe',
        'apk',
        'bat',
        'ace',
        'app',
        'com'
    ];

    /**
     * @var array $video 视频的文件类型
     */
    static public $video = [
        'mp4',
        'avi',
        'rmvb',
        'mp4',
        '3gp',
        'mpeg',
        'wmv',
        'mov',
        'mpv',
        'flv',
        'swf'
    ];

    /**
     * @var array $compressed 压缩包的文件类型
     */
    static public $compressed = [
        'rar',
        'zip',
        'tar',
        'gz',
        'tar.gz',
        '7z',
        'apz',
        'ar',
        'bz',
        'car',
        'dar',
        'cpgz',
        'f',
        'ha',
        'hbcj',
        'hbc2j',
        'hbej',
        'hpkj',
        'hypj'
    ];

    /**
     * @var array $music 音乐的文件类型
     */
    static public $music = [
        'mp3',
        'aac',
        'ac3',
        'mp3adu',
        'mp3adufloat',
        'mp3float',
        'mp3on4',
        'mp3on4float',
        'amrnb',
        'amrwb',
        'cook',
        'ra_144',
        'ra_288',
        'sipr',
        'wmav1',
        'wmav2',
        'wmavoice',
        'wmapro',
        'wamlossless',
        'nellymoser',
        'vorbis',
        'm3u8'
    ];

    /**
     * @var array $code 代码的文件类型
     */
    static public $code = [
        'cpp',
        'c',
        'js',
        'html',
        'css',
        'php',
        'go',
        'java',
        'py',
        'ts',
        'json',
        'gitignore',
        'sh',
        'lock',
        'rs',
        'dart',
        'cs',
        'r'
    ];

    /**
     * @var array $pdf pdf的文件类型
     */
    static public $pdf = [
        'pdf'
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
     * 创建用户文件夹
     * @param string $path 文件路径
     */
    static public function creatFile($path)
    {
        Base::judgeCreatPath($path);
        $helptext = fopen($path . '/' . Base::Base64Encode('README') . '.txt', "w");
        $content = "LTPP在线开发平台许可协议\n
请务必认真阅读和理解本《LTPP在线开发平台许可协议》 ( 以下简称《协议》 ) 中规定的所有权利和限制。除非您接受本《协议》条款，否则您无权下载、安装或使用“LTPP”软件及其相关服务。您一旦安装、复制、下载、访问或以其它方式使用本软件产品，将视为对本《协议》的接受，即表示您同意接受本《协议》各项条款的约束。如果您不同意本《协议》中的条款，请不要安装、复制或使用本软件。\n
一 . 权利声明\n
“LTPP”由LTPP自主开发，LTPP软件的一切知识产权，以及与“LTPP”软件相关的所有信息内容，包括但不限于：文字表述及其组合、图标、图饰、图像、图表、色彩、界面设计、版面框架、有关数据、附加程序、印刷材料或电子文档等均为LTPP所有，受著作权法和国际著作权条约以及其他知识产权法律法规的保护。\n
二 . 许可范围\n
2.1 下载、安装和使用：本软件为免费下载使用，用户可以非商业性、无限制数量地下载、安装及使用本软件。\n
2.2 复制、分发和传播：用户可以非商业性、无限制数量地复制、分发和传播本软件产品。但必须保证每一份复制、分发和传播都是完整和真实的 , 包括所有有关本软件产品的软件、电子文档 , 版权和商标，亦包括本协议。\n
三 . 权利限制\n
3.1 禁止反向工程、反向编译和反向汇编：用户不得对本软件产品进行反向工程 (Reverse Engineer) 、反向编译 (Decompile) 或反向汇编 (Disassemble) ，同时不得改动编译在程序文件内部的任何资源。除法律、法规明文规定允许上述活动外，用户必须遵守此协议限制。\n
3.2 保留权利：本协议未明示授权的其他一切权利仍归盛强实所有，用户使用其他权利时必须获得盛强实的书面同意。\n
四 . 用户使用须知\n
4.1 本软件提供分为服务器端 agent 和 PC 控制台两部分，其中服务器端 agent 主要功能为服务器安全防护， PC 控制台主要功能为远程管理和监控。服务器端 agent 适用于 linux/windows 操作系统； PC 控制台支持 window XP 以上操作系统。如果用户在安装本软件后因任何原因欲放弃使用，可删除本软件。\n
4.2 本软件由盛强实提供产品支持。\n
4.3 软件的修改和升级：LTPP保留为用户提供本软件的修改、升级版本的权利。\n
4.4 用户应在遵守法律及本协议的前提下使用本软件。\n
4.4.1 不得故意避开或者破坏著作权人为保护本软件著作权而采取的技术措施 ;\n
4.4.2 用户不得利用本软件误导、欺骗他人 ;\n
4.4.3 违反国家规定，对计算机信息系统功能进行删除、修改、增加、干扰，造成计算机信息系统不能正常运行，\n
4.4.4 其他任何危害计算机信息网络安全的。\n
4.5 LTPP的唯一官网为 http://ltpp.vip , 对于从非LTPP指定站点下载的本软件产品以及从非LTPP发行的介质上获得的本软件产品，LTPP无法保证该软件是否具有风险，使用此类软件，将可能导致不可预测的风险，建议用户不要轻易下载、安装、使用，LTPP不承担任何由此产生的一切法律责任。\n
五 . 免责与责任限制\n
5.1 本软件经过详细的测试，但不能保证与所有的软硬件系统完全兼容，不能保证本软件完全没有错误。如果出现不兼容及软件错误的情况，用户可登录LTPP官网论坛、LTPP QQ 群将情况报告LTPP官方，获得技术支持。如果无法解决兼容性问题，用户可以删除本软件。\n
5.2 使用本软件产品风险由用户自行承担，在适用法律允许的最大范围内，对因使用或不能使用本软件所产生的损害及风险，包括但不限于直接或间接的个人损害、商业赢利的丧失、贸易中断、商业信息的丢失或任何其它经济损失，LTPP不承担任何责任。\n
5.3 对于因电信系统或互联网网络故障、计算机故障或病毒、信息损坏或丢失、计算机系统问题或其它任何不可抗力原因而产生损失，LTPP不承担任何责任。\n
5.4 用户违反本协议规定，对LTPP造成损害的。LTPP有权采取包括但不限于中断使用许可、停止提供服务、限制使用、法律追究等措施。\n
六 . 法律及争议解决\n
6.1 本协议适用中华人民共和国法律。\n
6.2 因本协议引起的或与本协议有关的任何争议，各方应友好协商解决 ; 协商不成的，任何一方均可将有关争议提交至北京仲裁委员会并按照其届时有效的仲裁规则仲裁 ; 仲裁裁决是终局的，对各方均有约束力。\n
七 . 其他条款\n
7.1 如果本协议中的任何条款无论因何种原因完全或部分无效或不具有执行力，或违反任何适用的法律，则该条款被视为删除，但本协议的其余条款仍应有效并且有约束力。\n
7.2 LTPP有权根据有关法律、法规的变化以及公司经营状况和经营策略的调整等修改本协议。修改后的协议会随附于新版本软件。当发生有关争议时，以最新的协议文本为准。如果不同意改动的内容，用户可以自行删除本软件。如果用户继续使用本软件，则视为您接受本协议的变动。\n
7.3 本协议的一切解释权与修改权归LTPP。\n";
        try {
            fwrite($helptext, $content);
            fclose($helptext);
        } catch (Exception $e) {
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
     * 加载文件夹和文件
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadList(Request $request)
    {
        $postpath = $request->post('path') ?? '';
        $my_uid = JwtToken::getCurrentId();
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        Base::judgeCreatPath($path);
        if (is_dir($path) != true) {
            return json(['code' => -1, 'msg' => '不是文件夹！']);
        }

        $dirs = scandir($path);
        $res = array();
        foreach ($dirs as &$tem) {
            if ($tem == '.' || $tem == '..')
                continue;
            $temarray = array();
            $tempath = $path . '/' . $tem;
            $tem = str_replace(Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid, "", $tem);
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
                    foreach (self::$code as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 4;
                            $ispush = true;
                            break;
                        }
                    }
                }
                if (!$ispush) {
                    foreach (self::$photo as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 7;
                            $ispush = true;
                            break;
                        }
                    }
                }

                if (!$ispush) {
                    foreach (self::$pdf as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 5;
                            $ispush = true;
                            break;
                        }
                    }
                }
                if (!$ispush) {
                    foreach (self::$compressed as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 6;
                            $ispush = true;
                            break;
                        }
                    }
                }
                if (!$ispush) {
                    foreach (self::$video as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 3;
                            $ispush = true;
                            break;
                        }
                    }
                }
                if (!$ispush) {
                    foreach (self::$music as $ttem) {
                        if ($ttem == $end) {
                            $temarray[] = 2;
                            $ispush = true;
                            break;
                        }
                    }
                }
                if (!$ispush) {
                    foreach (self::$exe as $ttem) {
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
            Base::getChineseSize($size);
            $temarray[] = $size;
            $temarray[] = $time;
            $res[] = $temarray;
        }
        if (empty($dirs)) {
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
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        $rescode = '';
        foreach (Cloudfile::$unsupport as &$tem) {
            //不支持的格式
            if (strpos($path, $tem) === false) {
            } else {
                return json(['code' => -1, 'msg' => '该格式不支持访问']);
            }
        }
        if (is_file($path)) {
            $rescode = file_get_contents($path);
        }
        return json(['code' => 1, 'msg' => '文件获取成功', 'data' => $rescode]);
    }

    /**
     * 删除文件夹或者文件
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteFile(Request $request)
    {
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }

        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        $end = pathinfo($path, PATHINFO_EXTENSION);
        if ($end == "" && is_dir($path) === true) {
            // 文件夹
            Base::deleteAllFile($path);
            return json(['code' => 1, 'msg' => '文件夹删除成功']);
        }
        // 文件
        unlink($path);
        return json(['code' => 1, 'msg' => '文件删除成功']);
    }

    /**
     * 更新代码
     * @param Request $request 请求
     * @return string $res json
     */
    public function updataCode(Request $request)
    {
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        $code = $request->post('code');
        Base::writeToFile($path, $code);
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

        /* 不是管理员就进行限制大小 */
        //大小验证
        // 换算成字节
        $usercloudfilememory = $this->getAllFileSizeLimit();
        $tempath = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . Base::getUidById($my_aid);
        if (!file_exists($tempath)) {
            return 1;
        }
        if (is_dir($tempath)) {
            $size = Base::getFileSize($tempath);
        } else {
            $size = filesize($tempath);
        }
        $size += $file->getSize();
        if ($size > $usercloudfilememory) {
            Base::deleteAllFile($file->getRealPath());
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
        $postpath = $request->post('path');
        $isexist = strripos($postpath, '..');
        if ($isexist !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }

        $savepath = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        $file = $request->file('file');
        // 容量验证
        if ($this->judgeFileBig($my_aid, $file) != 1) {
            return json(['code' => -1, 'msg' => '您的剩余容量不足！']);
        }
        if (!$file->isValid()) {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }
        $up_full_name = $file->getUploadName();

        if (Base::getStrLen($up_full_name) > Base::$file_name_length_limit) {
            return json(['code' => -1, 'msg' => '文件名字符数不能超过' . Base::$file_name_length_limit . '个']);
        }

        //判断是否需要新建文件夹
        Base::judgeCreatPath($savepath);
        $name_json = Base::first_last_name($up_full_name);
        $first_last_json = json_decode($name_json);
        $first_name = $first_last_json->first_name;
        $last_name = $first_last_json->last_name;
        $name = Base::Base64Encode($first_name) . $last_name;
        $file->move($savepath . '/' . $name);
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return json(['code' => 1, 'msg' => '上传成功']);
    }

    /**
     * 文件下载
     * @param Request $request 请求
     * @return string $res json
     */
    public function downloadFile(Request $request)
    {
        $postpath = $request->post('path');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (strripos($postpath, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }

        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath;
        //判断文件是否存在
        if (file_exists($path)) {
            //原始文件名称
            $name = '';
            $istext = false;
            for ($i = strlen($path) - 1; $i >= 0; --$i) {
                if ($path[$i] == '/')
                    break;
                if ($path[$i] == '.') {
                    $istext = true;
                }
                $name = $path[$i] . $name;
            }
            if ($name == '') {
                $name = md5($my_aid . time());
            }
            //不是文件就下载压缩包
            if ($istext === false) {
                $name = md5($my_aid . time());
                if ($this->dfs($path) == 0) {
                    //路径下无文件，为了确保可以压缩，新建一个文件
                    Cloudfile::creatFile($path);
                }
                $tempath = '/tmp/' . $my_uid . '/' . md5(time());
                Base::judgeCreatPath($tempath);
                Base::fileCopy($path, $tempath, 1);
                Base::decodeFile($tempath, strlen($tempath));
                //文件路径加文件名称
                $path = '/tmp/' . $name . '.zip';
                Base::make_zip_file_for_folder($path, $tempath); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                // exec('cd ' . $tempath . ' && zip -r ' . $path . ' ' . $tempath);
            }
            //以只读和二进制模式打开文件
            return response('')->download($path);
        }
        return json(['code' => -1, 'msg' => '文件不存在']);
    }

    /**
     * 目录结尾不含/
     * 文件DFS
     * @param string $dir 目录
     * @return int $filenum 文件数目
     */
    protected function dfs($dir)
    {
        $filenum = 0;
        if (is_dir($dir)) {
            $diearr = scandir($dir);
            foreach ($diearr as &$tem) {
                if ($tem == '.' || $tem == '..') {
                    continue;
                } else {
                    $filenum = $filenum + $this->dfs($dir . '/' . $tem);
                }
            }
        } else {
            ++$filenum;
        }
        return $filenum;
    }


    /**
     * 新建文件（夹）
     * @param Request $request 请求
     * @return string $res json
     */
    public function newFile(Request $request)
    {
        $postpath = $request->post('path');
        $up_name = $request->post('name');
        $my_uid = JwtToken::getCurrentId();
        if (strripos($postpath, '..') !== false || strripos($up_name, '..') !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        if (Base::getStrLen($up_name) > Base::$file_name_length_limit) {
            return json(['code' => -1, 'msg' => '文件名/文件夹名字符数不能超过' . Base::$file_name_length_limit . '个']);
        }
        $first_last_json = json_decode(Base::first_last_name($up_name));
        $first_name = $first_last_json->first_name;
        $last_name = $first_last_json->last_name;
        $name = Base::Base64Encode($first_name) . $last_name;
        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid . $postpath . '/' . $name;
        $istxt = false;
        for ($i = 0; $i < strlen($name); ++$i) {
            if ($name[$i] == '.') {
                $istxt = true;
                break;
            }
        }
        try {
            if ($istxt) {
                $filein = fopen($path, 'a');
                fwrite($filein, '');
                fclose($filein);
            } else {
                mkdir($path, 0666, true);
            }
        } catch (Exception $e) {
            return json(['code' => -1, 'msg' => '创建失败！']);
        }
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

        $path = Base::$LTPP_public_path . Cloudfile::$cloudfile_root_path . $my_uid;
        //判断是否需要新建文件夹
        Base::judgeCreatPath($path);
        $size = Base::getFileSize($path);
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