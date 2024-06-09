<?php

namespace app\controller;

use Exception;
use support\Db;
use support\Redis;
use Webman\Http\Request;
use Tinywan\Jwt\JwtToken;
use GatewayWorker\Lib\Gateway;
use Webman\RedisQueue\Redis as RedisQueue;

class Language
{
    const c = 'c';
    const cpp = 'cpp';
    const php = 'php';
    const java = 'java';
    const javascript = 'javascript';
    const typescript = 'typescript';
    const ruby = 'ruby';
    const rust = 'rust';
    const python = 'python';
    const golang = 'golang';
    const csharp = 'csharp';
};

class Base
{

    /**
     * 软件名称
     */
    static $app_name = 'LTPP在线开发平台';

    /**
     * Redis数据库数目
     */
    static $redis_db_num = 38;

    /**
     * mysql域名
     */
    static $mysql_domain_name = 'MYSQL';

    /**
     * redis域名
     */
    static $redis_domain_name = 'REDIS';

    /**
     * clash域名
     */
    static $clash_domain_name = 'CLASH';

    /**
     * 音乐端口
     */
    static $music_port = '3000';

    /**
     * mysql端口
     */
    static $mysql_port = 4466;

    /**
     * redis端口
     */
    static $redis_port = 6379;

    /**
     * ltpp-ssh端口
     */
    static $ssh_port = 49999;

    /**
     * clash端口
     */
    static $clash_port = 7890;

    /**
     * 请求过期时间（单位：秒）
     */
    static $request_timout = 600;

    /**
     * 默认SSH最小公网端口数目
     */
    static $ssh_min_open_ports_num = 2;

    /**
     * gzip压缩率
     */
    static $gzip_num = 5;

    /**
     * 图片压缩质量
     */
    static $img_quality = 60;

    /**
     * 通知文件扩展名
     */
    static $notice_file_extension = 'md';

    /**
     * 默认trace信息
     */
    static $default_trace_msg = '暂无Trace信息';

    /**
     * 默认错误信息
     */
    static $default_error_msg = '暂无报错信息';

    /**
     * 代码提交成功提示
     */
    static $code_up_success_msg = '代码提交成功';

    /**
     * 代码提交失败提示
     */
    static $code_up_fail_msg = '代码提交失败！请重新提交！';

    /**
     * 不支持的语言提示
     */
    static $no_support_language_msg = '该语言暂不支持！请重新选择语言后提交！';

    /**
     * 参数错误
     */
    static $param_error_msg = '参数错误';

    /**
     * 空代码提示
     */
    static $empty_code_msg = '请编写代码后再次提交哦！';

    /**
     * 代码提交等待关键词
     */
    static $code_up_waiting = '等待中';

    /**
     * 代码提交运行关键词
     */
    static $code_up_running = '运行中';

    /**
     * 代码TLE关键词
     */
    static $code_run_tle = 'TLE';

    /**
     * 代码RE关键词
     */
    static $code_run_re = 'RE';

    /**
     * 代码MLE关键词
     */
    static $code_run_mle = 'MLE';

    /**
     * 代码正常运行关键词
     */
    static $code_run_success = '正常运行';

    /**
     * 代码编译出错关键词
     */
    static $code_run_compiler_wrong = '编译出错';

    /**
     * 代码运行出错关键词
     */
    static $code_run_running_wrong = '运行出错';

    /**
     * 代码AC关键词
     */
    static $code_run_ac = 'AC';

    /**
     * 代码Wrong关键词
     */
    static $code_run_wrong = '答案错误';

    /**
     * 判题机编译出错关键词
     */
    static $judge_compiler_error_msg = '判题机编译异常';

    /**
     * 代码保存失败关键词
     */
    static $judge_code_save_error_msg = '代码保存失败';

    /**
     * 判题机出错关键词
     */
    static $judge_error_msg = '判题机运行异常';

    /**
     * 题库管理AC代码字段默认提示词
     */
    static $oj_ac_code_default = '无';

    /**
     * 竞赛排名计算中提示词
     */
    static $contest_rank_in_calculation = '竞赛排名计算中';

    /**
     * 竞赛无排名提示词
     */
    static $contest_no_rank = '当前竞赛暂无排名信息';

    /**
     * 竞赛未开始排名提示词
     */
    static $contest_rank_not_begin = '竞赛开始后展示排名';

    /**
     * OI赛制未结束排名提示词
     */
    static $contest_rank_oi_not_end = 'OI赛制竞赛结束后展示排名';

    /**
     * AC提示
     */
    static $ac_msg = '恭喜您AC啦';

    /**
     * AK提示
     */
    static $ak_msg = '恭喜您AK啦';

    /**
     * 服务器异常提示
     */
    static $server_error_msg = '服务器异常';

    /**
     * GPT Key在MySQL&&Redis中Key的名称
     */
    static $chatgpt_keys_key = 'chatgpt_keys';

    /**
     * GPT API地址在MySQL&&Redis中Key的名称
     */
    static $chat_gpt_api_url_key = 'chatgpt_api_url';

    /**
     * 文件数据数据表公有后缀名称
     */
    static $db_file_data_same_name = '_file_data';

    /**
     * 文件路径数据表公有后缀名称
     */
    static $db_file_path_same_name = '_file_path';

    /**
     * @var array $extion_map_number 文件类型转数字
     */
    static public $extion_map_number = [
        'mp3' => 2,
        'aac' => 2,
        'ac3' => 2,
        'mp3adu' => 2,
        'mp3adufloat' => 2,
        'mp3float' => 2,
        'mp3on4' => 2,
        'mp3on4float' => 2,
        'amrnb' => 2,
        'amrwb' => 2,
        'cook' => 2,
        'ra_144' => 2,
        'ra_288' => 2,
        'sipr' => 2,
        'wmav1' => 2,
        'wmav2' => 2,
        'wmavoice' => 2,
        'wmapro' => 2,
        'wamlossless' => 2,
        'nellymoser' => 2,
        'vorbis' => 2,
        'm3u8' => 2,
        'mp4' => 3,
        'avi' => 3,
        'rmvb' => 3,
        '3gp' => 3,
        'mpeg' => 3,
        'wmv' => 3,
        'mov' => 3,
        'mpv' => 3,
        'flv' => 3,
        'swf' => 3,
        'cpp' => 4,
        'c' => 4,
        'js' => 4,
        'html' => 4,
        'css' => 4,
        'php' => 4,
        'go' => 4,
        'java' => 4,
        'py' => 4,
        'ts' => 4,
        'json' => 4,
        'gitignore' => 4,
        'sh' => 4,
        'lock' => 4,
        'rs' => 4,
        'dart' => 4,
        'cs' => 4,
        'r' => 4,
        'pdf' => 5,
        'rar' => 6,
        'zip' => 6,
        'tar' => 6,
        'gz' => 6,
        'tar.gz' => 6,
        '7z' => 6,
        'apz' => 6,
        'ar' => 6,
        'bz' => 6,
        'car' => 6,
        'dar' => 6,
        'cpgz' => 6,
        'f' => 6,
        'ha' => 6,
        'hbcj' => 6,
        'hbc2j' => 6,
        'hbej' => 6,
        'hpkj' => 6,
        'hypj' => 6,
        'jpg' => 7,
        'png' => 7,
        'jpeg' => 7,
        'gif' => 7,
        'svg' => 7,
        'bmp' => 7,
        'tif' => 7,
        'pcx' => 7,
        'tga' => 7,
        'exif' => 7,
        'fpx' => 7,
        'psd' => 7,
        'cdr' => 7,
        'pcd' => 7,
        'dxf' => 7,
        'ufo' => 7,
        'eps' => 7,
        'ai' => 7,
        'raw' => 7,
        'WMF' => 7,
        'webp' => 7,
        'avif' => 7,
        'apng' => 7,
        'exe' => 8,
        'apk' => 8,
        'bat' => 8,
        'ace' => 8,
        'app' => 8,
        'com' => 8
    ];

    /**
     * Redis密码
     */
    static $redis_password = 'SQS';

    /**
     * Redis MQ数据库
     */
    static $redis_mq_db = 19;

    /**
     * MySQL用户名
     */
    static $mysql_username = 'ltpp';

    /**
     * MySQL密码
     */
    static $mysql_password = 'ltpp';

    /**
     * 没有更多消息提示
     */
    static $no_more_msg = '到底啦！没有更多了！';

    /**
     * 游客用户名
     */
    static $no_login_user_name = '游客';

    /**
     * 竞赛机器人比赛开始后多久开始做题（单位：秒）
     * @var int $robot_contest_start_after_begin_seconds
     */
    static $robot_contest_start_after_begin_seconds = 60;

    /**
     * 判题机路径
     */
    static $judgepath = '/JudgeServer/judge';

    /**
     * 发送邮件消息队列名称
     */
    static $redis_queue_send_mail_name = 'send_mail';

    /**
     * 购买SSH消息队列名称
     */
    static $redis_queue_buy_ssh_name = 'buy_ssh';

    /**
     * 请求消息队列名称
     */
    static $redis_queue_request_name = 'request';

    /**
     * 在线测试代码消息队列名称
     */
    static $redis_queue_webcode_run_name = 'webcode_run';

    /**
     * 在线判题消息队列名称
     */
    static $redis_queue_judgecode_run_name = 'judgecode_run';

    /**
     * 更新代码信息消息队列名称
     */
    static $redis_queue_update_code_name = 'update_code';

    /**
     * 删除竞赛队列名称
     */
    static $redis_queue_delete_contest_name = 'delete_contest';

    /**
     * 更新题库信息消息队列名称
     */
    static $redis_queue_update_oj_name = 'update_oj';

    /**
     * 机器人参赛消息队列名称
     */
    static $redis_queue_robot_contest_name = 'robot_contest';

    /**
     * 竞赛排名计算队列名称
     */
    static $redis_queue_contest_rank_name = 'contest_rank';

    /**
     * 监控队列名称
     */
    static $redis_queue_monitor = 'monitor';

    /**
     * 未知用户展示名称
     */
    static $unknow_user_name = '未知用户';

    /**
     * 竞赛代码缓存key索引所在竞赛索引
     */
    static $redis_contest_code_list_key_name = 'ContestCodeListKey';

    /**
     * 用户名和群名称的长度限制
     */
    static $name_len_limit = 26;

    /**
     * 除了用户名和群名称，其余名称长度限制
     */
    static $other_name_len_limit = 191;

    /**
     * 默认请求头
     */
    static $default_http_header = ['Content-Type:application/x-www-form-urlencoded'];

    /**
     * AK奖励用户学虫币
     */
    static $ak_money = 1;


    /**
     * AC未通过的题目奖励用户学虫币
     */
    static $ac_money = 0.1;

    /**
     * 服务器缓存信息过期时间（单位：秒）
     */
    static $redis_timeout = 3600;

    /**
     * 服务器代码结果缓存信息过期时间（单位：秒）
     */
    static $redis_code_run_res_timeout = 60;

    /**
     * 服务器地址
     */
    static $GLOBlinuxurl = 'http://127.0.0.1:48787';

    /**
     * ltpp用户id
     */
    static $ltpp_linux_user_id = 1000;

    /**
     * 文字编码
     */
    static $str_encoding = 'UTF-8,GBK,GB2312,BIG5';

    /**
     * IP请求限制次数
     */
    static $GLOBiplimit = 0;

    /**
     * IP拉黑达到的请求次数
     */
    static $GLOBipblack = 0;

    /**
     * 请求频率过期时间
     */
    static $GLOBiplimitTime = 0;

    /**
     * testdata时间文件名
     */
    static $testdata_time_file_name = 'time.stamp';

    /**
     * RobotContest Redis前缀
     */
    static $robot_contest_redis_front = 'RobotContest';

    /**
     * 强制取消RobotContest Redis前缀
     */
    static $robot_contest_cancel_redis_front = 'ForceCancelRobotContest';

    /**
     * 代码安全信息
     */
    static $code_safe = 'safe';

    /**
     * 判题机安装路径
     */
    static $judge_install_path = '/JudgeServer/';

    /**
     * 判题机名称
     */
    static $judge_name = 'judge';

    /**
     * 沙箱地址
     */
    static $sandbox_path = '/home/LTPPSANDBOX/';

    /**
     * 判题机用户代码运行状态码
     */
    static $judge_code_error = -1;

    /**
     * 判题机异常状态码
     */
    static $judge_server_error = 0;

    /**
     * 判题机用户代码正常运行完成状态码
     */
    static $judge_code_finish = 1;

    /**
     * 判题机编译错误状态码
     */
    static $judge_code_compiler_error = 2;

    /**
     * 判题机用户代码运行TLE状态码
     */
    static $judge_code_tle = 3;

    /**
     * 判题机用户代码运行MLE状态码
     */
    static $judge_code_mle = 4;

    /**
     * 判题机用户代码运行RE状态码
     */
    static $judge_code_re = 5;

    /**
     * 写入最大重试次数
     */
    static $write_to_file_retry_max_times = 1024;

    /**
     * 需要删除的key 
     */
    static $to_safe_delete_key = [
        'isdel' => true
    ];

    /**
     * 测试用例路径
     */
    static $testdata_path = '/tmp/testdata/';

    /**
     * 需要加密的key
     */
    static $to_safe_key = [
        'id' => true,
        'writerid' => true,
        'problemid' => true,
        'userid' => true,
        'maincommentid' => true,
        'touserid' => true,
        'articleid' => true,
        'user_id' => true,
        'contestid' => true,
        'createrid' => true,
        'videoid' => true,
        'followid' => true,
        'creatorid' => true,
        'get_user_id' => true,
        'msg_id' => true,
        'post_user_id' => true,
        'group_id' => true,
        'fanuserid' => true,
        'questionid' => true,
        'mainanswerid' => true,
        'creator_id' => true,
    ];

    /**
     * 可识别的语言
     */

    static $oj_judge_language = [
        'C',
        'C++',
        'Java',
        'Python3',
        'Go',
        'PHP',
        'JavaScript',
        'Rust',
        'TypeScript',
        'C#',
        'Ruby'
    ];

    /**
     * 语言转换
     */
    static $language_map = [
        'c' => Language::c,
        'c++' => Language::cpp,
        'cpp' => Language::cpp,
        'rs' => Language::rust,
        'rust' => Language::rust,
        'java' => Language::java,
        'go' => Language::golang,
        'golang' => Language::golang,
        'php' => Language::php,
        'inc' => Language::php,
        'javascript' => Language::javascript,
        'js' => Language::javascript,
        'node' => Language::javascript,
        'typescript' => Language::typescript,
        'ts' => Language::typescript,
        'python' => Language::python,
        'python2' => Language::python,
        'python3' => Language::python,
        'rusthon' => Language::python,
        'c#' => Language::csharp,
        'Ruby' => Language::ruby,
        'csharp' => Language::csharp,
        'ruby' => Language::ruby,
        'jruby' => Language::ruby,
        'macruby' => Language::ruby,
        'rake' => Language::ruby,
        'rb' => Language::ruby,
        'rbx' => Language::ruby,
    ];

    /**
     * 代码文件后缀对应语言
     */
    static $map_language_file = [
        'c' => 'c',
        'h' => 'cpp',
        'cc' => 'cpp',
        'cpp' => 'cpp',
        'java' => 'java',
        'py' => 'python',
        'go' => 'golang',
        'php' => 'php',
        'js' => 'javascript',
        'rs' => 'rust',
        'ts' => 'typescript',
        'cs' => 'csharp',
        'rb' => 'ruby',
        'css' => 'css',
        'sh' => 'bash',
        'dll' => 'csharp',
        'm' => 'objectivec',
        'sql' => 'sql',
        'vb' => 'vbnet',
        'kt' => 'kotlin',
        'json' => 'json',
        'pas' => 'delphi',
        'html' => 'html',
        'yaml' => 'yaml',
        'lock' => 'yaml',
        'jsx' => 'javascript',
        'vue' => 'javascript',
        'tsx' => 'typescript'
    ];

    /**
     * 默认聊天云文件大小限制（单位MB）
     * @var int $chat_file_size_limit 默认聊天云文件大小限制（单位MB）
     */
    static $chat_file_size_limit = 100;

    /**
     * 数据库加载数据条数限制
     * @var int $db_get_limit 数据库加载数据条数限制
     */
    static $db_get_limit = 50;

    /**
     * 全站首页一篇文章文字限制
     * @var int $home_one_article_max_length 全站首页一篇文章文字限制
     */
    static $home_one_article_max_length = 100;

    /**
     * 文章标题长度限制
     * @var int $article_name_limit 文章标题长度限制
     */
    static $article_name_limit = 40;

    /**
     * 视频标题长度限制
     * @var int $video_name_limit 视频标题长度限制
     */
    static $video_name_limit = 40;

    /**
     * 代码输出内容长度限制
     * @var int $code_out_limit 代码输出内容长度限制
     */
    static $code_out_limit = 100000;

    /**
     * 后台公告列表文字限制
     * @var int $home_one_article_max_length 全站首页一篇文章文字限制
     */
    static $back_one_notice_max_length = 100;

    /**
     * 图片大小限制
     * @var int $image_size_limit 图片大小限制
     */
    static $image_size_limit = 10485760 * 2;

    /**
     * 视频背景大小限制
     * @var int $image_size_limit 图片大小限制
     */
    static $video_size_limit = 10485760 * 2;

    /**
     * 1MB字节数
     * @var int $one_mb_size 1MB字节数
     */
    static $one_mb_size = 1048576;

    /**
     * timeout超时后最大等待时间（秒）
     */
    static $max_timeout_time = 1;

    /**
     * timeout超时后返回的状态码
     */
    static $timeout_exit_code = 124;

    /**
     * 云盘Base64字符集，勿动，需前端字符集保持一致
     * @var array $char_set Base64字符集，勿动，需前端字符集保持一致
     */
    static public $char_set = [
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '_',
        '*'
    ];

    /**
     * ID Base64字符集，勿动
     * @var array $char_set Base64字符集，勿动
     */
    static public $id_char_set = [
        [
            'b',
            '9',
            'P',
            'h',
            'R',
            '_',
            'S',
            'T',
            '6',
            '1',
            'l',
            'o',
            '7',
            'n',
            'D',
            '*',
            'q',
            'I',
            'r',
            's',
            'u',
            'v',
            'i',
            'm',
            'y',
            'g',
            'z',
            'A',
            'p',
            'w',
            'G',
            'H',
            'E',
            'F',
            'J',
            'M',
            '2',
            'B',
            'N',
            'O',
            'c',
            '8',
            'k',
            'U',
            'X',
            'Y',
            'Z',
            'a',
            '4',
            'e',
            'f',
            'd',
            'C',
            'j',
            't',
            'L',
            'x',
            'V',
            'Q',
            '0',
            'K',
            'W',
            '3',
            '5',
        ],
        [
            'u',
            '6',
            'v',
            'w',
            '8',
            'k',
            'H',
            'I',
            'A',
            'G',
            '9',
            'J',
            'a',
            'b',
            '7',
            'c',
            'x',
            'y',
            '3',
            'F',
            'E',
            'D',
            'z',
            'B',
            'C',
            'd',
            'f',
            'g',
            'h',
            'i',
            'j',
            'V',
            'Y',
            'Z',
            'l',
            'e',
            '0',
            '2',
            'm',
            '4',
            '1',
            'n',
            '_',
            'M',
            'o',
            'p',
            'q',
            'r',
            's',
            'K',
            'L',
            'N',
            '*',
            'O',
            'P',
            'Q',
            'W',
            'S',
            'T',
            't',
            'U',
            'X',
            'R',
            '5',
        ],
        [
            'v',
            '6',
            'w',
            'k',
            'H',
            'I',
            'A',
            'G',
            'a',
            'b',
            '7',
            'c',
            'x',
            'y',
            '3',
            'F',
            'E',
            'D',
            'z',
            'B',
            '1',
            'C',
            'd',
            'u',
            'X',
            'U',
            'f',
            'g',
            'n',
            '4',
            'i',
            'j',
            'V',
            'Y',
            'J',
            'l',
            '0',
            '9',
            'Q',
            '2',
            'm',
            'S',
            '8',
            'h',
            'R',
            'e',
            '_',
            't',
            'M',
            'o',
            'p',
            'q',
            'r',
            's',
            'K',
            'L',
            'N',
            '*',
            'O',
            'P',
            'W',
            'T',
            'Z',
            '5',
        ],
        [
            'v',
            '6',
            'w',
            '1',
            'C',
            'd',
            'u',
            'X',
            'H',
            'I',
            'A',
            'G',
            'a',
            'b',
            '7',
            'c',
            'x',
            'U',
            'f',
            'g',
            'n',
            '4',
            'i',
            'j',
            'V',
            'Y',
            'J',
            'l',
            '0',
            '9',
            'Q',
            '*',
            '2',
            'm',
            '8',
            'k',
            'y',
            '3',
            'F',
            'E',
            'D',
            'z',
            'B',
            'h',
            'R',
            't',
            '5',
            'M',
            'o',
            'p',
            'q',
            'r',
            's',
            'K',
            'S',
            'L',
            'Z',
            'N',
            'O',
            'P',
            'W',
            'T',
            'e',
            '_',
        ]
    ];

    /**
     * 机器人默认用户名
     */
    static $robot_name = '机器人';

    /**
     * LTPP文件夹绝对路径
     * @var string $LTPP_path LTPP文件夹绝对路径
     */
    static $LTPP_path = '/home/LTPP/';

    /**
     * LTPP_runtime_path
     * @var string $LTPP_runtime_path LTPP_runtime_path
     */
    static $LTPP_runtime_path = '/home/LTPP/LTPPRUNTIME';

    /**
     * LTPP日志文件夹绝对路径
     * @var string $LTPP_logs_path LTPP日志文件夹绝对路径
     */
    static $LTPP_logs_path = '/home/LTPP/LTPPRUNTIME/logs/';

    /**
     * LTPP公开文件夹绝对路径
     * @var string $LTPP_public_path LTPP公开文件夹绝对路径
     */
    static $LTPP_public_path = '/home/LTPP/public';

    /**
     * LTPP static文件夹路径
     * @var string $LTPP_public_static_path LTPP static文件夹路径
     */
    static $LTPP_public_static_path = '/static';

    /**
     * 单表数据限制
     * @var string $one_table_length_limit 单表数据限制
     */
    static $one_table_length_limit = 1000000;

    /**
     * 保存文件名长度限制
     * @var string $file_name_length_limit 保存文件名长度限制
     */
    static $file_name_length_limit = 100;

    /**
     * 临时目录
     */
    static $tmp_path = '/tmp/';


    /**
     * 项目名称
     */
    static $LTPP_name = 'LTPP在线开发平台';

    /**
     * 公开方法
     */
    static $safe_func = [
        'judgeLogin' => true,
        'judgeRegister' => true,
        'lookView' => true,
        'getMusicBkurl' => true,
        'send' => true,
        'sendPassword' => true,
        'getVersion' => true,
        'getClassUrl' => true,
        'getSocketUrl' => true,
        'getBackUrl' => true,
        'getFrontUrl' => true,
        'getMusicBkUrl' => true,
        'publicContestRank' => true,
        'loadCharset' => true,
        'oneArticle' => true,
        'lookContestProblemCode' => true,
    ];

    /**
     * 禁止访问的方法
     */
    static $danger_func = [
        'contestIdGetRankEcharts' => true,
        'contestIdGetRank' => true,
        'creatFile' => true,
        'sendUpdateRankMQ' => true,
        'judgeLimitIsSafe' => true,
        'mailto' => true,
        'randImage' => true,
        'judgeIsMyProblem' => true,
        'run' => true,
        'sendChatToOneUserMsgAndEmail' => true,
        'sendChatToOneUserMsg' => true,
        'judgeHasBuy' => true,
        'buy' => true,
        'sendLogin' => true,
        'judgeHasJudgeContest' => true,
        'updateNoLookNum' => true,
        'judgeIsMyContest' => true,
    ];

    /**
     * 私有
     */
    static $danger_path = [
        'app\controller\Base' => true,
        'app\controller\Robot' => true,
        'app\controller\ChatBase' => true,
        'app\controller\Image' => true,
        'app\controller\Ssh' => true,
        'plugin\webman\gateway\Events' => true,
        'plugin\webman\gateway\ChatBase' => true,
        'plugin\webman\gateway\ClassMsg' => true,
        'plugin\webman\gateway\GlobalNotice' => true,
        'plugin\webman\gateway\GroupChat' => true,
        'plugin\webman\gateway\PrivateChat' => true,
        'plugin\webman\gateway\PrivateRobot' => true,
    ];

    /**
     * 防盗链设计，允许的referer
     */
    static $safe_referer_url = [
        'http://localhost',
        'http://127.0.0.1'
    ];

    /**
     * 防盗链设计，允许LTPP后端访问资源的URL
     */
    static $file_can_visit_func = [
        '/Article/oneArticle?path=',
    ];

    /**
     * 检测URL域名部分是否以指定域名结尾
     */
    static public function isDomainEndsWith($url, $ending)
    {
        try {
            // 匹配 URL 中的域名部分
            $pattern = '/^(https?:\/\/)?([\w\d-]+(\.[\w\d-]+)*\.[\w\d]{2,})(\/.*)?$/i';
            preg_match($pattern, $url, $matches);
            // 如果匹配成功并且域名以 $ending 结尾，返回 true；否则返回 false
            if ($matches && isset($matches[2])) {
                $domain = $matches[2];
                return preg_match('/' . preg_quote($ending, '/') . '$/', $domain);
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 获取URL域名
     */
    static public function getDomainFromUrl($url)
    {
        try {
            // 解析 URL
            $parsedUrl = parse_url($url);
            // 检查是否解析成功并且包含域名部分
            if ($parsedUrl && isset($parsedUrl['host'])) {
                // 返回域名部分
                return $parsedUrl['host'];
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        // 解析失败或不包含域名部分时返回空字符串
        return '';
    }


    /**
     * 鉴权
     * @param Request $request
     * @param callable $handler
     */
    static public function judgeAuthCheckTestSafe(Request &$request,  callable $handler = null)
    {
        $my_uid = '';
        if (!$handler) {
            $handler = function () {
            };
        }
        try {
            $is_logout = false;
            $func = $request->action;
            $loc = $request->getRealIp(true);
            if (!$func) {
                $func = '';
            }
            $header = $request->header();
            if (!$header) {
                $header = [];
            }
            try {
                $my_uid = JwtToken::getCurrentId();
            } catch (Exception $e) {
                $is_logout = true;
            }
            $controller = $request->controller;
            if (!$controller) {
                $controller = '';
            }
            // 监控
            RedisQueue::send(Base::$redis_queue_monitor, [
                'path' => $controller ? $controller : $loc,
                'function' => $func ? $func : $request->host(),
                'user_uid' => $my_uid,
            ]);
            // 判断是否是文件资源
            $path = $request->path();
            if (!empty($path) && stripos($path, Base::$LTPP_public_static_path) === 0) {
                // 匹配到访问静态资源
                $file_extion = Base::getDbFileExtion($path);
                $referer = '';
                if (isset($header['referer']) && $header['referer']) {
                    $referer = $header['referer'];
                }
                if (isset($header['Referer']) && $header['Referer']) {
                    $referer = $header['Referer'];
                }
                if (!$referer) {
                    $referer = $request->fullUrl();
                }
                if (!$referer) {
                    return Base::notFoundPage();
                }
                $linuxurl = Base::getSettingKeyData('GLOBlinuxurl');
                $front_url = Base::getSettingKeyData('GLOBfronturl');
                foreach (Base::$safe_referer_url as &$tem_safe_referer_url) {
                    if (strpos($referer, $tem_safe_referer_url) === 0) {
                        // 来自本地访问，直接通过
                        return $handler($request);
                    }
                }
                foreach (Base::$file_can_visit_func as &$tem_file_can_visit_func) {
                    // 允许来自LTPP后端的URL访问白名单
                    if (strpos($referer, $linuxurl . $tem_file_can_visit_func) === 0) {
                        return $handler($request);
                    }
                }
                if (Base::getDomainFromUrl($referer) == Base::getDomainFromUrl($linuxurl)) {
                    // 来自LTPP后端非白名单URL访问
                    $file_can_not_visit_extion = Base::getFileCanNotVisitExtionList();
                    foreach ($file_can_not_visit_extion as &$tem_file_can_not_visit_extion) {
                        // 不可访问类型，直接拒绝
                        if ($file_extion === $tem_file_can_not_visit_extion) {
                            return Base::notFoundPage();
                        }
                    }
                    return $handler($request);
                }
                if (Base::isDomainEndsWith($referer, Base::getDomainFromUrl($front_url))) {
                    // 来自LTPP系列，直接通过
                    return $handler($request);
                }
                return Base::notFoundPage();
            }
            //判断是否需要鉴权
            if (isset(Base::$safe_func[$func])) {
                return $handler($request);
            }
            // 禁止访问的内容
            if (isset(Base::$danger_func[$func])) {
                return Base::notFoundPage();
            }
            if (isset(Base::$danger_path[$request->controller])) {
                return Base::notFoundPage();
            }

            $now_time = time();
            $now = date('Y-m-d H:i:s', $now_time);

            //鉴权，获取authorization
            //判断authorization是否存在或为空
            // 判断是否有单点登录信息
            if (
                !isset($header['authorization']) || empty($header['authorization']) ||
                !isset($header['key']) || empty($header['key']) ||
                !isset($header['requestid']) || empty($header['requestid'])
            ) {
                return json(['code' => 500, 'msg' => '非法访问！', 'data' => []]);
            }
            if ($is_logout) {
                return json(['code' => 500, 'msg' => '您已下线！请重新登录！', 'data' => []]);
            }
            //判断请求是否过期
            // 获取请求时间
            $request_id = (int)Base::Base64Decode($header['requestid']);
            // 毫秒换成秒
            $request_id = (int)($request_id / 1000);
            if ($request_id > $now_time + Base::$request_timout || $request_id + Base::$request_timout < $now_time) {
                return json(['code' => -1, 'msg' => '系统检测到请求异常！', 'data' => []]);
            }
            $my_aid = Base::getIdByUid($my_uid);
            $redis0 = Redis::connection('db0');
            $redis14 = Redis::connection('db14');
            $onekey = $header['key'];
            // 判断单点登录
            if ($onekey != $redis14->get($my_aid . 'login')) {
                return \json(['code' => 500, 'msg' => '您已下线！请重新登录！', 'data' => []]);
            }
            // 是root用户直接放行，不限速
            $root_id = Base::getRootId();

            if ($my_aid == $root_id) {
                return $handler($request);
            }

            if ($redis0->get('BlackID' . $my_aid)) {
                return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！', 'data' => []]);
            } else {
                $black_aid_db = Db::table('blackip')
                    ->where('user_id', $my_aid)
                    ->where('isdel', 0)
                    ->exists();
                if ($black_aid_db) {
                    $redis0->set('BlackID' . $my_aid, 1);
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！', 'data' => []]);
                }
            }

            $GLOBiplimit = Base::getSettingKeyData('GLOBiplimit');
            $GLOBiplimitTime = Base::getSettingKeyData('GLOBiplimitTime');
            $GLOBipblack = Base::getSettingKeyData('GLOBipblack');

            $redis1 = Redis::connection('db1');
            $redisip = 'ip' . $loc . 'id' . $my_aid;
            $user_db = Base::getUserData($my_aid);
            if (!$user_db) {
                return \json(['code' => 500, 'msg' => '账号不存在！请重新登录！', 'data' => []]);
            }
            if ($redis1->get($redisip)) {
                $requestnum = $redis1->get($redisip);
                //频率过快超过限制先拉黑处理
                if ($requestnum >= $GLOBipblack) {
                    $isblack = Db::table('blackip')
                        ->where('user_id', $my_aid)
                        ->where('isdel', 0)
                        ->exists();
                    if (!$isblack) {
                        $msg = '';
                        if (!$user_db) {
                            $msg = '非法用户（伪造id：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已拉黑，可在设置中删除该用户黑名单';
                        } else {
                            $msg = '用户' . $user_db->name . '（id：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已拉黑，可在设置中删除该用户黑名单';
                        }
                        Robot::sendChatToOneUserMsg($root_id, $msg);
                        Db::table('blackip')->insert([
                            'user_id' => $my_aid,
                            'ip' => $loc
                        ]);
                    }
                    return \json(['code' => 500, 'msg' => '您已被拉黑！请联系管理员解除黑名单！', 'data' => []]);
                } else {
                    //频率过快，屏蔽
                    if ($requestnum >= $GLOBiplimit) {
                        if ($requestnum == $GLOBiplimit) {
                            // 通知一次即可
                            $msg = '';
                            if (!$user_db) {
                                $msg = '非法用户（伪造ID：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已对该用户限速处理';
                            } else {
                                $msg = '用户' . $user_db->name . '（ID：' . $my_aid . '）于北京时间' . $now . '请求过快，系统已对该用户限速处理';
                            }
                            Robot::sendChatToOneUserMsg($root_id, $msg);
                        }
                        return \json(['code' => 400, 'msg' => '系统检测到访问异常！已拒绝该请求！', 'data' => []]);
                    }
                    //自增
                    $redis1->incr($redisip);
                }
            } else {
                //不存在就插入
                $redis1->setEx($redisip, $GLOBiplimitTime, 1);
            }
            // 若果想终止执行Action就直接返回Response对象，不想终止则无需return
            // return response('终止执行Action');
            // 请求继续穿越
            return $handler($request);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return Base::notFoundPage();
    }

    /**
     * 获取字符个数
     * @param string $str
     * @return int $length
     */
    static public function getStrLen($str = '')
    {
        try {
            preg_match_all("/./u", $str, $matches);
            $length = count($matches[0]);
            return $length;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return 0;
        }
    }

    /**
     * 获取404内容
     */
    static public function notFoundData()
    {
        $not_found = '';
        try {
            $redis23 = Redis::connection('db23');
            $key = '404_PAGE';
            if ($redis23->get($key)) {
                $not_found = $redis23->get($key);
                return $not_found;
            }
            $path = Base::$LTPP_public_path . '/404.html';
            if (file_exists($path)) {
                $not_found = file_get_contents($path);
                $redis23->set($key, $not_found);
            } else {
                $not_found = gzencode($not_found, Base::$gzip_num);
                $redis23->set($key, $not_found);
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $not_found;
    }

    /**
     * 返回404页面
     */
    static public function notFoundPage($path = '', $file_extion = '')
    {
        if (!$path) {
            $file_extion = '';
            $path = '';
        } else {
            if (!$file_extion) {
                $file_extion = Base::getDbFileExtion($path);
            }
        }
        $not_found = Base::notFoundData();
        return response($not_found, 404, [
            'Content-Type' => Base::getContentType('html'),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($not_found),
            'File-Content-Type' => Base::getContentType($not_found),
            'Content-Encoding' => 'gzip',
            'File-Path' => $path,
            'File-Extion' => $file_extion,
        ]);
    }

    /**
     * markdown字符串转html字符串
     * @param string $md
     * @return string $html
     */
    static public function markdownToHTML($md = '', $path = '', $file_extion = '')
    {
        if (!$path) {
            $file_extion = '';
            $path = '';
        } else {
            if (!$file_extion) {
                $file_extion = Base::getDbFileExtion($path);
            }
        }
        $md = addslashes($md);
        $md = preg_replace('/[`$<>]/', '\\\\$0', $md);
        $highlight_css = Base::getCss('highlight');
        $highlight_js = Base::getJs('highlight');
        $markdown_it_js = Base::getJs('markdown-it');
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"><link rel="icon" href="https://ltpp.vip/logo.png" type="image/x-icon"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="keywords" content="LTPP,开发,编程,计算机,学习,资源,OJ,LTPP在线开发平台"><meta name="description" content="LTPP（Learning teaching practice platform）在线开发平台是一个编程学习网站，该网站集文章学习、短视频、在线直播、代码训练、在线问答、在线聊天和在线商店于一体，专注于提升用户编程能力，做到“学”与“练”的统一。"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"><base target="_blank" /><title>' . Base::$LTPP_name . '</title><style>' . $highlight_css . '</style><script>' . $highlight_js . '</script><script>' . $markdown_it_js . '</script></head><body><div id="loading-main"><div class=\'loading-body\'><span><span></span><span></span><span></span><span></span></span><div class=\'loading-base\'><span></span><div class=\'loading-face\'></div></div></div><div class=\'loading-longfazers\'><span></span><span></span><span></span><span></span></div><h1 class="loading-h1">LOADING</h1></div><div id="LTPP"></div><script>const md=window.markdownit({html:true,xhtmlOut:true,linkify:true,typographer:true,html_blocks:{allowed:\'all\'},allowedTags:[\'script\',\'style\']});const code=`' . $md . '`;const result=md.render(code);document.getElementById("LTPP").innerHTML=result;</script></body></html>';
        $html = gzencode($html, Base::$gzip_num);
        return response($html, 200, [
            'Content-Type' => Base::getContentType('html'),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($html),
            'File-Content-Type' => Base::getContentType($html),
            'Content-Encoding' => 'gzip',
            'File-Path' => $path,
            'File-Extion' => $file_extion,
        ]);
    }

    /**
     * code字符串转html字符串
     * @param string $code
     * @param string $language
     * @return string $html
     */
    static public function codeToHTML($code = '', $language = 'cpp', $path = '', $file_extion = '')
    {
        if (!$path) {
            $file_extion = '';
            $path = '';
        } else {
            if (!$file_extion) {
                $file_extion = Base::getDbFileExtion($path);
            }
        }
        if (!$language) {
            $language = 'cpp';
        }
        $code = "```$language\n" . $code . "\n```";
        return Base::markdownToHTML($code, $path, $file_extion);
    }

    /**
     * 字符串转html字符串
     * @param string $str
     * @return string $html
     */
    static public function strToHTML($str = '', $path = '', $file_extion = '')
    {
        if (!$path) {
            $file_extion = '';
            $path = '';
        } else {
            if (!$file_extion) {
                $file_extion = Base::getDbFileExtion($path);
            }
        }
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"><link rel="icon" href="https://ltpp.vip/logo.png" type="image/x-icon"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="keywords" content="LTPP,开发,编程,计算机,学习,资源,OJ,LTPP在线开发平台"><meta name="description" content="LTPP（Learning teaching practice platform）在线开发平台是一个编程学习网站，该网站集文章学习、短视频、在线直播、代码训练、在线问答、在线聊天和在线商店于一体，专注于提升用户编程能力，做到“学”与“练”的统一。"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"><base target="_blank" /><title>' . Base::$LTPP_name . '</title></head><body>' . $str . '</body></html>';
        $html = gzencode($html, Base::$gzip_num);
        return response($html, 200, [
            'Content-Type' => Base::getContentType('html'),
            'Accept-Ranges' => 'bytes',
            'Content-Length' => strlen($html),
            'File-Content-Type' => Base::getContentType($html),
            'Content-Encoding' => 'gzip',
            'File-Path' => $path,
            'File-Extion' => $file_extion,
        ]);
    }

    /**
     * url编码
     * @param string $str
     * @return string $res
     */
    static public function urlEncode($str)
    {
        if (!$str || !is_string($str)) {
            return '';
        }
        $res = '';
        foreach (str_split($str) as $char) {
            $res .= '%' . bin2hex($char);
        }
        return $res;
    }

    /**
     * 加载HTML文章内容
     */
    static public function getHTMLArticle($article_uid = '')
    {
        try {
            $article_id = Base::getIdByUid($article_uid);
            if (!$article_id) {
                return Base::notFoundPage();
            }
            $data = Base::getArticleData($article_id);
            if (!$data || empty($data) || $data->public != 1) {
                return Base::notFoundPage();
            }
            $user_db = Base::getUserData($data->writerid);
            if (!$user_db) {
                return Base::notFoundPage();
            }
            $name = $data->name ?? '';
            $article = $data->article ?? '';
            $writer = $user_db->name ?? '';
            $fabulous = $data->fabulous ?? '';
            $collection = $data->collection ?? '';
            $releasetime = $data->releasetime ?? '';
            $lastchangetime = $data->lastchangetime ?? '';
            $image = $data->image ?? '';
            $url = Base::getSettingKeyData('GLOBfronturl') . '/onearticle?path=' . Base::urlEncode($article_uid);
            $res = '<h1>' . $name . "</h1>\n\n" .
                '[原文链接](' . $url . ')' . "\n\n" .
                '> 版权声明：本文为LTPP作者「' . $writer . '」的文章，著作权归作者所有，商业转载请联系作者获得授权，非商业转载请注明出处。' . "\n\n<br>\n\n" .
                '> 发布时间：' . $releasetime . "\n\n<br>\n\n" .
                ($lastchangetime != $releasetime ? '> 修改时间：' . $lastchangetime . "\n\n<br>\n\n" : '') .
                '> 点赞数：' . $fabulous . "\n\n<br>\n\n" .
                '> 收藏数：' . $collection . "\n\n<br>\n\n" .
                '<div style="display:flex;justify-content:center;"><img src="' . $image . '" alt="" style="margin:0px 0px 8px 0px;"></div>' . "\n\n" .
                $article;
            return Base::markdownToHTML($res);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return Base::notFoundPage();
    }

    /**
     * 发送POST请求
     * @param string $url 请求地址
     * @param array $header 请求头
     * @param array $body 请求体
     * @param bool $body_type_is_json 请求体是否是json
     * @return string $res 响应数据
     */
    static function postRequest($url = '', $header = [], $body = [], $body_type_is_json = false)
    {
        if (!$url) {
            return '';
        }
        $res = '';
        try {
            if ($body_type_is_json) {
                $data_string = json_encode($body);
            } else {
                $data_string = http_build_query($body);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_PROXY, 'http://' . Base::$clash_domain_name . ':' . Base::$clash_port);
            // 禁用 SSL 证书验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // 禁用主机验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $res = curl_exec($ch);
            // if (curl_errno($ch)) {
            //     Base::sendErrorNotice('', '发送POST请求异常信息：' . curl_error($ch));
            // }
            curl_close($ch);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $res;
    }

    /**
     * 发送GET请求
     */
    static function getRequest($url = '', $header = [])
    {
        if (!$url) {
            return '';
        }
        $res = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_PROXY, 'http://' . Base::$clash_domain_name . ':' . Base::$clash_port);
            // 禁用 SSL 证书验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // 禁用主机验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $res = curl_exec($ch);
            // if (curl_errno($ch)) {
            //     Base::sendErrorNotice('', '发送GET请求异常信息：' . curl_error($ch));
            // }
            curl_close($ch);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $res;
    }

    /**
     * 去除开头若干换行
     * @param string $str
     */
    static public function removeBr(&$str)
    {
        try {
            $len = strlen($str);
            $res = '';
            for ($i = 0; $i < $len; ++$i) {
                if ($str[$i] == "\n" && $res == '') {
                    continue;
                }
                $res .= $str[$i];
            }
            $str = $res;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 压缩整个文件夹为zip文件
     * @param string $zip_path 压缩包保存到的文件位置
     * @param string $folder_path 待压缩文件所在路径
     */
    static public function make_zip_file_for_folder($zip_path = '', $folder_path = '')
    {
        $rootPath = realpath($folder_path);
        $zip = new \ZipArchive();
        $zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    /**
     * 判断路径是否存在（路径以/开头），不存在创建路径中的文件夹
     * @param string $path 路径
     * @param int $grade 权限
     */
    static public function judgeCreatPath($path, $grade = 0666)
    {
        if (file_exists($path)) {
            return true;
        }
        $name = [];
        $length = strlen($path);
        // 获取全部名称
        for ($i = 0; $i < $length; ++$i) {
            if ($path[$i] == '/') {
                $tem = '';
                for ($j = $i + 1; $j < $length; ++$j) {
                    if ($path[$j] == '/') {
                        $i = $j - 1;
                        break;
                    }
                    $tem .= $path[$j];
                    if ($j == $length - 1) {
                        $i = $j;
                        break;
                    }
                }
                if ($tem != '') {
                    $name[] = $tem;
                }
            }
        }
        $now_path = '/';
        foreach ($name as &$tem) {
            $now_path .= $tem . '/';
            $isfile = strripos($now_path, '.');
            if (!file_exists($now_path) && $isfile === false && !is_dir($now_path)) {
                try {
                    @mkdir($now_path, $grade, true);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        return false;
    }

    /**
     * 从url获取ip/域名端口
     * @param string $url
     * @return string $port
     */
    static public function getPort($url)
    {
        $port = '';
        try {
            if (!is_string($url)) {
                return $port;
            }
            $cnt = 0;
            $len = strlen($url);
            for ($i = 0; $i < $len; ++$i) {
                if ($url[$i] == ':') {
                    ++$cnt;
                }
                if ($cnt == 2) {
                    for ($j = $i + 1; $j < $len; ++$j) {
                        if ($url[$j] < '0' || $url[$j] > '9') {
                            break;
                        }
                        $port .= $url[$j];
                    }
                    break;
                }
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return $port;
        }
        return $port;
    }

    /**
     * 从url获取ip/域名地址
     * @param string $url
     * @return string $ip
     */
    static public function getIp($url)
    {
        $ip = '';
        try {
            if (!is_string($url)) {
                return $ip;
            }
            $len = strlen($url);
            $begin = false;
            $times = 0;
            for ($i = 0; $i < $len; ++$i) {
                if (($begin && $url[$i] == ':') || ($times >= 2 && $url[$i] == '/')) {
                    break;
                }
                if (!$begin && $url[$i] == ':') {
                    $begin = true;
                    continue;
                } else if ((!$begin && $url[$i] != ':') || ($times < 2 && $url[$i] != '/')) {
                    continue;
                }
                if ($url[$i] == '/') {
                    ++$times;
                    continue;
                }
                $ip .= $url[$i];
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return $ip;
        }
        return $ip;
    }

    /**
     * 生成随机字符串
     * @param int $length
     * @return string $res
     */
    static public function generateRandomString($length = 0)
    {
        try {
            $characters = '7845Whij0123&*()_+{}|:"<qrs6klmEFVtuvXYZ!@#$%^9abcdefg>?~,./;\'[]\\nopwxyzABCD-GHIJKLMNOPQRSTU=';
            $len = strlen($characters);
            $res = '';
            for ($i = 0; $i < $length; $i++) {
                $res .= $characters[rand(0, $len - 1)];
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return '';
        }
        return $res;
    }

    /**
     * 判断是否超时
     */
    static public function judgeIsTimeout($run_exec_code)
    {
        try {
            return $run_exec_code == Base::$timeout_exit_code;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return false;
        }
    }

    /**
     * 获取文件路径表名
     */
    static public function getFilePathTableName($file_path = '')
    {
        if ($file_path) {
            $array = explode('/', $file_path);
            if (sizeof($array) >= 3) {
                $id = Base::md5GetFileTableIndexId($array[2]);
                if (is_numeric($id)) {
                    return $id . Base::$db_file_path_same_name;
                }
            }
        }
        return Base::getFileTableIndex()[0] . Base::$db_file_path_same_name;
    }

    /**
     * 通过md5获取file_table_index id
     */
    static public function md5GetFileTableIndexId($base64_md5 = '')
    {
        try {
            $md5 = Base::decodeStr($base64_md5);
            if (!$md5) {
                return null;
            }
            $redis35 = Redis::connection('db35');
            $key = 'md5' . $md5;
            $id = $redis35->get($key);
            if ($id && is_numeric($id)) {
                return $id;
            }
            $db = Db::table('file_table_index')
                ->where('md5', $md5)
                ->select('id')
                ->first();
            if (!$db) {
                return null;
            }
            $redis35->setEx($key, Base::$redis_code_run_res_timeout, $db->id);
            return $db->id;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return null;
    }

    /**
     * 获取文件数据表名
     */
    static public function getFileDataTableName($file_path = '')
    {
        if ($file_path) {
            $array = explode('/', $file_path);
            if (sizeof($array) >= 3) {
                $id = Base::md5GetFileTableIndexId($array[2]);
                if (is_numeric($id)) {
                    return $id . Base::$db_file_data_same_name;
                }
            }
        }
        return Base::getFileTableIndex()[0] . Base::$db_file_data_same_name;
    }

    /**
     * 插入数据到指定数据表
     * @param string $db_name 数据表名
     * @param array $data 数据
     * @return string $id 插入后的id
     */
    static public function insertToDb($db_name = '', $data = [])
    {
        $resid = 0;
        try {
            if (!$db_name || !$data) {
                return $resid;
            }
            if ($db_name == 'ssh') {
                Db::table($db_name)->insert($data);
                return $resid;
            }
            if (Base::judgeStrWithEndStr('file_path', $db_name)) {
                // 路径为空或者不包含static目录则返回
                if (!isset($data['path']) || strripos($data['path'], Base::$LTPP_public_static_path) === false) {
                    return $resid;
                }
                Db::table($db_name)->insert($data);
            } else {
                $resid = Db::table($db_name)->insertGetId($data);
            }
        } catch (Exception $e) {
            $resid = null;
            Base::sendErrorNotice($e->getTraceAsString(), '插入数据' . json_encode($data ?? []) . '到数据表' . $db_name . '出错:' . $e->getMessage());
        }
        return $resid;
    }

    /**
     * 密码加密
     * @param string $password 密码
     * @return string $res 加密后的密码
     */
    static public function passwordEncryption($password)
    {
        return md5(md5($password));
    }

    /**
     * 两次加密
     * @param string $id 
     * @return string $res 加密后的结果
     */
    static public function doubleMd5($id)
    {
        return md5(md5($id));
    }

    /**
     * 根据用户id判断记录是否存在
     * @param string $db 表名称
     * @param string $id id
     * @return bool
     */
    static public function judgeExistInDb($db, $id)
    {
        if (!$db || !$id) {
            return false;
        }
        $is_exist = Db::table($db)
            ->where('id', $id)
            ->where('isdel', 0)
            ->exists();
        if ($is_exist) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为root
     * @param $id 用户id
     * @return bool true为是root
     */
    static public function judgeIsRoot($id = 0)
    {
        if (!$id) {
            return false;
        }
        return ((int) Base::getRootId()) === ((int) $id);
    }

    /**
     * 判断是否为机器人
     * @param $id 用户id
     * @return bool true为是机器人
     */
    static public function judgeIsRobot($id)
    {
        $user_data = Base::getUserData($id);
        if (!$user_data) {
            return false;
        }
        return $user_data->email == Base::getRobotEmail();
    }

    /**
     * 生成容器名称
     * @param int $port 端口
     **/
    static public function creatDockerName($port = 0)
    {
        return md5(Base::getUidById($port));
    }

    /**
     * 根据用户id判断是否是管理员
     * @param int $id 用户id
     * @return bool
     */
    static public function judgeIsAdmin($id)
    {
        $db = Base::getUserData($id);
        if (empty($db)) {
            return false;
        }
        if ($db->grade == 2 || $db->grade == 3) {
            return true;
        }
        return false;
    }

    /**
     * 代码结果缓存
     */
    static public function saveCodeJson($code_id = 0, $json = '')
    {
        $redis29 = Redis::connection('db29');
        $key = 'CodeJson' . $code_id;
        $redis29->setEx($key, Base::$redis_code_run_res_timeout, json_encode($json));
    }

    /**
     * 删除代码结果缓存
     */
    static public function deleteCodeJson($code_id = 0)
    {
        $redis33 = Redis::connection('db33');
        $redis29 = Redis::connection('db29');
        $key = 'CodeJson' . $code_id;
        $redis29->del($key);
        $key = 'CodeData' . $code_id;
        $redis33->del($key);
    }

    /**
     * 获取代码结果缓存
     */
    static public function getCodeJson($code_id = 0)
    {
        $redis29 = Redis::connection('db29');
        $key = 'CodeJson' . $code_id;
        $json = $redis29->get($key);
        if (!$json) {
            return [];
        }
        try {
            $json = json_decode($json, true);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            $json = [];
        }
        return $json;
    }

    /**
     * 大整数加法
     * @param string $a 第一个字符串数字
     * @param string $b 第二个字符串数字
     * @return string $res 两个字符串数字的和
     */
    static public function bigIntAdd($a, $b)
    {
        if (!is_numeric($a) || !is_numeric($b)) {
            return '不是数字';
        }
        $len_a = strlen($a);
        $len_b = strlen($b);
        $res = '';
        $nex = 0;
        for ($i = $len_a - 1, $j = $len_b - 1; $i >= 0 || $j >= 0 || $nex; --$i, --$j) {
            if ($i >= 0) {
                $nex += $a[$i];
            }
            if ($j >= 0) {
                $nex += $b[$j];
            }
            $res .= $nex % 10;
            $nex = (int) ($nex / 10);
        }
        return strrev($res);
    }

    /**
     * 获取文件大小
     * @param string $path 文件路径
     * @return int $size 文件大小
     */
    static public function getFileSize($path)
    {
        $size = 0;
        $dirs = scandir($path);
        foreach ($dirs as &$tem) {
            if ($tem == '.' || $tem == '..') {
                continue;
            }
            $tempath = $path . '/' . $tem;
            if (is_dir($tempath)) {
                $size += Base::getFileSize($tempath);
            } else {
                $size += filesize($tempath);
            }
        }
        return $size;
    }

    /**
     * 字节转汉字大小（带单位）
     */
    static public function getChineseSize(&$size)
    {
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
    }

    /**
     * 获取系统总容量，单位：字节数
     */
    static public function getLinuxAllDiskSize()
    {
        return disk_total_space('.');
    }

    /**
     * 获取系统剩余总容量，单位：字节数
     */
    static public function getLinuxCanUseDiskSize()
    {
        return disk_free_space('.');
    }


    /**
     * unicode编码
     * @param string $str Unicode编码后的字符串
     * @param string [$encoding] 原始字符串的编码，默认utf-8
     * @param string [$prefix] 编码字符串的前缀，默认"&#"
     * @param string [$postfix] 编码字符串的后缀，默认";"
     * @return string $res 编码后的字符串
     */
    function unicode_encode($str, $encoding = 'utf-8', $prefix = '&#', $postfix = ';')
    {
        //将字符串拆分
        $str = iconv("UTF-8", "gb2312", $str);
        $cind = 0;
        $arr_cont = array();
        for (
            $i = 0;
            $i < strlen($str);
            $i++
        ) {
            if (strlen(substr($str, $cind, 1)) > 0) {
                if (ord(substr($str, $cind, 1)) < 0xA1) { //如果为英文则取1个字节
                    $arr_cont[] = substr($str, $cind, 1);
                    $cind++;
                } else {
                    $arr_cont[] = substr($str, $cind, 2);
                    $cind += 2;
                }
            }
        }
        foreach ($arr_cont as &$row) {
            $row = iconv("gb2312", "UTF-8", $row);
        }
        $unicodestr = '';
        //转换Unicode码
        foreach ($arr_cont as $key => $value) {
            $unicodestr .= $prefix . base_convert(bin2hex(iconv('utf-8', 'UCS-4', $value)), 16, 10) . $postfix;
        }
        return $unicodestr;
    }

    /**
     * unicode解码
     * @param string $str Unicode编码后的字符串
     * @param string $decoding 原始字符串的编码，默认utf-8
     * @param string $prefix 编码字符串的前缀，默认"&#"
     * @param string $postfix 编码字符串的后缀，默认";"
     * @return string $res 编码后的字符串
     */
    function unicode_decode($unistr, $encoding = 'utf-8', $prefix = '&#', $postfix = ';')
    {
        $arruni = explode($prefix, $unistr);
        $unistr = '';
        for ($i = 1, $len = count($arruni); $i < $len; $i++) {
            if (strlen($postfix) > 0) {
                $arruni[$i] = substr($arruni[$i], 0, strlen($arruni[$i]) - strlen($postfix));
            }
            $temp = intval($arruni[$i]);
            $unistr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);
        }
        return iconv('UCS-2', $encoding, $unistr);
    }

    /**
     * 安装沙箱环境
     */
    static public function installSandboxEnv()
    {
        $root_aid = Base::getRootId();
        $path = Base::$sandbox_path;
        // 清理软连接
        Base::deleteAllFile($path . 'usr/bin/java');
        // 创建沙箱目录
        Base::judgeCreatPath($path);
        Robot::sendChatToOneUserMsg($root_aid, '沙箱创建完成');
        // 挂载（C#必须这步）
        $proc_path = $path . 'proc';
        Base::judgeCreatPath($proc_path);
        Base::runExec('umount ' . $proc_path);
        Base::runExec('mount -t proc none ' . $proc_path);
        Robot::sendChatToOneUserMsg($root_aid, '/proc目录挂载完成');
        // 系统时间
        Base::runExec("cp -p -r --parents -f /etc/timezone $path;cp -p -r --parents -f /etc/localtime $path;");
        Robot::sendChatToOneUserMsg($root_aid, '系统时间工具安装完成');
        // ln安装
        Base::runExec("cp -p -r --parents -f /usr/bin/ln $path");
        Robot::sendChatToOneUserMsg($root_aid, '/usr/bin/ln安装完成');
        // 环境安装
        Base::runExec("cp -p -r --parents -f /usr/share/ $path");
        Robot::sendChatToOneUserMsg($root_aid, '/usr/share/安装完成');
        Base::runExec("cp -p --parents -f /bin/bash $path;cp -p --parents -f /bin/sh $path;cp -p -r --parents -f /usr/lib/mono $path;cp -p --parents -f /usr/bin/mono $path;cp -p --parents -f /usr/bin/gem $path;cp -p --parents -f /usr/bin/gem3.1 $path;cp -p --parents -f /usr/bin/ruby $path;cp -p --parents -f /usr/bin/node $path;cp -p --parents -f /usr/bin/node $path;cp -p --parents -f /usr/bin/php $path;cp -p --parents -f /usr/bin/python3 $path;cp -p -r --parents -f /root/.cargo $path;cp -p --parents -f /usr/bin/mcs $path;cp -p -r --parents -f /usr/local/nodejs $path;cp -p --parents -f /usr/bin/go $path;cp -p --parents -f /usr/bin/g++ $path;cp -p --parents -f /usr/bin/javac $path;cp -p --parents -f /lib64/ld-linux-x86-64.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/librt.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libdl.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libxml2.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libssl.so.3 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libcrypto.so.3 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpcre2-8.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libz.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libsodium.so.23 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libargon2.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libicuuc.so.72 $path;cp -p --parents -f /lib/x86_64-linux-gnu/liblzma.so.5 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libicudata.so.72 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libstdc++.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libdl.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libstdc++.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libruby-3.1.so.3.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgmp.so.10 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libcrypt.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libtinfo.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libc.so.6 $path;");
        Base::runExec("cp -p -r --parents -f /usr/lib $path;cp -p -r --parents -f /usr/lib32 $path;cp -p -r --parents -f /usr/lib64 $path;cp -p -r --parents -f /etc/python3 $path;cp -p -r --parents -f /usr/lib/python3 $path;cp -p -r --parents -f /usr/lib/jvm/ $path;cp -p --parents -f /usr/lib/x86_64-linux-gnu/libexpat.so.1 $path;cp -p --parents -f /usr/bin/python3 $path;cp -p --parents -f /etc/alternatives/java $path;cp -p -r --parents -f /etc/ssl/certs/java $path;cp -p --parents -f /var/lib/dpkg/alternatives/java $path;cp -p --parents -f /etc/alternatives/javac $path;cp -p --parents -f /usr/bin/javac $path;cp -p --parents -f /var/lib/dpkg/alternatives/javac $path;cp -p --parents -f /etc/alternatives/php $path;cp -p --parents -f /etc/cron.d/php $path;cp -p -r --parents -f /etc/php $path;cp -p -r --parents -f /usr/lib/php $path;cp -p -r --parents -f /usr/include/php $path;cp -p --parents -f /var/lib/dpkg/alternatives/php $path;cp -p -r --parents -f /var/lib/php $path;cp -p --parents -f /usr/bin/mcs $path;cp -p -r --parents -f /usr/lib/x86_64-linux-gnu/ruby $path;cp -p -r --parents -f /usr/lib/ruby $path;cp -p --parents -f /usr/bin/ruby $path;");
        // java安装
        Base::runExec('chroot ' . $path . ' /bin/sh -c "ln -s /usr/lib/jvm/java-17-openjdk-amd64/bin/java /usr/bin/java"');
        Robot::sendChatToOneUserMsg($root_aid, '编程运行环境安装完成');
        // 权限设置
        Base::runExec('chmod -R --no-preserve-root 777 ' . $path);
        Robot::sendChatToOneUserMsg($root_aid, '沙箱所有者设置完成');
        Base::runExec('chown -R --no-preserve-root ltpp:ltpp ' . $path);
        Robot::sendChatToOneUserMsg($root_aid, '沙箱权限设置完成');
    }

    /**
     * 文件（夹）删除
     * @param string $dir 文件路径
     * @return bool $res 删除是否成功
     */
    static public function deleteAllFile($dir = '/tmp')
    {
        //其他文件夹不可删除
        if (
            strripos($dir, Base::$sandbox_path) === false &&
            strripos($dir, '/runtime') === false &&
            strripos($dir, '/tmp') === false &&
            strripos($dir, '/cloudfile/') === false &&
            strripos($dir, '/testdata/') === false &&
            strripos($dir, '/background/') === false &&
            strripos($dir, '/headimage/') === false &&
            strripos($dir, '/video/') === false &&
            strripos($dir, '/homephoto/') === false &&
            strripos($dir, '/contest/') === false &&
            strripos($dir, '/contestcode/') === false &&
            strripos($dir, Base::$LTPP_logs_path) === false
        ) {
            return false;
        }
        try {
            if (!file_exists($dir)) {
                return false;
            }
            if ($dir == '.' || $dir == '..') {
                return false;
            }
            if (!is_dir($dir)) {
                @unlink("$dir");
                return true;
            }
            $handle = opendir($dir);
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    Base::deleteAllFile("$dir/$file");
                }
            }
            closedir($handle);
            @rmdir($dir);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), 'deleteAllFile执行出错：' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 中文字符串截取
     * @param string [$str] 字符串
     * @param int [$index] 起始下标
     * @param int [$getlen] 获取的字符串长度
     * @return string $res 截取后的字符串 
     * @param bool $is_has_br 是否保留换行符（默认false）
     * @return string res
     */
    static public function utfsubstr(string $str = '', $index = 0, $getlen = 0, $is_has_br = false)
    {
        try {
            if (!$str) {
                return '';
            }
            mb_internal_encoding('UTF-8');
            $len = min(mb_strlen($str), $getlen);
            $s = mb_substr($str, $index, $len);
            if (!$is_has_br) {
                // 去除所有换行符
                $s = str_replace(["\r", "\n"], '', $s);
            }
            return $s;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), 'utfsubstr执行出错：' . $e->getMessage());
            return '';
        }
    }

    /**
     * 获取css
     */
    static public function getCss($name)
    {
        $path = Base::$LTPP_public_path . '/css/' . $name . '.css';
        $redis18 = Redis::connection('db18');
        $css = $redis18->get($path);
        if ($css) {
            return $css;
        }
        if (!file_exists($path)) {
            return '';
        }
        try {
            $css = @file_get_contents($path);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            $css = '';
        }
        $redis18->set($path, $css);
        return $css;
    }

    /**
     * 获取js
     */
    static public function getJs($name)
    {
        $path = Base::$LTPP_public_path . '/js/' . $name . '.js';
        $redis18 = Redis::connection('db18');
        $js = $redis18->get($path);
        if ($js) {
            return $js;
        }
        if (!file_exists($path)) {
            return '';
        }
        try {
            $js = @file_get_contents($path);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            $js = '';
        }
        $redis18->set($path, $js);
        return $js;
    }

    /**
     * 去除用户一些字段信息
     */
    static public function removeUserUnSafeData(&$user_data)
    {
        unset($user_data->bkimage);
        unset($user_data->acnum);
        unset($user_data->bkvideo);
        unset($user_data->class);
        unset($user_data->college);
        unset($user_data->email);
        unset($user_data->enrollment_year);
        unset($user_data->fans);
        unset($user_data->follow);
        unset($user_data->grade);
        unset($user_data->isdel);
        unset($user_data->isusemusic);
        unset($user_data->lastlogin);
        unset($user_data->musiclovelistid);
        unset($user_data->musicuid);
        unset($user_data->mysay);
        unset($user_data->password);
        unset($user_data->registertime);
        unset($user_data->school);
        unset($user_data->sex);
        unset($user_data->student_number);
        unset($user_data->subject);
        unset($user_data->money);
    }

    /**
     * 目录结尾不含/
     * 1.取被复制的文件夹的名字；
     * 2.写出新的文件夹的名字；
     * 3.调用此函数，将旧、新文件夹名字作为参数传递；
     * 4.如需复制文件夹内的文件，第三个参数传1，否则传0
     * @param string $source 源目录名
     * @param string $destination 目的目录名
     * @param int [$child] 复制时，是不是包含的子目录（默认包含）
     * @return void
     */
    static public function fileCopy($source, $destination, $child = 1)
    {
        //用法示例：
        // fileCopy("feiy","feiy2",1):拷贝feiy下的文件到 feiy2,包括子目录
        // fileCopy("feiy","feiy2",0):拷贝feiy下的文件到 feiy2,不包括子目录
        if (!is_dir($source)) {
            return;
        }

        if (!is_dir($destination)) {
            @mkdir($destination, 0666);
        }

        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . "/" . $entry)) {
                    if ($child)
                        Base::fileCopy($source . "/" . $entry, $destination . "/" . $entry, $child);
                } else {
                    copy($source . "/" . $entry, $destination . "/" . $entry);
                }
            }
        }
        return;
    }

    /**
     * 获取文件名的前缀和后缀
     * @param string $str 文件名
     * @return string json格式 first_name为前缀，last_name为后缀
     */
    static public function first_last_name(&$str)
    {
        $len = strlen($str);
        $point_loc = $len;
        $first_name = '';
        $last_name = '';
        // 记录最后一个point
        for ($i = $len - 1; $i >= 0; --$i) {
            if ($str[$i] == '.') {
                $point_loc = $i;
                break;
            }
        }
        if ($point_loc == $len) {
            return json_encode(['first_name' => $str, 'last_name' => $last_name]);
        }
        // 前缀
        for ($i = 0; $i < $point_loc; ++$i) {
            $first_name .= $str[$i];
        }
        // point+后缀
        for ($i = $point_loc; $i < $len; ++$i) {
            $last_name .= $str[$i];
        }
        return json_encode(['first_name' => $first_name, 'last_name' => $last_name]);
    }

    /**
     * 字符串类似Base64方式编码
     * @param string $str 待编码字符串
     * @param array $use_char_set 字符集
     * @return string $res 编码后的字符串
     */
    static public function Base64Encode($str, $use_char_set = null)
    {
        try {
            $str = (string)$str;
            if (!$use_char_set) {
                $use_char_set = Base::$char_set;
            }
            if (empty($str) || !isset($str) || strlen($str) < 1) {
                return '';
            }
            $len = strlen($str);
            $bin = '';
            for ($i = 0; $i < $len; ++$i) {
                $tem_bin = '';
                if (ord($str[$i]) > 127) {
                    $mb_str = '';
                    for ($j = $i; $j - $i < 3 && $j < $len; ++$j) {
                        $mb_str .= $str[$j];
                    }
                    $tem_bin = decbin(mb_ord($mb_str));
                    $i += 2;
                } else {
                    $tem_bin = decbin(ord($str[$i]));
                }
                $tem_bin = str_pad($tem_bin, 24, '0', STR_PAD_LEFT);
                $bin .= $tem_bin;
            }
            $base64_encode = '';
            $len = strlen($bin);
            for ($i = 0; $i < $len; $i += 6) {
                $tem_bin = '';
                for ($j = $i; $j - $i < 6 && $j < $len; ++$j) {
                    $tem_bin .= $bin[$j];
                }
                $base64_encode .= $use_char_set[bindec($tem_bin)];
            }
            return $base64_encode;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 字符串类似Base64方式解码
     * @param string $str 待解码字符串
     * @return string $res 解码后的字符串
     */
    static public function Base64Decode($str, $use_char_set = null)
    {
        try {
            $str = (string)$str;
            if (!$use_char_set) {
                $use_char_set = Base::$char_set;
            }
            if (!$str || strlen($str) < 1) {
                return '';
            }
            $len = strlen($str);
            $bin = '';
            for ($i = 0; $i < $len; ++$i) {
                $char_set_length = sizeof($use_char_set);
                $tem_num = 0;
                for ($j = 0; $j < $char_set_length; ++$j) {
                    if ($str[$i] == $use_char_set[$j]) {
                        $tem_num = $j;
                        break;
                    }
                }
                $bin .= str_pad(decbin($tem_num), 6, '0', STR_PAD_LEFT);
            }
            $base64_decode = '';
            $len = strlen($bin);
            for ($i = 0; $i < $len; $i += 24) {
                $tem_bin = '';
                for ($j = $i; $j - $i < 24 && $j < $len; ++$j) {
                    $tem_bin .= $bin[$j];
                }
                if (bindec($tem_bin) > 127) {
                    $base64_decode .= mb_chr(bindec($tem_bin));
                } else {
                    $base64_decode .= chr(bindec($tem_bin));
                }
            }
            return $base64_decode;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 从URL下载文件到本地
     */
    static public function saveNetworkFile($url, $save_path, $is_post = false, $header = [], $body = [], $body_type_is_json = false)
    {
        $file_data = '';
        try {
            if ($is_post) {
                $file_data = Base::postRequest($url, $header, $body, $body_type_is_json);
            } else {
                $file_data = Base::getRequest($url, $header);
            }
            file_put_contents($save_path, $file_data);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '保存网络文件到本地出错：' . $e->getMessage());
        }
    }

    /**
     * 获取最新文件表序号
     * @return array [id, md5(id)]
     */
    static public function getFileTableIndex()
    {
        $index = 0;
        $md5 = '';
        $index = max(1, Db::table('file_table_index')->count());
        $file_path_name = $index . Base::$db_file_path_same_name;
        $file_data_name = $index .  Base::$db_file_data_same_name;
        $file_path_has = Db::schema()
            ->hasTable($file_path_name);
        $file_data_has = Db::schema()
            ->hasTable($file_data_name);
        if (!$file_data_has || !$file_path_has) {
            Base::creatFilePathDataTable($index);
        }
        $md5 = md5($index);
        return [$index, $md5];
    }

    /**
     * 从URL下载文件到数据库
     */
    static public function saveNetworkFileToDb($my_aid, $url, $save_path, $is_post = false, $header = [], $body = [], $body_type_is_json = false)
    {
        $file_data = '';
        try {
            if ($is_post) {
                $file_data = Base::postRequest($url, $header, $body, $body_type_is_json);
            } else {
                $file_data = Base::getRequest($url, $header);
            }
            if (!$file_data) {
                // 兜底
                $file_data = file_get_contents($url);
            }
            if (!$file_data) {
                return false;
            }
            $id = Base::insertToDb(Base::getFileDataTableName($save_path), [
                'data' => $file_data
            ]);
            if (!$id) {
                return false;
            }
            Base::insertToDb(Base::getFilePathTableName($save_path), [
                'path' => $save_path,
                'file_id' => $id,
                'userid' => $my_aid,
                'time' => date('Y-m-d H:i:s', time())
            ]);
            return true;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '保存网络文件到数据库出错：' . $e->getMessage());
        }
        return false;
    }

    /**
     * 获取QQ邮箱图片保存本地，返回URL
     */
    static public function getEmailImageToLtppUrl($email = '')
    {
        try {
            /**
             * 邮箱为空 || 邮箱不是QQ邮箱
             * 返回随机一条数据库里的图片URL
             */
            if (
                !$email ||
                strripos($email, '@qq.com') === false
            ) {
                return Image::randImage();
            }
            // 申请图片路径
            $local_path = Base::creatFilePath('png');
            Base::$GLOBlinuxurl = Base::getGLOBlinuxurl();
            // 保存网络图片到本地
            $email_image = 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . $email . '&spec=640';
            $save_res = Base::saveNetworkFileToDb(Base::getRobotId(), $email_image, $local_path);
            if ($save_res) {
                return Base::$GLOBlinuxurl . $local_path;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return Image::randImage();
    }

    /**
     * 获取GLOBlinuxurl
     * @return string $url linux url地址
     */
    static public function getGLOBlinuxurl()
    {
        Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
        return Base::$GLOBlinuxurl;
    }

    /**
     * 通过id获取一个用户头像
     * @param int $id 用户id
     * @return string $res 用户头像地址
     */
    static public function getUserHeadimage(&$user_id)
    {
        $data = Base::getUserData($user_id);
        if (isset($data->headimage)) {
            return $data->headimage;
        }
        return '';
    }

    /**
     * 判断分页参数是否合法，不合法纠正
     * @param int $page 页数
     * @param int $limit 每页多少条数据
     * @return void
     */
    static public function judgePageLimitIsSafe(&$page, &$limit)
    {
        if (!$page || !is_numeric($page) || $page <= 0) {
            $page = 1;
        }
        if (!$limit || !is_numeric($limit) || $limit <= 0 || $limit > Base::$db_get_limit) {
            $limit = Base::$db_get_limit;
        }
    }

    /**
     * 判断limit参数是否合法，不合法纠正
     * @param int $limit 每页多少条数据
     * @return void
     */
    static public function judgeLimitIsSafe(&$limit)
    {
        if (!$limit || !is_numeric($limit) || $limit <= 0 || $limit > Base::$db_get_limit) {
            $limit = Base::$db_get_limit;
        }
    }

    /**
     * 分页
     * @param int $page
     * @param int $limit
     * @param array $data
     */
    static public function paging($page, $limit, &$data)
    {
        if (!$page) {
            $page = 1;
        }
        if (!$limit) {
            $limit = 1;
        }
        if ($page <= 0) {
            $page = 1;
        }
        $len = sizeof($data);
        $start_loc = max(0, ($page - 1) * $limit);
        $end_loc = min($start_loc + $limit - 1, $len - 1);
        $res = [];
        for ($i = $start_loc; $i <= $end_loc; ++$i) {
            $res[] = $data[$i];
        }
        return $res;
    }

    /**
     * 获取root用户id
     * @return string $id root用户id
     */
    static public function getRootId()
    {
        $redis5 = Redis::connection('db5');
        if (!$redis5->get('rootid')) {
            $rootdbaid = Db::table('user')
                ->where('grade', 3)
                ->where('name', 'root')
                ->orderBy('id', 'desc')
                ->where('isdel', 0)
                ->select('id')
                ->first();
            if ($rootdbaid) {
                $redis5->set('rootid', $rootdbaid->id);
            } else {
                return 0;
            }
        }
        return $redis5->get('rootid');
    }

    /**
     * 获取机器人 ID
     */
    static public function getRobotId()
    {
        $redis5 = Redis::connection('db5');
        if ($redis5->get('robotid')) {
            return $redis5->get('robotid');
        }
        $user_db = Db::table('user')
            ->where('name', '机器人')
            ->where('isdel', 0)
            ->orderBy('id', 'desc')
            ->select('id')
            ->first();
        if (!$user_db) {
            Base::creatRobot();
            $user_db = Db::table('user')
                ->where('name', '机器人')
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->select('id')
                ->first();
        }
        $redis5->set('robotid', $user_db->id);
        return $user_db->id;
    }

    /**
     * 获取机器人邮箱
     */
    static public function getRobotEmail()
    {
        return Base::getSettingKeyData('robot_email');
    }

    /**
     * 更新机器人邮箱
     */
    static public function updateRobotUsersEmail($old_email = '')
    {
        try {
            if (!$old_email) {
                return;
            }
            $new_email = Base::getRobotEmail();
            if ($old_email == $new_email) {
                return;
            }
            Db::table('user')
                ->where('email', $old_email)
                ->update([
                    'email' => $new_email
                ]);
            Base::clearAllUserDataRedis();
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 机器人不存在，创建机器人账号
     */
    static public function creatRobot()
    {
        try {
            $redis5 = Redis::connection('db5');
            if ($redis5->exists('robotid')) {
                return $redis5->get('robotid');
            }
            $robot_db = Db::table('user')
                ->where('name', Base::$robot_name)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->select('id')
                ->first();
            $redis5->set('robotid', $robot_db->id);
            if ($robot_db) {
                return $robot_db->id;
            }
            // 机器人账号不存在，立即发送root邮件通知
            $root_id = Base::getRootId();
            $root_db = Base::getUserData($root_id);
            if (!$root_db) {
                return $root_id;
            }

            $data = [
                'name' => '机器人',
                'password' => Base::passwordEncryption(rand(1, 100000) . time()),
                'sex' => '男',
                'registertime' => date('Y-m-d H:i:s', time()),
                'headimage' => Image::randImage(),
                'fans' => 0,
                'follow' => 0,
                'grade' => 1,
                'email' => Base::getRobotEmail(),
                'school' => '无',
                'enrollment_year' => 0,
                'subject' => '无',
                'class' => '无',
                'money' => 1
            ];
            $res_id = Base::insertToDb('user', $data);
            $content = '系统机器人的账号不存在，系统已自动重新生成！机器人账号用户名：' . Base::$robot_name;
            $redis5->set('robotid', $res_id);
            $offline = (int) Base::getSettingKeyData('offline');
            if ($offline == 0) {
                Robot::sendChatToOneUserMsg($root_id, $content);
            }
            return $res_id;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return 0;
    }

    /**
     * 通过字符串authorization获取uid信息
     */
    static public function getUidByToken($authorization)
    {
        try {
            $data = JwtToken::verify(1, $authorization);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return '';
        }
        if (!$data || !isset($data['extend']) || !isset($data['extend']['id'])) {
            return '';
        }
        return $data['extend']['id'];
    }

    /**
     * 根据ID获取KEY
     */
    static public function getKeyById($db_name, $id, $key)
    {
        $db = Db::table($db_name)
            ->where('id', $id)
            ->select($key)
            ->first();
        if (!$db) {
            return -1;
        }
        return $db->$key;
    }

    /**
     * 分页获取数据（降序排序）
     * @param string $name 数据库名
     * @param int $id 查找的数据ID
     * @param int $limit 数据条数限制
     * @param array $select 选择的数据库字段
     * @param bool $is_next 寻找方向（true为找下面，否则找上面）
     */
    static public function getDataByLimit($db_name, $id, $limit, $select = ['id'], $is_next = true)
    {
        $res = [];
        Base::judgeLimitIsSafe($limit);
        if ($id) {
            $res = Db::table($db_name)
                ->where('id', $is_next ? '<' : '>', $id)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->select($select)
                ->get();
        } else {
            $res = Db::table($db_name)
                ->where('isdel', 0)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->select($select)
                ->get();
        }
        return $res;
    }

    /**
     * UID转ID
     */
    static public function getIdByUid($uid)
    {
        $id = 0;
        try {
            if (!is_string($uid)) {
                return $id;
            }
            if (strlen($uid) == 0) {
                return $id;
            }
            $loc = ord($uid[0]) - ord('0');
            if ($loc >= sizeof(Base::$id_char_set)) {
                return $id;
            }
            $base_str = substr($uid, 1);
            $id = Base::Base64Decode($base_str, Base::$id_char_set[$loc]);
        } catch (Exception $e) {
        }
        return $id;
    }

    /**
     * 普通ID转UID
     */
    static public function getUidById($id)
    {
        $uid = '';
        try {
            if (!is_numeric($id) || $id <= 0) {
                return '';
            }
            $num = rand(0, sizeof(Base::$id_char_set) - 1);
            $base_str = Base::Base64Encode(strval($id), Base::$id_char_set[$num]);
            $uid = $num . $base_str;
        } catch (Exception $e) {
        }
        return $uid;
    }

    /**
     * 字符串解密
     * @param string $encode_str 待解密字符串
     * @return string $str 解密后字符串
     */
    static public function decodeStr($encode_str = '')
    {
        $str = '';
        try {
            if (!is_string($encode_str)) {
                return $str;
            }
            if (strlen($encode_str) == 0) {
                return $str;
            }
            $loc = ord($encode_str[0]) - ord('0');
            if ($loc >= sizeof(Base::$id_char_set)) {
                return $str;
            }
            $base_str = substr($encode_str, 1);
            $str = Base::Base64Decode($base_str, Base::$id_char_set[$loc]);
        } catch (Exception $e) {
        }
        return $str;
    }

    /**
     * 字符串加密
     * @param string $encode_str 待加密字符串
     * @return string $str 加密后字符串
     */
    static public function encodeStr($str = '')
    {
        $uid = '';
        try {
            $num = rand(0, sizeof(Base::$id_char_set) - 1);
            $base_str = Base::Base64Encode(strval($str), Base::$id_char_set[$num]);
            $uid = $num . $base_str;
        } catch (Exception $e) {
        }
        return $uid;
    }

    /**
     * 竞赛代码ID加密
     */
    static public function getUuidById($id = 0)
    {
        $uuid = '';
        try {
            if ($id <= 0 || !is_numeric($id)) {
                return '';
            }
            $num = rand(0, sizeof(Base::$id_char_set) - 1);
            $base_str = Base::Base64Encode(strval($id), Base::$id_char_set[$num]);
            $uuid = $num . $base_str;
            $num = rand(0, sizeof(Base::$id_char_set) - 1);
            $base_str = Base::Base64Encode(strval($uuid), Base::$id_char_set[$num]);
            $uuid = $num . $base_str;
        } catch (Exception $e) {
        }
        return $uuid;
    }

    /**
     * 竞赛代码ID解密
     */
    static public function getIdByUuid($uuid = '')
    {
        $id = 0;
        try {
            if (!is_string($uuid)) {
                return $id;
            }
            if (strlen($uuid) == 0) {
                return $id;
            }
            $loc = ord($uuid[0]) - ord('0');
            if ($loc >= sizeof(Base::$id_char_set)) {
                return $id;
            }
            $base_str = substr($uuid, 1);
            $uid = Base::Base64Decode($base_str, Base::$id_char_set[$loc]);
            $loc = ord($uid[0]) - ord('0');
            if ($loc >= sizeof(Base::$id_char_set)) {
                return $id;
            }
            $base_str = substr($uid, 1);
            $id = Base::Base64Decode($base_str, Base::$id_char_set[$loc]);
        } catch (Exception $e) {
        }
        return $id;
    }

    /**
     * 竞赛普通ID转UID
     */
    static public function getContestUidById($id)
    {
        if ($id <= 0 || !is_numeric($id)) {
            return '';
        }
        $num = 1;
        $base_str = Base::Base64Encode(strval($id), Base::$id_char_set[$num]);
        $uid = $num . $base_str;
        return $uid;
    }

    /**
     * 聊天用户ID转UID
     */
    static public function getChatUserUidById($id)
    {
        if ($id <= 0 || !is_numeric($id)) {
            return '';
        }
        $num = 0;
        $base_str = Base::Base64Encode(strval($id), Base::$id_char_set[$num]);
        $uid = $num . $base_str;
        return $uid;
    }

    /**
     * 加密数据给前端，递归处理
     */
    static public function dataToSafe(&$data, $is_chat_or_rank = false)
    {
        if (!is_array($data) && !is_object($data)) {
            return;
        }
        foreach ($data as $key => &$t_data) {
            if (!is_array($t_data) && !is_object($t_data) && isset(Base::$to_safe_delete_key[$key])) {
                // 删除不需要显示的字段
                unset($data->$key);
                continue;
            }
            if (isset(Base::$to_safe_key[$key])) {
                $t_data = $is_chat_or_rank ? Base::getChatUserUidById($t_data) : Base::getUidById($t_data);
            }
            if (is_array($t_data) || is_object($t_data)) {
                Base::dataToSafe($t_data, $is_chat_or_rank);
            }
        }
    }

    /**
     * 前端给后端解密数据，递归处理
     */
    static public function dataToUnSafe(&$data)
    {
        if (!is_array($data) && !is_object($data)) {
            return;
        }
        foreach ($data as $key => &$t_data) {
            if (isset(Base::$to_safe_key[$key])) {
                $t_data = Base::getIdByUid($t_data);
            }
            if (is_array($t_data) || is_object($t_data)) {
                Base::dataToUnSafe($t_data);
            }
        }
    }

    /**
     * 写入文件
     * @param string $path 文件路径
     * @param string $content 写入的内容
     */
    static public function writeToFile($path, $content = '')
    {
        Base::judgeCreatPath($path);
        $times = 0;
        while (1) {
            try {
                ++$times;
                if ($times > Base::$write_to_file_retry_max_times) {
                    Base::sendErrorNotice(
                        '文件路径：' . $path . '<br>' . '文件内容：' . $content,
                        'Base::writeToFile写入文件失败超过最大重试次数'
                    );
                    return;
                }
                $result = file_put_contents($path, $content);
                if ($result !== false) {
                    // 写入成功
                    return;
                }
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
                return;
            }
        }
    }

    /**
     * 各模块文件路径根目录
     */
    static public function getPathMd5($user_id)
    {
        return md5($user_id);
    }

    /**
     * 获取某个配置信息
     */
    static public function getSettingKeyData($key)
    {
        $res = null;
        $redis5 = Redis::connection('db5');
        if ($redis5->get($key)) {
            $res = $redis5->get($key);
        } else {
            $db = Db::table('setting')
                ->orderBy('id', 'desc')
                ->first();
            if (!$db) {
                return $res;
            }
            try {
                $res = $db->$key;
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
                return null;
            }
            $redis5->setEx($key, 0, $res);
        }
        return $res;
    }

    /**
     * 生成随机字符串
     * @param string
     */
    static public function randString()
    {
        $res = '';
        try {
            $res = uniqid() . mt_rand(1, 100000) . time();
        } catch (Exception $e) {
            return $res;
        }
        return $res;
    }

    /**
     * 获取缓存中代码信息
     * @param int $code_id
     */
    static public function getCodeData($code_id)
    {
        try {
            if (!is_numeric($code_id)) {
                return [];
            }
            $redis33 = Redis::connection('db33');
            $key = 'CodeData' . $code_id;
            $db = $redis33->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('codehistory')
                ->where('id', $code_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $redis33->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中用户信息
     * @param int $user_id
     */
    static public function getUserData($user_id)
    {
        try {
            if (!is_numeric($user_id)) {
                return [];
            }
            $redis8 = Redis::connection('db8');
            $key = 'UserData' . $user_id;
            $db = $redis8->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('user')
                ->where('id', $user_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $redis8->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中群组信息
     * @param int $group_id
     */
    static public function getGroupData($group_id)
    {
        try {
            if (!is_numeric($group_id)) {
                return [];
            }
            $redis20 = Redis::connection('db20');
            $key = 'Group' . $group_id;
            $db = $redis20->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('group')
                ->where('id', $group_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $redis20->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中竞赛信息
     * @param int $contest_id
     */
    static public function getContestData($contest_id)
    {
        try {
            if (!is_numeric($contest_id)) {
                return [];
            }
            $redis21 = Redis::connection('db21');
            $key = 'ContestData' . $contest_id;
            $db = $redis21->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('contest')
                ->where('id', $contest_id)
                ->where('isdel', 0)
                ->select(Contest::$contest_db_key)
                ->first();
            if (!$db) {
                return [];
            }
            $db->content = Base::removeImgAlt($db->content);
            $redis21->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中题单信息
     * @param int $question_sheet_id
     */
    static public function getQuestionSheetData($question_sheet_id)
    {
        try {
            if (!is_numeric($question_sheet_id)) {
                return [];
            }
            $redis22 = Redis::connection('db22');
            $key = 'QuestionSheetData' . $question_sheet_id;
            $db = $redis22->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('question_sheet')
                ->where('id', $question_sheet_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $db->content = Base::removeImgAlt($db->content);
            $redis22->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中题单题目列表信息
     * @param int $question_sheet_id
     */
    static public function getQuestionSheetProblemListData($question_sheet_id)
    {
        try {
            if (!is_numeric($question_sheet_id)) {
                return [];
            }
            $redis34 = Redis::connection('db34');
            $key = 'QuestionSheetProblemListData' . $question_sheet_id;
            $db = $redis34->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('question_sheet_data')
                ->where('question_sheet_id', $question_sheet_id)
                ->where('isdel', 0)
                ->select('question_id')
                ->distinct()
                ->get();
            if (!$db) {
                return [];
            }
            $redis34->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中文章信息
     * @param int $article_id
     */
    static public function getArticleData($article_id)
    {
        try {
            if (!is_numeric($article_id)) {
                return [];
            }
            $redis25 = Redis::connection('db25');
            $key = 'ArticleData' . $article_id;
            $db = $redis25->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('article')
                ->where('id', $article_id)
                ->where('isdel', 0)
                ->select(Article::$article_db_key)
                ->first();
            if (!$db) {
                return [];
            }
            $user_db = Base::getUserData($db->writerid);
            if (!$user_db) {
                return [];
            }
            $db->writer = $user_db->name;
            $data = Db::table('article_data')
                ->where('article_id', $article_id)
                ->select('data')
                ->first();
            if (!$db) {
                return [];
            }
            $db->article = Base::removeImgAlt($data->data);
            $redis25->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中题库信息
     * @param int $oj_id
     */
    static public function getOjData($oj_id)
    {
        try {
            if (!is_numeric($oj_id)) {
                return [];
            }
            $redis26 = Redis::connection('db26');
            $key = 'OjData' . $oj_id;
            $db = $redis26->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('oj')
                ->where('id', $oj_id)
                ->where('isdel', 0)
                ->select(Oj::$oj_db_key)
                ->first();
            if (!$db) {
                return [];
            }
            $db->problemContent = Base::removeImgAlt($db->problemContent);
            $redis26->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中App信息
     * @param int $app_id
     */
    static public function getAppData($app_id)
    {
        try {
            if (!is_numeric($app_id)) {
                return [];
            }
            $redis36 = Redis::connection('db36');
            $key = 'AppData' . $app_id;
            $db = $redis36->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('app')
                ->where('id', $app_id)
                ->where('isdel', 0)
                ->select(App::$app_db_key)
                ->first();
            if (!$db) {
                return [];
            }
            $db->content = Base::removeImgAlt($db->content);
            $redis36->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 获取缓存中竞赛代码信息
     * @param int $contestrank_id
     */
    static public function getContestRankData($contestrank_id)
    {
        try {
            if (!is_numeric($contestrank_id)) {
                return [];
            }
            $redis28 = Redis::connection('db28');
            $key = 'ContestRank' . $contestrank_id;
            $db = $redis28->get($key);
            if ($db) {
                return json_decode($db, false);
            }
            $db = Db::table('contestrank')
                ->where('id', $contestrank_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $redis28->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return [];
    }

    /**
     * 根据ID更新代码缓存信息
     * @param int $code_id
     */
    static public function updateCodeDataRedis($code_id)
    {
        if (!is_numeric($code_id)) {
            return;
        }
        $redis33 = Redis::connection('db33');
        $key = 'CodeData' . $code_id;
        $db = Db::table('codehistory')
            ->where('id', $code_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            $redis33->del($key);
            return;
        }
        $redis33->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 获取DB中用户信息
     * @param int $user_id
     */
    static public function getUserDataFromDb($user_id)
    {
        try {
            if (!is_numeric($user_id)) {
                return [];
            }
            $db = Db::table('user')
                ->where('id', $user_id)
                ->where('isdel', 0)
                ->first();
            if (!$db) {
                return [];
            }
            $db->mysay = Base::removeImgAlt($db->mysay);
            return $db;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [];
        }
    }

    /**
     * 根据ID更新用户缓存信息
     * @param int $user_id
     */
    static public function updateUserDataRedis($user_id)
    {
        if (!is_numeric($user_id)) {
            return;
        }
        $redis8 = Redis::connection('db8');
        $key = 'UserData' . $user_id;
        $db = Db::table('user')
            ->where('id', $user_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            $redis8->del($key);
            return;
        }
        $redis8->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新群组缓存信息
     * @param int $group_id
     */
    static public function updateGroupDataRedis($group_id)
    {
        if (!is_numeric($group_id)) {
            return;
        }
        $redis20 = Redis::connection('db20');
        $key = 'Group' . $group_id;
        $db = Db::table('group')
            ->where('id', $group_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            $redis20->del($key);
            return;
        }
        $redis20->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新竞赛缓存信息
     * @param int $contest_id
     */
    static public function updateContestDataRedis($contest_id)
    {
        if (!is_numeric($contest_id)) {
            return;
        }
        $redis21 = Redis::connection('db21');
        $key = 'ContestData' . $contest_id;
        $db = Db::table('contest')
            ->where('id', $contest_id)
            ->where('isdel', 0)
            ->select(Contest::$contest_db_key)
            ->first();
        if (!$db) {
            $redis21->del($key);
            return;
        }
        $redis21->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新题单缓存信息
     * @param int $question_sheet_id
     */
    static public function updateQuestionSheetDataRedis($question_sheet_id)
    {
        if (!is_numeric($question_sheet_id)) {
            return;
        }
        $redis22 = Redis::connection('db22');
        $key = 'QuestionSheetData' . $question_sheet_id;
        $db = Db::table('question_sheet')
            ->where('id', $question_sheet_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            $redis22->del($key);
            return;
        }
        $redis22->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新题单题目列表缓存信息
     * @param int $question_sheet_id
     */
    static public function updateQuestionSheetProblemListDataRedis($question_sheet_id)
    {
        if (!is_numeric($question_sheet_id)) {
            return;
        }
        $redis34 = Redis::connection('db34');
        $key = 'QuestionSheetProblemListData' . $question_sheet_id;
        $db = Db::table('question_sheet_data')
            ->where('question_sheet_id', $question_sheet_id)
            ->where('isdel', 0)
            ->select('question_id')
            ->distinct()
            ->get();
        if (!$db) {
            $redis34->del($key);
            return;
        }
        $redis34->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新文章缓存信息
     * @param int $article_id
     */
    static public function updateArticleDataRedis($article_id)
    {
        if (!is_numeric($article_id)) {
            return;
        }
        $redis25 = Redis::connection('db25');
        $key = 'ArticleData' . $article_id;
        $db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->select(Article::$article_db_key)
            ->first();
        if (!$db) {
            $redis25->del($key);
            return;
        }
        $user_db = Base::getUserData($db->writerid);
        if (!$user_db) {
            return;
        }
        $db->writer = $user_db->name;
        $data = Db::table('article_data')
            ->where('article_id', $article_id)
            ->select('data')
            ->first();
        if ($data) {
            $db->article = $data->data;
        }
        $redis25->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新题库缓存信息
     * @param int $oj_id
     */
    static public function updateOjDataRedis($oj_id)
    {
        if (!is_numeric($oj_id)) {
            return;
        }
        $redis26 = Redis::connection('db26');
        $key = 'OjData' . $oj_id;
        $db = Db::table('oj')
            ->where('id', $oj_id)
            ->where('isdel', 0)
            ->select(Oj::$oj_db_key)
            ->first();
        if (!$db) {
            $redis26->del($key);
            return;
        }
        $redis26->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新应用缓存信息
     * @param int $app_id
     */
    static public function updateAppDataRedis($app_id)
    {
        if (!is_numeric($app_id)) {
            return;
        }
        $redis36 = Redis::connection('db36');
        $key = 'AppData' . $app_id;
        $db = Db::table('app')
            ->where('id', $app_id)
            ->where('isdel', 0)
            ->select(App::$app_db_key)
            ->first();
        if (!$db) {
            $redis36->del($key);
            return;
        }
        $redis36->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 删除全部用户用户缓存信息
     */
    static public function clearAllUserDataRedis()
    {
        $redis8 = Redis::connection('db8');
        $redis8->flushdb();
    }

    /**
     * AC赠送虚拟币
     * @param int $my_aid 用户id
     * @param string $problem_name 题目名称
     * @param string $userlanguage 语言
     */
    static public function addAcMoney($my_aid, $problem_name, $userlanguage = 'C++')
    {
        if (!is_numeric($my_aid)) {
            return;
        }
        Db::table('user')
            ->where('id', $my_aid)
            ->increment('money', Base::$ac_money);
        Base::updateUserDataRedis($my_aid);
        Robot::sendChatToOneUserMsg($my_aid, '恭喜您使用' . $userlanguage . '编程语言AC<strong>【' . $problem_name . '】</strong>，奖励您<strong>' . Base::$ac_money . '</strong>个学虫币！（北京时间：<strong>' . date('Y-m-d H:i:s', time()) . '</strong>）');
    }

    /**
     * AK赠送虚拟币
     * @param int $my_aid 用户id
     * @param object $contestdb 竞赛名称
     */
    static public function addAkMoney($my_aid, $contestdb)
    {
        if (!is_numeric($my_aid)) {
            return;
        }
        $my = Base::getUserData($my_aid);
        Db::table('user')
            ->where('id', $my_aid)
            ->increment('money', Base::$ak_money);
        Base::updateUserDataRedis($my_aid);
        $title = 'LTPP竞赛AK通知';
        $content_robot = '恭喜您在LTPP平台的<strong>【' . $contestdb->name . '】</strong>中AK，给予<strong>' . Base::$ak_money . '</strong>学虫币奖励（北京时间：<strong>' . date('Y-m-d H:i:s', time()) . '</strong>）';
        $content_email = '恭喜您在LTPP平台的【' . $contestdb->name . '】中AK，给予' . Base::$ak_money . '学虫币奖励（北京时间：' . date('Y-m-d H:i:s', time()) . '）';
        Robot::sendChatToOneUserMsg($my_aid, $content_robot);
        RedisQueue::send(Base::$redis_queue_send_mail_name, [
            'to' => $my->email,
            'title' => $title,
            'content' => $content_email
        ]);
    }

    /**
     * 判断是否是Docker环境
     */
    static public function judgeIsDocker()
    {
        try {
            $is_in_docker = file_exists('/.dockerenv') ||
                (file_exists('/proc/1/cgroup') && is_file('/proc/1/cgroup') &&
                    strpos(file_get_contents('/proc/1/cgroup'), 'docker') !== false);
            if ($is_in_docker) {
                return true;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * @param string $code
     * @param string $userlanguage
     */
    static public function judgeCodeSafe($code, $userlanguage)
    {
        if (!$code) {
            return [
                'code' => -1,
                'msg' => Base::$empty_code_msg,
                'code_id' => 0
            ];
        }
        if (!$userlanguage) {
            $userlanguage = '';
        }
        $userlanguage = strtolower($userlanguage);
        if (!isset(Base::$language_map[$userlanguage])) {
            return [
                'code' => -1,
                'msg' => Base::$param_error_msg,
                'code_id' => 0
            ];
        }
        $userlanguage = Base::$language_map[$userlanguage];
        switch ($userlanguage) {
            case Language::c:
                break;
            case Language::cpp:
                break;
            case Language::java:
                break;
            case Language::python:
                break;
            case Language::golang:
                break;
            case Language::php:
                break;
            case Language::javascript:
                break;
            case Language::rust:
                break;
            case Language::typescript:
                break;
            case Language::csharp:
                break;
            case Language::ruby:
                break;
            default:
                return json([
                    'code' => -1,
                    'msg' => Base::$param_error_msg,
                    'code_id' => 0,
                ]);
        }
        return [
            'code' => 1,
            'msg' => Base::$code_safe,
            'code_id' => 0
        ];
    }


    /**
     * 判断判题机是否安装
     */
    static public function judgeJudgeInstall()
    {
        $path_judge = Base::$judge_install_path . Base::$judge_name;
        $path_sandbox = Base::$sandbox_path;
        try {
            $has_judge = file_exists($path_judge);
            $has_sandbox = file_exists($path_sandbox);
            if ($has_judge && $has_sandbox) {
                return true;
            }
            Base::deleteAllFile(Base::$judge_install_path);
            Base::judgeCreatPath(Base::$judge_install_path);
            Base::runExec('cp -f /home/LTPP/InstallMust/JudgeServer/judge ' . Base::$judge_install_path, $out);
            if ($out) {
                Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), '判题机安装出错：' . $out);
                return false;
            }
            Base::chmodFile('/JudgeServer', 0555);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '判题机安装出错：' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 修改权限
     * @param string $path
     * @param int $num
     */
    static public function chmodFile($path, $num = 0444)
    {
        try {
            if (!file_exists($path)) {
                return;
            }
            if ($path == '.' || $path == '..') {
                return;
            }
            if (!is_dir($path)) {
                chmod($path, $num);
                return;
            }
            $dirs = scandir($path);
            foreach ($dirs as &$tem) {
                if ($tem == '.' || $tem == '..') {
                    continue;
                }
                $tempath = $path . '/' . $tem;
                Base::chmodFile($tempath, $num);
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return;
        }
    }

    /**
     * 修改所有者
     * @param string $path
     * @param int $num
     */
    static public function chownFile($path, $user_id)
    {
        try {
            if (!file_exists($path)) {
                return;
            }
            if ($path == '.' || $path == '..') {
                return;
            }
            if (!is_dir($path)) {
                chown($path, $user_id);
                return;
            }
            $dirs = scandir($path);
            foreach ($dirs as &$tem) {
                if ($tem == '.' || $tem == '..') {
                    continue;
                }
                $tempath = $path . '/' . $tem;
                Base::chownFile($tempath, $user_id);
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return;
        }
    }

    /**
     * 代码写入文件
     * @param string $userlanguage
     * @param string $code
     * @param string $filepath
     * @param string $runcodefilepath     
     * @return array $res
     */
    static public function writeCodeToFile($userlanguage, $code, $filepath, $runcodefilepath)
    {
        try {
            if (!$userlanguage) {
                $userlanguage = '';
            }
            $userlanguage = strtolower($userlanguage);
            if (!isset(Base::$language_map[$userlanguage])) {
                return [
                    'code' => -1,
                    'result' => Base::$no_support_language_msg,
                    'usememory' => 0,
                    'usetime' => 0
                ];
            }
            $userlanguage = Base::$language_map[$userlanguage];
            switch ($userlanguage) {
                case Language::rust:
                    Base::writeToFile($runcodefilepath . '.rs', $code);
                    break;
                case Language::c:
                    Base::writeToFile($runcodefilepath . '.c', $code);
                    break;
                case Language::cpp:
                    Base::writeToFile($runcodefilepath . '.cpp', $code);
                    break;
                case Language::golang:
                    Base::writeToFile($runcodefilepath . '.go', $code);
                    break;
                case Language::java:
                    $runcodefilepath = $filepath . 'Main';
                    Base::writeToFile($runcodefilepath . '.java', $code);
                    break;
                case Language::javascript:
                    Base::writeToFile($runcodefilepath . '.js', $code);
                    break;
                case Language::typescript:
                    Base::writeToFile($runcodefilepath . '.ts', $code);
                    break;
                case Language::php:
                    Base::writeToFile($runcodefilepath . '.php', $code);
                    break;
                case Language::python:
                    Base::writeToFile($runcodefilepath . '.py', $code);
                    break;
                case Language::ruby:
                    Base::writeToFile($runcodefilepath . '.rb', $code);
                    break;
                case Language::csharp:
                    Base::writeToFile($runcodefilepath . '.cs', $code);
                    break;
                default:
                    return [
                        'code' => -1,
                        'result' => Base::$no_support_language_msg,
                        'usememory' => 0,
                        'usetime' => 0
                    ];
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return [
                'code' => -1,
                'result' => Base::$judge_code_save_error_msg . '！',
                'usememory' => 0,
                'usetime' => 0
            ];
        }
        return [
            'code' => 1,
            'result' => '',
            'usememory' => 0,
            'usetime' => 0
        ];
    }

    /**
     * 运行shell命令
     */
    public static function runExec($command = '', &$out = '', &$run_exec_code = 0)
    {
        try {
            $run_exec_code = 0;
            $pipes = [];
            $descriptorspec = [
                0 => ['pipe', 'r'],  // 标准输入
                1 => ['pipe', 'w'],  // 标准输出
                2 => ['pipe', 'w']   // 标准错误输出
            ];
            $process = proc_open($command, $descriptorspec, $pipes);
            if (is_resource($process)) {
                // 关闭标准输入管道
                fclose($pipes[0]);
                // 读取标准输出和标准错误输出
                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);
                // 关闭标准输出和标准错误输出管道
                fclose($pipes[1]);
                fclose($pipes[2]);
                // 注册信号处理程序                
                pcntl_signal(SIGTERM, function ($signo) {
                    $pid = getmypid();
                    posix_kill(-$pid, SIGKILL);
                });
                $pid = intval(proc_get_status($process)['pid']);
                // 等待进程终止
                pcntl_waitpid($pid, $run_exec_code);
                // 输出结果或错误信息
                if (!empty($stdout)) {
                    $out = $stdout;
                }
                if (!empty($stderr)) {
                    $out =  $stderr;
                }
                // 取消注册信号处理程序
                pcntl_signal(SIGTERM, SIG_DFL);
                // 关闭进程
                proc_close($process);
            }
        } catch (Exception $e) {
            $out = Base::$judge_error_msg;
            $run_exec_code = 0;
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 运行
     * @param string $userlanguage
     * @param string $filepath
     * @param string $inpath
     * @param string $outpath
     * @param string $errpath
     * @param string $runcodefilepath
     * @param int $limittime
     * @param int limitmemory
     */
    static public function run($userlanguage, $filepath, $inpath, $outpath, $errpath, $runcodefilepath, $limittime, $limitmemory)
    {
        $out = '';
        $compiler_cmd = '\'\'';
        $run_cmd = '\'\'';
        try {
            if (!$userlanguage) {
                $userlanguage = '';
            }
            $userlanguage = strtolower($userlanguage);
            if (!isset(Base::$language_map[$userlanguage])) {
                return json_encode([
                    'status' => Base::$judge_server_error,
                    'time_used' => 0,
                    'memory_used' => 0,
                    'msg' => Base::$param_error_msg
                ]);
            }
            $userlanguage = Base::$language_map[$userlanguage];
            $compiler_timeout_time = intval(Base::getSettingKeyData('compiler_time_limit'));
            // 运行
            switch ($userlanguage) {
                case Language::rust:
                    $compiler_cmd = '/root/.cargo/bin/rustc@-C@opt-level=3@-o@' . $runcodefilepath . '@' . $runcodefilepath . '.rs';
                    $run_cmd = $runcodefilepath;
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd  . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::c:
                    $compiler_cmd = '/usr/bin/g++@-o@' . $runcodefilepath . '@' . $runcodefilepath . '.c@-std=c++2a';
                    $run_cmd =  $runcodefilepath;
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd  . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::cpp:
                    $compiler_cmd = '/usr/bin/g++@-o@' . $runcodefilepath . '@' . $runcodefilepath . '.cpp@-std=c++2a';
                    $run_cmd =  $runcodefilepath;
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::golang:
                    Base::runExec('/usr/bin/go env -w GO111MODULE=auto');
                    $compiler_cmd = '/usr/bin/go@build@-o@' . $filepath . '@' . $runcodefilepath . '.go';
                    $run_cmd =  $runcodefilepath;
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd  . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::java:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $runcodefilepath = $filepath . 'Main';
                    $compiler_cmd = '/usr/bin/javac@-J-Dfile.encoding=UTF-8@' . $runcodefilepath . '.java';
                    $run_cmd = '/usr/bin/java@-cp@' . $filepath . '@Main';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::javascript:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '\'\'';
                    $run_cmd = '/usr/bin/node@' . $runcodefilepath . '.js';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::typescript:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '/usr/local/nodejs/bin/tsc@-t@es2022@--outFile@' . $runcodefilepath . '.js@' . $runcodefilepath . '.ts';
                    $run_cmd =  '/usr/bin/node@' . $runcodefilepath . '.js';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::php:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '\'\'';
                    $run_cmd = '/usr/bin/php@' . $runcodefilepath . '.php';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::python:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '\'\'';
                    $run_cmd = '/usr/bin/python3@' . $runcodefilepath . '.py';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::ruby:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '\'\'';
                    $run_cmd = '/usr/bin/ruby@' . $runcodefilepath . '.rb';
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                    break;
                case Language::csharp:
                    $limittime <<= 1;
                    $limitmemory <<= 1;
                    $compiler_cmd = '/usr/bin/mcs@-out:' . $runcodefilepath . '@' . $runcodefilepath . '.cs';
                    $run_cmd = '/usr/bin/mono@' . $runcodefilepath;
                    Base::runExec(Base::$judgepath . ' ' . $compiler_cmd . ' ' . $run_cmd . ' ' . $compiler_timeout_time . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath, $out);
                default:
                    break;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            return json_encode([
                'status' => Base::$judge_server_error,
                'time_used' => 0,
                'memory_used' => 0,
                'msg' => $e->getMessage()
            ]);
        }
        return $out;
    }

    /**
     * 加载服务器配置
     * @param string $reply 消息
     */
    static public function loadLinuxData(&$reply = '')
    {
        $reply = '';
        $out = '';
        $name = '服务器信息：';
        $reply .= $name;
        Base::runExec('lsb_release -a', $out);
        $reply .= $out;
        $out = '';
        Base::runExec('uname -a', $out);
        $reply .= $out;
        $out = '';
        $name = 'CPU型号：';
        $reply .= $name;
        Base::runExec("grep 'model name' /proc/cpuinfo |uniq", $out);
        $reply .= $out;
        $out = '';
        $name = 'CPU物理个数 ：';
        $reply .= $name;
        Base::runExec("grep 'physical id' /proc/cpuinfo |sort |uniq |wc -l", $out);
        $reply .= $out;
        $out = '';
        $name = 'CPU核心数 ：';
        $reply .= $name;
        Base::runExec("grep 'cpu cores' /proc/cpuinfo |uniq", $out);
        $reply .= $out;
        $name = 'CPU使用情况：';
        $reply .= $name;
        Base::runExec("mpstat", $out);
        $reply .= $out;
        $name = '内存信息：';
        $reply .= $name;
        $out = '';
        Base::runExec('free -h', $out);
        $reply .= $out;
        $out = '';
        Base::runExec('free -m', $out);
        $reply .= $out;
        $out = '';
        Base::runExec('vmstat', $out);
        $reply .= $out;
        $out = '';
        Base::runExec('cat /proc/meminfo', $out);
        $reply .= $out;
        $out = '';
        $name = '磁盘信息：';
        $reply .= $name;
        $out = '';
        Base::runExec('df -h', $out);
        $reply .= $out;
        $out = '';
        $name = '当前进程：';
        $reply .= $name;
        $out = '';
        Base::runExec('ps -ef', $out);
        $reply .= $out;
        $out = '';
    }

    /**
     * 获取文本
     * @param string $file_path 文件路径
     */
    static public function getFileText($file_path)
    {
        $text = '';
        if (file_exists($file_path)) {
            $text = file_get_contents($file_path);
        }
        return Base::textToSafeText($text);
    }

    /**
     * 获取输入测试用例
     * @param string $file_path 文件路径
     */
    static public function getTestinFileData($file_path)
    {
        $testin = Base::getFileText($file_path);
        if (strlen($testin) > Base::$code_out_limit) {
            $testin = Base::utfsubstr($testin, 0, Base::$code_out_limit, true) . "\n" . '【仅显示前' . Base::$code_out_limit . '个字符】';
        }
        return $testin;
    }

    /**
     * 去除沙箱路径信息
     * @param string $mainfile 可执行文件路径
     * @param string $str 文本
     */
    static public function removeMsgSandboxPath($mainfile = '', $str = '')
    {
        try {
            $tp = Base::utfsubstr(Base::$sandbox_path, 1, strlen(Base::$sandbox_path)) . $mainfile;
            $str = str_replace([$tp, $mainfile], '', $str);
            Base::removeBr($str);
            if (strlen($str) > Base::$code_out_limit) {
                $str = Base::utfsubstr($str, 0, Base::$code_out_limit, true) . "\n" . '【仅显示前' . Base::$code_out_limit . '个字符】';
            }
            return $str;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 文本安全转换
     * @param string $text 文本内容
     */
    static public function textToSafeText(&$text = '')
    {
        try {
            $encoding = mb_detect_encoding($text);
            if (!$encoding) {
                $encoding = Base::$str_encoding;
            }
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
            return $text;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 获取用户代码状态，消耗的时间和内存
     * @param string $str
     * @return array $res
     */
    static public function getCodeTimeMemory(&$str)
    {
        $status = 0;
        $time_used = 0;
        $memory_used = 0;
        $msg = '';
        try {
            $res = json_decode($str, true);
            if (!$res) {
                throw new Exception('');
            }
            if (!isset($res['status']) || $res['status'] == null) {
                $res['status'] = 0;
            }
            if (!isset($res['time_used']) || $res['time_used'] == null) {
                $res['time_used'] = 0;
            }
            if (!isset($res['memory_used']) || $res['memory_used'] == null) {
                $res['memory_used'] = 0;
            }
            if (!isset($res['msg']) || $res['msg'] == null) {
                $res['msg'] = '';
            }
            $status = intval($res['status']);
            $time_used = intval($res['time_used']);
            $memory_used = intval($res['memory_used']);
            $msg = $res['msg'];
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
            // 触发错误的情况是判题机输出 Segmentation fault (core dumped) 导致解析json失败
            // 而判题机触发该错误是不断分配内存不回收触发安全机制导致程序崩溃
            // 由于具体分配内存大小不确定，所以按照 RE 处理
            return [
                'status' => Base::$judge_code_re,
                'time_used' => $time_used,
                'memory_used' => $memory_used,
                'msg' => Base::$code_run_re
            ];
        }
        return [
            'status' => $status,
            'time_used' => $time_used,
            'memory_used' => $memory_used,
            'msg' => $msg
        ];
    }

    /**
     * 递归复制文件
     * @param string $src 源路径
     * @param string $dst 目标路径
     */
    static public function copyDirectory($src, $dst)
    {
        if (!file_exists($src)) {
            return;
        }
        if (!is_dir($src)) {
            unlink($src);
            copy($src, $dst);
            return;
        }
        if (!is_dir($dst)) {
            mkdir($dst, 0777, true);
        }
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $src_file = $src . '/' . $file;
            $dst_file = $dst . '/' . $file;
            if (is_dir($src_file)) {
                Base::copyDirectory($src_file, $dst_file);
            } else {
                if (file_exists($dst_file)) {
                    unlink($dst_file);
                }
                copy($src_file, $dst_file);
            }
        }
        closedir($dir);
    }

    /**
     * 获取GPT KEY LIST
     * @return array key_list
     */
    static public function getChatGptKeyList()
    {
        try {
            $list_str = Base::getSettingKeyData('chatgpt_keys') ?? '';
            $list = preg_split("/[\s]+/", $list_str);
            return $list;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '获取GPT KEY LIST出错：' . $e->getMessage());
        }
        return [];
    }

    /**
     * 获取防盗链禁止访问文件类型数组
     * @return array list
     */
    static public function getFileCanNotVisitExtionList()
    {
        try {
            $list_str = Base::getSettingKeyData('file_can_not_visit_extion') ?? '';
            $list = preg_split("/[\s]+/", $list_str);
            return $list;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '获取防盗链禁止访问文件类型数组出错：' . $e->getMessage());
        }
        return [];
    }

    /**
     * 获取GPT接口地址
     */
    static public function getChatGptUrl()
    {
        try {
            $url = Base::getSettingKeyData('chatgpt_api_url') ?? '';
            return $url;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '获取GPT接口地址出错：' . $e->getMessage());
        }
        return '';
    }

    /**
     * 添加用户在线状态
     */
    static public function userOnline(&$user, $is_list = true, $isdel_key_email = false, $need_add_lastlogin = true)
    {
        $now = date('Y-m-d H:i:s', time());
        if ($is_list) {
            foreach ($user as &$tem) {
                $is_online = Gateway::isUidOnline($tem->id);
                if (
                    (isset($tem->id) && $is_online) ||
                    (isset($tem->name) &&  $tem->name == '机器人') ||
                    (isset($tem->email) &&  $tem->email == Base::getRobotEmail())
                ) {
                    // 机器人账号需要处理在线情况和上次在线时间
                    $tem->online = 1;
                    if ($need_add_lastlogin) {
                        $tem->lastlogin = $now;
                    }
                } else {
                    $tem->online = $is_online ? 1 : 0;
                }
                if (isset($tem->email) && $isdel_key_email) {
                    unset($tem->email);
                }
            }
        } else {
            $is_online = Gateway::isUidOnline($user->id);
            if (
                (isset($user->id) & $is_online) ||
                (isset($user->name) && $user->name == '机器人') ||
                (isset($user->email) && $user->email == Base::getRobotEmail())
            ) {
                $user->online = 1;
                if ($need_add_lastlogin) {
                    $user->lastlogin = $now;
                }
            } else {
                $user->online = $is_online ? 1 : 0;
            }
            if (isset($user->email) && $isdel_key_email) {
                unset($user->email);
            }
        }
    }

    /**
     * 去除图片的alt
     */
    static public function removeImgAlt($input)
    {
        try {
            // 替换Markdown格式中的图片描述为![]，并清空链接文字
            $pattern = '/!\[.*?\]\((.*?)\)/';
            $replacement = '![]($1)';
            $output = preg_replace($pattern, $replacement, $input);
            if (!$output) return $input;
            // 删除所有img标签中已有的alt属性（包括没有=的情况）
            $pattern = '/(<img\b[^>]*?)\s*alt\s*=\s*(".*?"|\'.*?\'|[^\'"\s>]*)?/i';
            $replacement = '$1';
            $output = preg_replace($pattern, $replacement, $output);
            // 为所有img标签添加空的alt属性
            // 如果没有alt属性，则在img标签中追加空的alt属性
            $pattern = '/(<img\b(?![^>]*\salt=)[^>]*?)(\/?>)/i';
            $replacement = '$1 alt="" $2';
            $output = preg_replace($pattern, $replacement, $output);
            return $output;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $input;
    }

    /**
     * 删除代码缓存
     * @param int $my_aid
     * @param int $code_id
     */
    static  public function deleteCodeCache($my_aid, $code_id)
    {
        if (!$my_aid || $code_id) {
            return;
        }
        $code_db = Base::getCodeData($code_id);
        if (
            !$code_db ||
            $code_db->userid != $my_aid ||
            !Base::codeStatusIsFinish($code_db->status)
        ) {
            return;
        }
        Base::deleteCodeJson($code_id);
    }

    /**
     * 判断代码状态是否已完成
     * @param string $status
     */
    static public function codeStatusIsFinish($status)
    {
        return $status && $status != Base::$code_up_waiting && $status != Base::$code_up_running;
    }

    /**
     * 新增文件插入数据返回URL
     */
    static public function writeNewStaticFile($my_aid, $data, $file_extion)
    {
        try {
            if (!$my_aid) {
                return '';
            }
            Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
            $file_path = Base::creatFilePath($file_extion);
            $id = Base::insertToDb(Base::getFileDataTableName($file_path), [
                'data' => $data
            ]);
            if (!$id) {
                return '';
            }
            Base::insertToDb(Base::getFilePathTableName($file_path), [
                'path' => $file_path,
                'file_id' => $id,
                'userid' => $my_aid,
                'time' => date('Y-m-d H:i:s', time())
            ]);
            return Base::$GLOBlinuxurl . $file_path;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 获取文件数据
     * @param string $file_path 路径
     * @return string|bool data
     */
    static public function getStaticFileData($file_path = '')
    {
        $redis30 = Redis::connection('db30');
        if ($redis30->exists($file_path)) {
            return $redis30->get($file_path);
        }
        $db = Db::table(Base::getFilePathTableName($file_path))
            ->where('path', $file_path)
            ->where('isdel', 0)
            ->select('file_id')
            ->first();
        if (!$db) {
            return false;
        }
        $db = Db::table(Base::getFileDataTableName($file_path))
            ->where('id', $db->file_id)
            ->select('data')
            ->first();
        if (!$db) {
            return false;
        }
        $redis30->setEx($file_path, Base::$redis_timeout, $db->data);
        return $db->data;
    }

    /**
     * 判断文件是否存在
     */
    static public function judgeFileExist($file_path)
    {
        $redis30 = Redis::connection('db30');
        if ($redis30->exists($file_path)) {
            return true;
        }
        $db = Db::table(Base::getFilePathTableName($file_path))
            ->where('path', $file_path)
            ->where('isdel', 0)
            ->select('file_id')
            ->first();
        if (!$db) {
            return false;
        }
        $db = Db::table(Base::getFileDataTableName($file_path))
            ->where('id', $db->file_id)
            ->select('data')
            ->first();
        if (!$db) {
            return false;
        }
        $redis30->setEx($file_path, Base::$redis_timeout, $db->data);
        return true;
    }

    /**
     * 创建文件数据表
     * 创建文件路径表
     */
    static public function creatFilePathDataTable($index = 0)
    {
        $table_file_data = $index .  Base::$db_file_data_same_name;
        $table_file_path = $index . Base::$db_file_path_same_name;
        $sql = [
            'CREATE TABLE `' . $table_file_data . '` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'文件ID\',
                    `data` longblob NOT NULL DEFAULT \'\' COMMENT \'文件数据\',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'CREATE TABLE `' . $table_file_path . '` (
                    `path` varchar(535) NOT NULL COMMENT \'文件路径\',
                    `isdel` bigint(20) NOT NULL DEFAULT 0 COMMENT \'是否删除\',
                    `userid` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'用户ID\',
                    `file_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'文件ID\',
                    `time` datetime NOT NULL DEFAULT current_timestamp() COMMENT \'上传时间\',
                    PRIMARY KEY (`path`),
                    INDEX `' . $index . '_isdel` (`isdel`),
                    INDEX `' . $index . '_userid` (`userid`),
                    INDEX `' . $index . '_file_id` (`file_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;'
        ];
        foreach ($sql as &$run_sql) {
            Db::statement($run_sql);
        }
        $now = date('Y-m-d H:i:s', time());
        $msg = '数据表【' . $table_file_data . '】和【' . $table_file_path . '】已创建完成！';
        Robot::sendChatToOneUserMsg(
            Base::getRootId(),
            '<h4>LTPP自动分表完成【' . $now
                . '】</h4><br><strong>信息</strong><br><pre style="white-space:pre-wrap;word-wrap:break-word;font-size: 1.06rem;">'
                . $msg . '</pre>'
        );
    }

    /**
     * 生成数据库文件路径
     * @param string $file_upload_extension 文件后缀
     * @return string res
     */
    static public function creatFilePath($file_upload_extension = '')
    {
        try {
            $file_path = Base::$LTPP_public_static_path . '/' . Base::encodeStr(Base::getFileTableIndex()[1]);
            $file_name = '';
            do {
                $num = rand(0, sizeof(Base::$id_char_set) - 1);
                $short_time = str_pad(time() % 100000000, 8, '0', STR_PAD_LEFT);
                $file_name = Base::Base64Encode($short_time, Base::$id_char_set[$num]) . '/' . md5(uniqid() . mt_rand(1, 100000) . time()) . ($file_upload_extension ? '.' . $file_upload_extension : '');
            } while (Base::judgeFileExist($file_path . '/' . $file_name));
            return $file_path . '/' . $file_name;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 根据文件类型获取Content-Type
     * @param string $file_extion 文件扩展名
     */
    static public function getContentType($file_extion = '')
    {
        if (isset(File::$file_extion_content_type_map[$file_extion])) {
            return File::$file_extion_content_type_map[$file_extion];
        }
        return 'application/octet-stream';
    }

    /**
     * 是否是不支持编辑的类型文件
     * @param string $file_extion 文件扩展名
     */
    static public function isNotSupportEditTypeFile($file_extion)
    {
        $has = isset(Base::$extion_map_number[$file_extion]);
        if ($has && Base::$extion_map_number[$file_extion] !== 4) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否启用前端强制缓存
     * @param string $file_extion 文件扩展名
     */
    static public function judgeIsOpenCacheControl($file_extion = '')
    {
        // 不可编辑类型文件前端缓存
        if (Base::isNotSupportEditTypeFile($file_extion)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否启用gzip
     * @param string $file_extion 文件扩展名
     */
    static public function judgeIsOpenGzip($file_extion = '')
    {
        if (
            !isset(File::$file_extion_content_type_map[$file_extion])
        ) {
            return false;
        }
        $type = File::$file_extion_content_type_map[$file_extion];
        // 文本类型启用gzip
        $list = [
            'text/',
            'application/'
        ];
        foreach ($list as &$tem) {
            if (stripos($type, $tem) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * DEBUG
     * @param * $trace trace
     */
    static public function debugTrace(&$trace = false)
    {
        $res = '';
        try {
            if ($trace === false) {
                return '';
            }
            return json_encode($trace, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), '<h4>LTPP运行出错</h4><br><strong>Trace信息</strong><br>' . json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT)) . '<br><strong>报错信息</strong><br>' . $e->getMessage());
        }
        return $res;
    }

    /**
     * 保存错误到数据库
     */
    static public function noticeSaveFile(&$str)
    {
        $new_path = Base::creatFilePath(Base::$notice_file_extension);
        $id = Base::insertToDb(Base::getFileDataTableName($new_path), [
            'data' => $str
        ]);
        if (!$id) {
            return Base::$default_trace_msg;
        }
        Base::insertToDb(Base::getFilePathTableName($new_path), [
            'path' => $new_path,
            'file_id' => $id,
            'userid' => Base::getRobotId(),
            'time' => date('Y-m-d H:i:s', time())
        ]);
        Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
        return '<a href="' . Base::$GLOBlinuxurl . $new_path . '" target="_blank">点击查看</a>';
    }

    /**
     * 发送错误通知
     */
    static public function sendErrorNotice($trace = false, $msg = '')
    {
        try {
            if ($trace === false) {
                return;
            }
            $trace_str = $trace;
            if (is_array($trace) || is_object($trace)) {
                $trace_str = Base::debugTrace($trace);
            }
            if (is_array($msg) || is_object($msg)) {
                $msg = Base::debugTrace($msg);
            }
            if (!$trace_str) {
                $trace_str = Base::$default_trace_msg;
            }
            if (!$msg) {
                $msg = Base::$default_error_msg;
            }
            $now = date('Y-m-d H:i:s', time());
            $same_start = '<h4>LTPP运行出错【' . $now
                . '】</h4><br><strong>报错信息</strong><br><pre style="white-space:pre-wrap;word-wrap:break-word;font-size: 1.06rem;">'
                . $msg .
                '</pre><br><strong>Trace信息</strong><br><pre style="white-space:pre-wrap;word-wrap:break-word;font-size: 1.06rem;">';
            $same_end = '</pre>';
            $notice_save_file_param = $same_start . $trace_str . $same_end;
            $msg = $same_start
                . ($trace_str == Base::$default_trace_msg ? $trace_str : Base::noticeSaveFile($notice_save_file_param)) . $same_end;
            Robot::sendChatToOneUserMsgAndEmail(
                Base::getRootId(),
                $msg,
            );
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsgAndEmail(Base::getRootId(), $e->getMessage());
        }
    }

    /**
     * 获取OJ测试用例
     */
    static public function getOjTestDataList($problem_id)
    {
        try {
            $redis31 = Redis::connection('db31');
            if ($redis31->exists($problem_id)) {
                return json_decode($redis31->get($problem_id), false);
            }
            $list = Db::table('oj_test_data')
                ->where('problem_id', $problem_id)
                ->where('isdel', 0)
                ->orderBy('id', 'asc')
                ->select('id', 'test_in', 'test_out')
                ->get();
            $redis31->setEx($problem_id, Base::$redis_timeout, json_encode($list));
            return $list;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return [];
    }

    /**
     * 更新缓存中OJ测试用例
     */
    static public function updateOjTestDataListRedis($problem_id)
    {
        try {
            $redis31 = Redis::connection('db31');
            $list = Db::table('oj_test_data')
                ->where('problem_id', $problem_id)
                ->where('isdel', 0)
                ->orderBy('id', 'asc')
                ->select('id', 'test_in', 'test_out')
                ->get();
            $redis31->setEx($problem_id, Base::$redis_timeout, json_encode($list));
            $md5_problem_id = Base::doubleMd5($problem_id);
            $alltestpath = Base::$testdata_path . $md5_problem_id . '/';
            $test_data_list = Base::getOjTestDataList($problem_id);
            // 删除解压的文件
            Base::deleteAllFile($alltestpath);
            // 重新写入
            Base::writeOjDataInToFile($problem_id, $alltestpath, $test_data_list);
            return $list;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return [];
    }

    /**
     * 将输入用例写入本地
     */
    static public function writeOjDataInToFile($problem_id = 0, $path = '', $test_data_list = [])
    {
        try {
            if (!$path) {
                $md5_problem_id = Base::doubleMd5($problem_id);
                $path = Base::$testdata_path . $md5_problem_id . '/';
            }
            $need_update = Base::checkTestDataNeedUpdata($problem_id, $path);
            if (Base::judgeCreatPath($path) && !$need_update) {
                return;
            }
            if (!$test_data_list) {
                $test_data_list = Base::getOjTestDataList($problem_id);
            }
            if ($need_update) {
                Base::deleteAllFile($path);
                Base::judgeCreatPath($path);
            }
            foreach ($test_data_list as &$tem) {
                $file_in_path = $path . $tem->id . '.in';
                Base::writeToFile($file_in_path, $tem->test_in);
                $file_in_path = $path . $tem->id . '.out';
                Base::writeToFile($file_in_path, $tem->test_out);
            }
            Base::updateTestDataTime($problem_id, $path);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 判断字符串是否是另一个字符串的后缀
     * @param string $suffix 搜索字串
     * @param string $str 目标串 
     */
    static public function judgeStrWithEndStr($suffix = '', $str = '')
    {
        return substr($str, -strlen($suffix)) === $suffix;
    }

    /**
     * 上传文件保存数据库
     */
    static public function uploadFileToDb($db_name, $my_aid, $file, $file_upload_extension = '')
    {
        try {
            if ($file && $file->isValid() && file_exists($file->getRealPath())) {
                if (!$file_upload_extension) {
                    $file_upload_extension = $file->getUploadExtension();
                }
                $data = file_get_contents($file->getRealPath());
                $new_path = Base::creatFilePath($file_upload_extension);
                $id = Base::insertToDb(Base::getFileDataTableName($new_path), [
                    'data' => $data
                ]);
                if (!$id) {
                    return '';
                }
                Base::insertToDb(Base::getFilePathTableName($new_path), [
                    'path' => $new_path,
                    'file_id' => $id,
                    'userid' => $my_aid,
                    'time' => date('Y-m-d H:i:s', time())
                ]);
                if (!Base::judgeStrWithEndStr('file_path', $db_name)) {
                    Base::insertToDb($db_name, [
                        'path' => $new_path,
                        'file_id' => $id,
                        'userid' => $my_aid,
                        'time' => date('Y-m-d H:i:s', time())
                    ]);
                }
                Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
                Base::deleteAllFile($file->getRealPath());
                return Base::$GLOBlinuxurl . $new_path;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 上传商品文件保存数据库
     */
    static public function uploadGoodsFileToDb($my_aid, $file, $file_upload_extension = '')
    {
        try {
            if ($file && $file->isValid() && file_exists($file->getRealPath())) {
                if (!$file_upload_extension) {
                    $file_upload_extension = $file->getUploadExtension();
                }
                $data = file_get_contents($file->getRealPath());
                $new_path = Base::creatFilePath($file_upload_extension);
                $id = Base::insertToDb(Base::getFileDataTableName($new_path), [
                    'data' => $data
                ]);
                if (!$id) {
                    return '';
                }
                Base::insertToDb(Base::getFilePathTableName($new_path), [
                    'path' => $new_path,
                    'file_id' => $id,
                    'userid' => $my_aid,
                    'time' => date('Y-m-d H:i:s', time())
                ]);
                Base::deleteAllFile($file->getRealPath());
                return $new_path;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 上传聊天文件保存数据库
     */
    static public function uploadChatFileToDb($post_user_id, $get_user_id, $file, $file_upload_extension = '')
    {
        try {
            if ($file && $file->isValid() && file_exists($file->getRealPath())) {
                if (!$file_upload_extension) {
                    $file_upload_extension = $file->getUploadExtension();
                }
                $file_name = $file->getUploadName();
                $file_size = $file->getSize();
                $data = file_get_contents($file->getRealPath());
                $new_path = Base::creatFilePath($file_upload_extension);
                $id = Base::insertToDb(Base::getFileDataTableName($new_path), [
                    'data' => $data
                ]);
                if (!$id) {
                    return '';
                }
                Base::insertToDb(Base::getFilePathTableName($new_path), [
                    'path' => $new_path,
                    'file_id' => $id,
                    'userid' => $post_user_id,
                    'time' => date('Y-m-d H:i:s', time())
                ]);
                Base::insertToDb('chat_file_path', [
                    'name' => $file_name,
                    'path' => $new_path,
                    'file_id' => $id,
                    'post_user_id' => $post_user_id,
                    'get_user_id' => $get_user_id,
                    'time' => date('Y-m-d H:i:s', time()),
                    'size' => $file_size
                ]);
                Base::$GLOBlinuxurl = Base::getSettingKeyData('GLOBlinuxurl');
                Base::deleteAllFile($file->getRealPath());
                return '[' . $file_name . '](' . Base::$GLOBlinuxurl . $new_path . ')';
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 加载聊天文件列表
     */
    static public function loadChatFileList($post_user_id, $get_user_id)
    {
        if (!$post_user_id || !$get_user_id) {
            return [];
        }
        $db = Db::table('chat_file_path')
            ->orWhere(function ($query) use ($post_user_id, $get_user_id) {
                $query
                    ->where('post_user_id', $post_user_id)
                    ->where('get_user_id', $get_user_id)
                    ->where('isdel', 0);
            })
            ->orWhere(function ($query) use ($post_user_id, $get_user_id) {
                $query
                    ->where('post_user_id', $get_user_id)
                    ->where('get_user_id', $post_user_id)
                    ->where('isdel', 0);
            })
            ->orderBy('id', 'desc')
            ->select('name', 'path', 'time', 'size')
            ->get();
        return $db;
    }

    /**
     * 获取数据库文件大小
     * @param string $path
     * @return int res
     */
    static public function getDbFileSize($path)
    {
        $res = 0;
        try {
            $data = Base::getStaticFileData($path);
            $res = strlen(Base::textToSafeText($data));
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $res;
    }

    /**
     * 获取数据库文件后缀
     */
    static public function getDbFileExtion($path)
    {
        $file_extion = '';
        try {
            $len = strlen($path);
            $begin_point = false;
            for ($i = 0; $i < $len; ++$i) {
                if ($path[$i] == '.') {
                    $file_extion = '';
                    $begin_point = true;
                    continue;
                }
                if ($begin_point) {
                    $file_extion .= $path[$i];
                }
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return $file_extion;
    }

    /**
     * 文件类型转数字
     */
    static public function fileExtionToNumberType($file_extion)
    {
        try {
            if (isset(Base::$extion_map_number[$file_extion])) {
                return Base::$extion_map_number[$file_extion];
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return 9;
    }

    /**
     * 上传云盘文件保存数据库
     */
    static public function uploadCloudFileToDb($my_aid, $file, $file_upload_extension = '')
    {
        try {
            if ($file && $file->isValid() && file_exists($file->getRealPath())) {
                if (!$file_upload_extension) {
                    $file_upload_extension = $file->getUploadExtension();
                }
                $file_name = $file->getUploadName();
                $file_size = $file->getSize();
                $data = file_get_contents($file->getRealPath());
                $new_path = Base::creatFilePath($file_upload_extension);
                $id = Base::insertToDb(Base::getFileDataTableName($new_path), [
                    'data' => $data
                ]);
                if (!$id) {
                    return '';
                }
                Base::insertToDb(Base::getFilePathTableName($new_path), [
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
                Base::deleteAllFile($file->getRealPath());
                return '[' . $file_name . '](' . Base::$GLOBlinuxurl . $new_path . ')';
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 更新云盘文件数据
     */
    static public function updateCloudFileData(&$size = 0, $userid = 0, $file_path = '', $data = '')
    {
        try {
            if (!$file_path || !$userid) {
                return json(['code' => -1, 'msg' => '文件不存在']);
            }
            $redis30 = Redis::connection('db30');
            $db = Db::table(Base::getFilePathTableName($file_path))
                ->where('userid', $userid)
                ->where('path', $file_path)
                ->where('isdel', 0)
                ->select('file_id')
                ->first();
            if (!$db) {
                return json(['code' => -1, 'msg' => '文件不存在']);
            }
            $size = strlen($data);
            if (!Base::judgeIsRoot($userid)) {
                $now_total_size = 0;
                $cloudfile_db_list = Db::table('cloud_file_path')
                    ->where('userid', $userid)
                    ->where('path', '!=', $file_path)
                    ->where('isdel', 0)
                    ->select('size')
                    ->get();
                foreach ($cloudfile_db_list as &$tem) {
                    $now_total_size += (int)$tem->size;
                }
                $usercloudfilememory = Base::getSettingKeyData('usercloudfilememory');
                if (!$usercloudfilememory) {
                    $usercloudfilememory = 50;
                }
                // 换算成字节
                $all = $usercloudfilememory * 1024 * 1024;
                if ($now_total_size > $all) {
                    return json(['code' => -1, 'msg' => '更新失败！您的剩余容量不足！']);
                }
            }
            Db::table('cloud_file_path')
                ->where('userid', $userid)
                ->where('path', $file_path)
                ->where('isdel', 0)
                ->update([
                    'size' => $size
                ]);
            Db::table(Base::getFileDataTableName($file_path))
                ->where('id', $db->file_id)
                ->update([
                    'data' => $data,
                ]);
            $redis30->setEx($file_path, Base::$redis_timeout, $data);
            Base::getChineseSize($size);
            return json(['code' => 1, 'msg' => '更新成功', 'data' => $size]);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return json(['code' => -1, 'msg' => '更新成功']);
    }

    /**
     * 删除云盘文件数据
     */
    static public function deleteCloudFileData($userid, $file_path = '')
    {
        try {
            if (!$file_path || !$userid) {
                return;
            }
            $redis30 = Redis::connection('db30');
            $redis30->del($file_path);
            $db = Db::table('cloud_file_path')
                ->where('userid', $userid)
                ->where('path', $file_path)
                ->where('isdel', 0)
                ->exists();
            if (!$db) {
                return;
            }
            Db::table('cloud_file_path')
                ->where('userid', $userid)
                ->where('path', $file_path)
                ->where('isdel', 0)
                ->update(['isdel' => 1]);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 获取数据库文件的文件名称
     */
    static public function getDbFileNameOfPath($path = '')
    {
        try {
            $len = strlen($path);
            $file_name = '';
            for ($i = 0; $i < $len; ++$i) {
                if ($path[$i] == '.') {
                    break;
                }
                $file_name .= $path[$i];
            }
            return $file_name;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
    }

    /**
     * 获取竞赛排名JSON
     */
    static public function getContestRankJsonData($contest_id = '')
    {
        try {
            if (!$contest_id) {
                return '';
            }
            $db = Db::table('contestrankcache')
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->select('json')
                ->first();
            if (!$db) {
                return '';
            }
            return json_decode($db->json, true);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 获取竞赛排名echarts
     */
    static public function getContestRankEchartsData($contest_id = '')
    {
        try {
            if (!$contest_id) {
                return '';
            }
            $db = Db::table('contestrankcache')
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->select('echarts')
                ->first();
            if (!$db) {
                return '';
            }
            return json_decode($db->echarts, true);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 获取竞赛排名HTML
     */
    static public function getContestRankHtml($contest_id = 0)
    {
        try {
            if (!$contest_id) {
                return '';
            }
            $db = Db::table('contestrankcache')
                ->where('contestid', $contest_id)
                ->where('isdel', 0)
                ->select('html')
                ->first();
            if (!$db) {
                return '';
            }
            return $db->html;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return '';
    }

    /**
     * 更新排名JSON结果，不存在就插入
     */
    static public function updateContestRankJson($contest_id = 0, &$json = '')
    {
        try {
            if (!$contest_id || !$json) {
                return false;
            }
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return false;
            }
            $json = json_encode($json);
            Db::table('contestrankcache')
                ->updateOrInsert(
                    [
                        'contestid' => $contest_id,
                        'isdel' => 0
                    ],
                    [
                        'json' => $json
                    ]
                );
            return true;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 更新排名HTML结果，不存在就插入
     */
    static public function updateContestRankHtml($contest_id = '', &$html = '')
    {
        try {
            if (!$contest_id || !$html) {
                return false;
            }
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return false;
            }
            Db::table('contestrankcache')
                ->updateOrInsert(
                    [
                        'contestid' => $contest_id,
                        'isdel' => 0
                    ],
                    [
                        'html' => $html
                    ]
                );
            return true;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 更新排名echarts结果，不存在就插入
     */
    static public function updateContestRankEcharts($contest_id = '', &$echarts = '')
    {
        try {
            if (!$contest_id || !$echarts) {
                return false;
            }
            $contest_db = Base::getContestData($contest_id);
            if (!$contest_db) {
                return false;
            }
            $echarts = json_encode($echarts);
            Db::table('contestrankcache')
                ->updateOrInsert(
                    [
                        'contestid' => $contest_id,
                        'isdel' => 0
                    ],
                    [
                        'echarts' => $echarts
                    ]
                );
            return true;
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 代码运行创建目录和代码
     * @param int $user_id
     * @param string $testin 
     */
    static public function creatCodeRunDirFile($user_id = 0, $testin = '', $is_problem = false)
    {
        // 子目录名称
        $mainfile = '';
        // 完整路径
        $filepath = '';
        // 可执行文件完整路径
        $runcodefilepath = '';
        // 输入文件完整路径
        $inpath = '';
        // 输出文件完整路径
        $outpath = '';
        // 错误文件完整路径
        $errpath = '';
        try {
            $md5aid = md5($user_id);
            //代码存放路径
            do {
                $mainfile = $md5aid . uniqid() . mt_rand(1, 100000) . time() . '/';
                $filepath = Base::$sandbox_path .  $mainfile;
            } while (file_exists($filepath));
            if (!file_exists($filepath)) {
                Base::judgeCreatPath($filepath, 0777);
            }
            // 可执行文件不能提前生成或写入
            // 如果提前生成或写入会导致编译器生成可执行文件失败
            $runcodefilepath = $filepath . 'main';
            if (!$is_problem) {
                //输入文件
                $inpath = $runcodefilepath . '.in';
                Base::writeToFile($inpath, $testin);
            }
            //输出文件
            $outpath = $runcodefilepath . '.out';
            Base::writeToFile($outpath, '');
            //错误文件
            $errpath = $runcodefilepath . '.err';
            Base::writeToFile($errpath, '');
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return [
            'mainfile' => $mainfile,
            'filepath' => $filepath,
            'runcodefilepath' => $runcodefilepath,
            'inpath' => $inpath,
            'outpath' => $outpath,
            'errpath' => $errpath,
        ];
    }

    /**
     * 判断是否是我的题单
     * @param int $question_sheet_id
     * @param int $my_aid
     */
    static public function judgeIsMyQuestionSheet($question_sheet_id, $my_aid)
    {
        try {
            $question_sheet_data = Base::getQuestionSheetData($question_sheet_id);
            if (!$question_sheet_id || !$question_sheet_data) {
                return false;
            }
            if ($question_sheet_data->creator_id == $my_aid) {
                return true;
            }
            if (Base::judgeIsRoot($my_aid)) {
                return true;
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), $e->getMessage());
        }
        return false;
    }

    /**
     * 判断OJ样例是否需要更新
     * @param int $oj_id
     * @param string $path
     */
    public static function checkTestDataNeedUpdata($oj_id, $path)
    {
        $redis37 = Redis::connection('db37');
        $key = 'testdatalasttime' . $oj_id;
        $now = Base::getFileText($path . Base::$testdata_time_file_name);
        if (!$now || !$redis37->exists($key)) {
            return true;
        }
        $last = $redis37->get($key);
        if ($last != $now) {
            return true;
        }
        return false;
    }

    /**
     * 更新OJ样例时间
     * @param int $oj_id
     * @param string $path
     */
    public static function updateTestDataTime($oj_id, $path)
    {
        $redis37 = Redis::connection('db37');
        $key = 'testdatalasttime' . $oj_id;
        $now = time();
        $redis37->set($key, $now);
        Base::judgeCreatPath($path);
        Base::writeToFile($path . Base::$testdata_time_file_name, $now);
    }
};
