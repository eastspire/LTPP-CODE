<?php

namespace app\controller;

use Exception;
use support\Db;
use support\Redis;
use Tinywan\Jwt\JwtToken;
use Webman\RedisQueue\Redis as RedisQueue;

class Base
{

    /**
     * 软件名称
     */
    static $app_name = 'LTPP在线开发平台';

    /**
     * 代码提交成功提示
     */
    static $code_up_success_msg = '代码提交成功';

    /**
     * 代码提交失败提示
     */
    static $code_up_fail_msg = '代码提交失败！请重新提交！';

    /**
     * 代码提交等待关键词
     */
    static $code_up_waiting = '等待中';

    /**
     * 代码提交运行关键词
     */
    static $code_up_running = '运行中';

    /**
     * AC提示
     */
    static $ac_msg = '恭喜您AC了';

    /**
     * AK提示
     */
    static $ak_msg = '恭喜您AK了';

    /**
     * ChatGPT 信息路径
     */
    static $chat_gpt_file_name = 'ltpp_chat_gpt.json';

    /**
     * GPT Key在Redis中Key的名称
     */
    static $redis_chatgpt_json_key = 'chatgpt_json_key';

    /**
     * GPT API地址在Redis中Key的名称
     */
    static $chat_gpt_api_url_key = 'chatgpt_api_url';

    /**
     * Redis密码
     */
    static $redis_password = 'SQS';

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
     * 判题机路径
     */
    static $judgepath = '/JudgeServer/judge';

    /**
     * RobotContest进行竞赛的最大时长(默认一天，竞赛时长超过一天不进行计算)
     */
    static $robot_contest_can_join_limit_contest_time = 86400;

    /**
     * 发送邮件消息队列名称
     */
    static $redis_queue_send_mail_name = 'send_mail';

    /**
     * 购买SSH消息队列名称
     */
    static $redis_queue_buy_ssh_name = 'buy_ssh';

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
    static $redis_queue_contest_rank = 'contest_rank';

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
     * 服务器地址
     */
    static $GLOBlinuxurl = 'http://127.0.0.1:8787';

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
     * RobotContest Redis前缀
     */
    static $robot_contest_redis_front = 'RobotContest';

    /**
     * 代码查重ID 前缀
     */
    static $contest_similarity_id_redis_front = 'ContestSimilarityId';

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
     * 判题机用户代码运行完成状态码
     */
    static $judge_code_finish = 1;

    /**
     * 判题机用户代码运行TLE状态码
     */
    static $judge_code_tle = 2;

    /**
     * 判题机用户代码运行MLE状态码
     */
    static $judge_code_mle = 3;

    /**
     * 判题机用户代码运行RE状态码
     */
    static $judge_code_re = 4;

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
        'mainanswerid' => true
    ];

    /**
     * 语言转markdown可识别的语言
     */

    static $map_language_to_markdown = [
        'C' => 'c',
        'C++' => 'cpp',
        'Java' => 'java',
        'Python3' => 'python',
        'Go' => 'go',
        'PHP' => 'php',
        'JavaScript' => 'javascript',
        'Rust' => 'rust',
        'TypeScript' => 'typescript',
        'C#' => 'csharp',
        'Ruby' => 'ruby'
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
     * 代码输出内容长度限制
     * @var int $code_out_limit 文章内容长度限制
     */
    static $code_out_limit = 10000;

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
     * @var array $char_set Base64字符集，勿动，需前端字符集保持一致
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
     * 机器人默认邮箱
     */
    static $robot_email = '2133103246@qq.com';

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
     * LTPP日志文件夹绝对路径
     * @var string $LTPP_logs_path LTPP日志文件夹绝对路径
     */
    static $LTPP_logs_path = '/home/LTPP/logs/';

    /**
     * LTPP公开文件夹绝对路径
     * @var string $LTPP_public_path LTPP公开文件夹绝对路径
     */
    static $LTPP_public_path = '/home/LTPP/public/';

    /**
     * LTPP static文件夹绝对路径
     * @var string $LTPP_public_path LTPP公开文件夹绝对路径
     */
    static $LTPP_public_static_path = '/home/LTPP/public/static';

    /**
     * 保存文件名长度限制
     * @var string $file_name_length_limit 保存文件名长度限制
     */
    static $file_name_length_limit = 20;

    /**
     * 临时目录
     */
    static $tmp_path = '/tmp/';


    /**
     * 项目名称
     */
    static $LTPP_name = 'LTPP在线开发平台';

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
            return 0;
        }
    }

    /**
     * 返回404页面
     */
    static public function notFoundPage()
    {
        try {
            $not_found = '';
            $redis23 = Redis::connection('db23');
            $key = '404_PAGE';
            if ($redis23->get($key)) {
                return $redis23->get($key);
            }
            $path = Base::$LTPP_public_path . '404.html';
            if (!$path) {
                $redis23->set($key, '');
                return '';
            }
            $not_found = file_get_contents($path);
        } catch (Exception $e) {
            $redis23->set($key, $not_found);
            return $not_found;
        }
        $redis23->set($key, $not_found);
        return $not_found;
    }

    /**
     * markdown字符串转html字符串
     * @param string $md
     * @return string $html
     */
    static public function markdownToHTML($md = '')
    {
        $md = addslashes($md);
        $md = preg_replace('/[`$<>]/', '\\\\$0', $md);
        $highlight_css = Base::getCss('highlight');
        $highlight_js = Base::getJs('highlight');
        $markdown_it_js = Base::getJs('markdown-it');
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"><title>' . Base::$LTPP_name . '</title><style>' . $highlight_css . '</style><script>' . $highlight_js . '</script><script>' . $markdown_it_js . '</script></head><body><div id="loading-main"><div class=\'loading-body\'><span><span></span><span></span><span></span><span></span></span><div class=\'loading-base\'><span></span><div class=\'loading-face\'></div></div></div><div class=\'loading-longfazers\'><span></span><span></span><span></span><span></span></div><h1 class="loading-h1">LOADING</h1></div><div id="LTPP"></div><script>const md=window.markdownit({html:true,xhtmlOut:true,linkify:true,typographer:true,html_blocks:{allowed:\'all\'},allowedTags:[\'script\',\'style\']});const code=`' . $md . '`;const result=md.render(code);document.getElementById("LTPP").innerHTML=result;</script></body></html>';
        return $html;
    }

    /**
     * code字符串转html字符串
     * @param string $code
     * @param string $language
     * @return string $html
     */
    static public function codeToHTML($code = '', $language = 'cpp')
    {
        if (!$language) {
            $language = 'cpp';
        }
        $code = "```$language\n" . $code . "\n```";
        return Base::markdownToHTML($code);
    }

    /**
     * 字符串转html字符串
     * @param string $str
     * @return string $html
     */
    static public function strToHTML($str = '')
    {
        $html = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"><title>' . Base::$LTPP_name . '</title></head><body>' . $str . '</body></html>';
        return $html;
    }

    /**
     * url假面
     * @param string $str
     * @return string $res
     */
    static public function url_encode($str)
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
                return response(Base::notFoundPage(), 404);
            }
            $data = Base::getArticleData($article_id);
            if (!$data || empty($data)) {
                return response(Base::notFoundPage(), 404);
            }
            if ($data->public != 1) {
                return response(Base::notFoundPage(), 404);
            }
            $name = $data->name ?? '';
            $article = $data->article ?? '';
            $writer = $data->writer ?? '';
            $fabulous = $data->fabulous ?? '';
            $collection = $data->collection ?? '';
            $releasetime = $data->releasetime ?? '';
            $lastchangetime = $data->lastchangetime ?? '';
            $image = $data->image ?? '';
            $url = Base::getSettingKeyData('GLOBfronturl') . '/onearticle?path=' . Base::url_encode($article_uid);
            $res = '# ' . $name . "\n" .
                '[原文链接](' . $url . ')' . "\n***\n" .
                '> 版权声明：本文为LTPP作者「' . $writer . '」的文章，著作权归作者所有，商业转载请联系作者获得授权，非商业转载请注明出处。' . "\n***\n" .
                '> 发布时间：' . $releasetime . "\n***\n" .
                ($lastchangetime != $releasetime ? '> 修改时间：' . $lastchangetime . "\n***\n" : '') .
                '> 点赞数：' . $fabulous . "\n***\n" .
                '> 收藏数：' . $collection . "\n***\n" .
                '<div style="display:flex;justify-content:center;"><img src="' . $image . '" alt="" style="margin:0px 0px 8px 0px;"></div>' . "\n\n" .
                $article;
            return response(Base::markdownToHTML($res), 200);
        } catch (Exception $e) {
            return response(Base::notFoundPage(), 404);
        }
    }

    /**
     * 发送请求
     * @param string $url 请求地址
     * @param array $header 请求头
     * @param array $body 请求体
     * @param bool $body_type_is_json 请求体是否是json
     * @return string $res 响应数据
     */
    static function sendRequest($url = '', $header = [], $body = [], $body_type_is_json = false)
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
            curl_setopt($ch, CURLOPT_PROXY, 'http://172.17.0.1:7890');
            $res = curl_exec($ch);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '请求异常信息：' . "\n" . $e->getMessage());
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
        /** @var \SplFileInfo[] $files */
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
            return;
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
            return '';
        }
        return $res;
    }

    /**
     * 插入数据到指定数据表
     * @param string $db_name 数据表名
     * @param array $data 数据
     * @return string $id 插入后的id
     */
    static public function insertToDb($db_name, $data)
    {
        if (!$db_name || !$data) {
            return 0;
        }
        try {
            $resid = Db::table($db_name)->insertGetId($data);
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '插入数据' . json_encode($data ?? []) . '到数据表' . $db_name . '出错:' . $e->getMessage());
            return 0;
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
    static public function judgeIsRoot($id)
    {
        return ((int) Base::getRootId()) === ((int) $id);
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
        $redis34 = Redis::connection('db34');
        $key = 'CodeJson' . $code_id;
        $redis34->setEx($key, Base::$redis_timeout, json_encode($json));
    }

    /**
     * 删除代码结果缓存
     */
    static public function deleteCodeJson($code_id = 0)
    {
        $redis33 = Redis::connection('db33');
        $redis34 = Redis::connection('db34');
        $key = 'CodeJson' . $code_id;
        $redis34->del($key);
        $key = 'CodeData' . $code_id;
        $redis33->del($key);
    }

    /**
     * 获取代码结果缓存
     */
    static public function getCodeJson($code_id = 0)
    {
        $redis34 = Redis::connection('db34');
        $key = 'CodeJson' . $code_id;
        $json = $redis34->get($key);
        if (!$json) {
            return [];
        }
        try {
            $json = json_decode($json, true);
        } catch (Exception $e) {
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
            if ($i >= 0)
                $nex += $a[$i];
            if ($j >= 0)
                $nex += $b[$j];
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
        exec('umount ' . $proc_path . ' > /dev/null 2>&1');
        exec('mount -t proc none ' . $proc_path . ' > /dev/null 2>&1');
        Robot::sendChatToOneUserMsg($root_aid, '/proc目录挂载完成');
        // 系统时间
        exec("cp -p -r --parents -f /etc/timezone $path;cp -p -r --parents -f /etc/localtime $path;");
        Robot::sendChatToOneUserMsg($root_aid, '系统时间工具安装完成');
        // ln安装
        exec("cp -p -r --parents -f /usr/bin/ln $path");
        Robot::sendChatToOneUserMsg($root_aid, '/usr/bin/ln安装完成');
        // 环境安装
        exec("cp -p -r --parents -f /usr/share/ $path");
        Robot::sendChatToOneUserMsg($root_aid, '/usr/share/安装完成');
        exec("cp -p --parents -f /bin/bash $path;cp -p --parents -f /bin/sh $path;cp -p -r --parents -f /usr/lib/mono $path;cp -p --parents -f /usr/bin/mono $path;cp -p --parents -f /usr/bin/gem $path;cp -p --parents -f /usr/bin/gem3.1 $path;cp -p --parents -f /usr/bin/ruby $path;cp -p --parents -f /usr/bin/node $path;cp -p --parents -f /usr/bin/node $path;cp -p --parents -f /usr/bin/php $path;cp -p --parents -f /usr/bin/python3 $path;cp -p -r --parents -f /root/.cargo $path;cp -p --parents -f /usr/bin/mcs $path;cp -p -r --parents -f /usr/local/nodejs $path;cp -p --parents -f /usr/bin/go $path;cp -p --parents -f /usr/bin/g++ $path;cp -p --parents -f /usr/bin/javac $path;cp -p --parents -f /lib64/ld-linux-x86-64.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/librt.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libdl.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libxml2.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libssl.so.3 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libcrypto.so.3 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpcre2-8.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libz.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libsodium.so.23 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libargon2.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libicuuc.so.72 $path;cp -p --parents -f /lib/x86_64-linux-gnu/liblzma.so.5 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libicudata.so.72 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libstdc++.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libdl.so.2 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libstdc++.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libpthread.so.0 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libruby-3.1.so.3.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgmp.so.10 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libcrypt.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libm.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libgcc_s.so.1 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libtinfo.so.6 $path;cp -p --parents -f /lib/x86_64-linux-gnu/libc.so.6 $path;");
        exec("cp -p -r --parents -f /usr/lib $path;cp -p -r --parents -f /usr/lib32 $path;cp -p -r --parents -f /usr/lib64 $path;cp -p -r --parents -f /etc/python3 $path;cp -p -r --parents -f /usr/lib/python3 $path;cp -p -r --parents -f /usr/lib/jvm/ $path;cp -p --parents -f /usr/lib/x86_64-linux-gnu/libexpat.so.1 $path;cp -p --parents -f /usr/bin/python3 $path;cp -p --parents -f /etc/alternatives/java $path;cp -p -r --parents -f /etc/ssl/certs/java $path;cp -p --parents -f /var/lib/dpkg/alternatives/java $path;cp -p --parents -f /etc/alternatives/javac $path;cp -p --parents -f /usr/bin/javac $path;cp -p --parents -f /var/lib/dpkg/alternatives/javac $path;cp -p --parents -f /etc/alternatives/php $path;cp -p --parents -f /etc/cron.d/php $path;cp -p -r --parents -f /etc/php $path;cp -p -r --parents -f /usr/lib/php $path;cp -p -r --parents -f /usr/include/php $path;cp -p --parents -f /var/lib/dpkg/alternatives/php $path;cp -p -r --parents -f /var/lib/php $path;cp -p --parents -f /usr/bin/mcs $path;cp -p -r --parents -f /usr/lib/x86_64-linux-gnu/ruby $path;cp -p -r --parents -f /usr/lib/ruby $path;cp -p --parents -f /usr/bin/ruby $path;");
        // java安装
        exec('chroot ' . $path . ' /bin/sh -c "ln -s /usr/lib/jvm/java-17-openjdk-amd64/bin/java /usr/bin/java" > /dev/null 2>&1');
        Robot::sendChatToOneUserMsg($root_aid, '编程运行环境安装完成');
        // 权限设置
        exec('chmod -R --no-preserve-root 777 ' . $path . ' > /dev/null 2>&1');
        Robot::sendChatToOneUserMsg($root_aid, '沙箱所有者设置完成');
        exec('chown -R --no-preserve-root ltpp:ltpp ' . $path . ' > /dev/null 2>&1');
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
            strripos($dir, '/contestcode/') === false
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
            Robot::sendChatToOneUserMsg(Base::getRootId(), 'deleteAllFile执行出错：' . $e->getMessage());
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
     */
    static public function utfsubstr(string $str = '', $index = 0, $getlen = 0, $is_has_br = false)
    {
        $len = strlen($str);
        $s = '';
        for ($i = $index; $i < $getlen && $i < $len; ++$i) {
            if (ord($str[$i]) < 192) {
                $s .= $str[$i];
            } else if (ord($str[$i]) < 224) {
                $s .= $str[$i];
                ++$i;
                if ($i >= $len) {
                    break;
                }
                $s .= $str[$i];
            } else {
                $s .= $str[$i];
                ++$i;
                if ($i >= $len) {
                    break;
                }
                $s .= $str[$i];
                ++$i;
                if ($i >= $len) {
                    break;
                }
                $s .= $str[$i];
            }
        }
        if (!$is_has_br) {
            // 去除所有换行符
            $s = str_replace(array("\r", "\n"), '', $s);
        }
        return $s;
    }

    /**
     * 获取css
     */
    static public function getCss($name)
    {
        $path = Base::$LTPP_public_path . 'css/' . $name . '.css';
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
        $path = Base::$LTPP_public_path . 'js/' . $name . '.js';
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
     * 文件(夹)名解码，目录结尾不含/
     * @param string $dir 文件（夹）目录
     * @param int [$root_file_len] 默认为0
     * @return void
     */
    static public function decodeFile($dir, $root_file_len = 0)
    {
        if (!file_exists($dir)) {
            return;
        }
        if (is_dir($dir)) {
            $diearr = scandir($dir);
            foreach ($diearr as &$tem) {
                if ($tem == '.' || $tem == '..') {
                    continue;
                } else {
                    Base::decodeFile($dir . '/' . $tem, $root_file_len);
                }
            }
            if (strlen($dir) <= $root_file_len) {
                // 防止根目录被重命名
                return;
            }
            // 文件夹重命名
            $len = strlen($dir);
            $slantingbar_loc = 0;
            $path = '';
            for ($i = $len - 1; $i >= 0; --$i) {
                if ($dir[$i] == '/') {
                    $slantingbar_loc = $i;
                    break;
                }
            }
            for ($i = 0; $i <= $slantingbar_loc; ++$i) {
                $path .= $dir[$i];
            }
            $first_name = "";
            for ($i = $slantingbar_loc + 1; $i < $len; ++$i) {
                $first_name .= $dir[$i];
            }
            rename($dir, $path . Base::Base64Decode($first_name));
        } else {
            // 文件重命名
            $len = strlen($dir);
            $point_loc = $len;
            $slantingbar_loc = 0;
            $path = '';

            for ($i = $len - 1; $i >= 0; --$i) {
                if ($dir[$i] == '/') {
                    $slantingbar_loc = $i;
                    break;
                }
            }
            for ($i = $len - 1; $i >= 0; --$i) {
                if ($dir[$i] == '.') {
                    $point_loc = $i;
                    break;
                }
            }
            for ($i = 0; $i <= $slantingbar_loc; ++$i) {
                $path .= $dir[$i];
            }
            $first_name = "";
            $last_name = "";
            for ($i = $slantingbar_loc + 1; $i < $point_loc; ++$i) {
                $first_name .= $dir[$i];
            }
            for ($i = $point_loc; $i < $len; ++$i) {
                $last_name .= $dir[$i];
            }
            rename($dir, $path . Base::Base64Decode($first_name) . $last_name);
        }

        return;
    }

    /**
     * 字符串类似Base64方式编码
     * @param string $str 待编码字符串
     * @return string $res 编码后的字符串
     */
    static public function Base64Encode($str, $use_char_set = null)
    {
        if (!$use_char_set) {
            $use_char_set = Base::$char_set;
        }
        if (empty($str) || !isset($str) || strlen($str) < 1) {
            return "";
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
    }

    /**
     * 字符串类似Base64方式解码
     * @param string $str 待解码字符串
     * @return string $res 解码后的字符串
     */
    static public function Base64Decode($str, $use_char_set = null)
    {
        try {
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
        } catch (Exception $e) {
            return '';
        }
        return $base64_decode;
    }

    /**
     * 获取GLOBlinuxurl
     * @return string $url linux url地址
     */
    static public function getGLOBlinuxurl()
    {
        $redis5 = Redis::connection('db5');
        if ($redis5->get('GLOBlinuxurl')) {
            Base::$GLOBlinuxurl = $redis5->get('GLOBlinuxurl');
        } else {
            $setting_db = Db::table('setting')
                ->where('isdel', 0)
                ->select('GLOBlinuxurl')
                ->orderBy('id', 'desc')
                ->first();
            if (!$setting_db) {
                return null;
            }
            Base::$GLOBlinuxurl = $setting_db->GLOBlinuxurl;
            $redis5->set('GLOBlinuxurl', Base::$GLOBlinuxurl);
        }
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
            ->select('id')
            ->first();
        if (!$user_db) {
            Base::creatRobot();
            $user_db = Db::table('user')
                ->where('name', '机器人')
                ->where('isdel', 0)
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
        $robot_db = Base::getUserData(Base::getRobotId());
        return $robot_db->email;
    }

    /**
     * 机器人不存在，创建机器人账号
     */
    static public function creatRobot()
    {
        $redis5 = Redis::connection('db5');
        if ($redis5->get('robotid')) {
            return;
        }

        $robot_db = Db::table('user')
            ->where('name', Base::$robot_name)
            ->where('isdel', 0)
            ->select('id')
            ->first();
        if ($robot_db) {
            return;
        }
        // 机器人账号不存在，立即发送root邮件通知
        $root_id = Base::getRootId();
        $root_db = Base::getUserData($root_id);
        if (!$root_db) {
            return;
        }

        $data = [
            'name' => '机器人',
            'password' => Base::passwordEncryption(rand(1, 100000) . time()),
            'sex' => '男',
            'registertime' => date('Y-m-d H:i:s', time()),
            'headimage' => 'https://q1.qlogo.cn/headimg_dl?dst_uin=' . Base::$robot_email . '&spec=640',
            'fans' => 0,
            'follow' => 0,
            'online' => 0,
            'grade' => 1,
            'email' => Base::$robot_email
        ];
        $res_id = Base::insertToDb('user', $data);
        $content = '系统机器人的账号不存在，系统已自动重新生成！机器人账号用户名：' . Base::$robot_name;
        $redis5->set('robotid', $res_id);
        $offline = (int) Base::getSettingKeyData('offline');
        if ($offline == 0) {
            Robot::sendChatToOneUserMsgAndEmail($root_id, $content);
        }
    }

    /**
     * 通过字符串authorization获取uid信息
     */
    static public function getUidByToken($authorization)
    {
        try {
            $data = JwtToken::verify(1, $authorization);
        } catch (Exception $e) {
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
        if (!is_string($uid)) {
            return 0;
        }
        if (strlen($uid) == 0) {
            return 0;
        }
        try {
            $loc = ord($uid[0]) - ord('0');
            $base_str = substr($uid, 1);
            $id = Base::Base64Decode($base_str, Base::$id_char_set[$loc]);
        } catch (Exception $e) {
            return 0;
        }
        return $id;
    }

    /**
     * 普通ID转UID
     */
    static public function getUidById($id)
    {
        if ($id <= 0 || !is_numeric($id)) {
            return '';
        }
        $num = strval(rand(1000000, 9999999) % sizeof(Base::$id_char_set));
        $base_str = Base::Base64Encode(strval($id), Base::$id_char_set[$num]);
        $uid = $num . $base_str;
        return $uid;
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
     * 生成临时ID
     */
    static public function getSafeUniqidByIdOnce($id)
    {
        return md5($id . '|' . uniqid() . '|' . mt_rand(1, 100000) . '|' . time());
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
     * 生成随机唯一竞赛查重ID
     */
    static public function getContestSimilarityId($contest_id)
    {
        if ($contest_id <= 0 || !is_numeric($contest_id)) {
            return '';
        }
        $redis31 = Redis::connection('db31');
        $key = Base::$contest_similarity_id_redis_front . $contest_id;
        $value = Base::getSafeUniqidByIdOnce($key);
        $redis31->setNx($key, $value);
        return $value;
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
     * @param string $file 文件路径
     * @param string $content 写入的内容
     */
    static public function writeToFile($file, $content = '')
    {
        Base::judgeCreatPath($file);
        while (1) {
            try {
                $result = file_put_contents($file, $content);
                if ($result !== false) {
                    // 写入成功
                    return;
                }
            } catch (Exception $e) {
                continue;
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
            $redis21->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
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
            $redis25->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
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
            $redis26->setEx($key, Base::$redis_timeout, json_encode($db));
            return $db;
        } catch (Exception $e) {
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
            return [];
        }
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
        $redis33->del($key);
        $db = Db::table('codehistory')
            ->where('id', $code_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            return;
        }
        $redis33->setEx($key, Base::$redis_timeout, json_encode($db));
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
        $redis8->del($key);
        $db = Db::table('user')
            ->where('id', $user_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
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
        $redis20->del($key);
        $db = Db::table('group')
            ->where('id', $group_id)
            ->where('isdel', 0)
            ->first();
        if (!$db) {
            return;
        }
        $redis20->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 根据ID更新竞赛缓存信息
     * @param int $contest_id
     */
    static public function updateContestData($contest_id)
    {
        if (!is_numeric($contest_id)) {
            return;
        }
        $redis21 = Redis::connection('db21');
        $key = 'ContestData' . $contest_id;
        $redis21->del($key);
        $db = Db::table('contest')
            ->where('id', $contest_id)
            ->where('isdel', 0)
            ->select(Contest::$contest_db_key)
            ->first();
        $redis21->setEx($key, Base::$redis_timeout, json_encode($db));
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
        $redis25->del($key);
        $db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->select(Article::$article_db_key)
            ->first();
        if (!$db) {
            return;
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
        $redis26->del($key);
        $db = Db::table('oj')
            ->where('id', $oj_id)
            ->where('isdel', 0)
            ->select(Oj::$oj_db_key)
            ->first();
        if (!$db) {
            return;
        }
        $redis26->setEx($key, Base::$redis_timeout, json_encode($db));
    }

    /**
     * 更新全部用户用户缓存信息
     */
    static public function updateAllUserDataRedis()
    {
        $redis8 = Redis::connection('db8');
        $redis8->flushdb();
        $user_db = Db::table('user')
            ->select('id')
            ->get();
        foreach ($user_db as &$tem) {
            Base::updateUserDataRedis($tem->id);
        }
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
        Robot::sendChatToOneUserMsg($my_aid, '恭喜您使用' . $userlanguage . '编程语言AC【' . $problem_name . '】，奖励您' . Base::$ac_money . '个学虫币！（北京时间：' . date('Y-m-d H:i:s', time()) . '）');
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
        $content = '恭喜您在LTPP平台的【' . $contestdb->name . '】中AK，给予' . Base::$ak_money . '学虫币奖励（北京时间：' . date('Y-m-d H:i:s', time()) . '）';
        Robot::sendChatToOneUserMsgAndEmail($my_aid, $content);
        RedisQueue::send(Base::$redis_queue_send_mail_name, [
            'to' => $my->email,
            'title' => $title,
            'content' => $content
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
                'msg' => '请编写后再次提交哦！',
                'code_id' => 0
            ];
        }
        switch ($userlanguage) {
            case 'C':
                break;
            case 'C++':
                break;
            case 'Java':
                break;
            case 'Python3':
                break;
            case 'Go':
                break;
            case 'PHP':
                break;
            case 'JavaScript':
                break;
            case 'Rust':
                break;
            case 'TypeScript':
                break;
            case 'C#':
                break;
            case 'Ruby':
                break;
            default:
                return [
                    'code' => -1,
                    'msg' => '请选择语言后提交！',
                    'code_id' => 0
                ];
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
            exec('cp -f /home/LTPP/InstallMust/JudgeServer/judge ' . Base::$judge_install_path . ' 2>&1', $out);
            Base::chmodFile('/JudgeServer', 0555);
            if (!empty($out)) {
                foreach ($out as $tem) {
                    $res .= $tem . "\n";
                }
                Robot::sendChatToOneUserMsg(Base::getRootId(), '判题机安装出错：' . $res);
                return false;
            }
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '判题机安装出错：' . $e->getMessage());
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
            return;
        }
    }

    /**
     * 编译器
     * @param string $userlanguage
     * @param string $code
     * @param string $filepath
     * @param string $runcodefilepath
     */
    static public function compiler($userlanguage, $code, $filepath, $runcodefilepath)
    {
        $out = [];
        try {
            //编译
            switch ($userlanguage) {
                case 'Java':
                    $runcodefilepath = $filepath . 'Main';
                    Base::writeToFile($runcodefilepath . '.java', $code);
                    exec('/usr/bin/javac -J-Dfile.encoding=UTF-8 ' . $runcodefilepath . '.java 2>&1', $out);
                    break;
                case 'C++':
                    Base::writeToFile($runcodefilepath . '.cpp', $code);
                    exec('/usr/bin/g++ -o ' . $runcodefilepath . ' ' . $runcodefilepath . '.cpp -std=c++2a 2>&1', $out);
                    break;
                case 'C':
                    Base::writeToFile($runcodefilepath . '.c', $code);
                    exec('/usr/bin/g++ -o ' . $runcodefilepath . ' ' . $runcodefilepath . '.c -std=c++2a 2>&1', $out);
                    break;
                case 'Go':
                    exec('/usr/bin/go env -w GO111MODULE=auto');
                    Base::writeToFile($runcodefilepath . '.go', $code);
                    exec('/usr/bin/go build -o ' . $filepath . ' ' . $runcodefilepath . '.go 2>&1', $out);
                    break;
                case 'Rust':
                    Base::writeToFile($runcodefilepath . '.rs', $code);
                    exec('/root/.cargo/bin/rustc -O -o ' . $runcodefilepath . ' ' . $runcodefilepath . '.rs 2>&1', $out);
                    break;
                case 'C#':
                    Base::writeToFile($runcodefilepath . '.cs', $code);
                    exec('/usr/bin/mcs -out:' . $runcodefilepath . ' ' . $runcodefilepath . '.cs 2>&1', $out);
                    break;
                case 'TypeScript':
                    Base::writeToFile($runcodefilepath . '.ts', $code);
                    exec('/usr/local/nodejs/bin/tsc -t es2022 --outFile ' . $runcodefilepath . '.js ' . $runcodefilepath . '.ts 2>&1', $out);
                    break;
                case 'Python3':
                    Base::writeToFile($runcodefilepath . '.py', $code);
                    break;
                case 'PHP':
                    Base::writeToFile($runcodefilepath . '.php', $code);
                    break;
                case 'JavaScript':
                    Base::writeToFile($runcodefilepath . '.js', $code);
                    break;
                case 'Ruby':
                    Base::writeToFile($runcodefilepath . '.rb', $code);
                    break;
                default:
                    return ['code' => -1, 'result' => '请选择语言后提交！', 'usememory' => 0, 'usetime' => 0];
            }
        } catch (Exception $e) {
            return ['code' => -1, 'result' => '判题机异常！' . $e->getMessage(), 'usememory' => 0, 'usetime' => 0];
        }
        return ['code' => 1, 'result' => $out, 'usememory' => 0, 'usetime' => 0];
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
        try {
            switch ($userlanguage) {
                case 'Java':
                    exec(Base::$judgepath . ' /usr/bin/java@-cp@' . $filepath . '@Main ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'Python3':
                    exec(Base::$judgepath . ' /usr/bin/python3@' . $runcodefilepath . '.py ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'C++':
                    exec(Base::$judgepath . ' ' . $runcodefilepath . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'C':
                    exec(Base::$judgepath . ' ' . $runcodefilepath . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'Go':
                    exec(Base::$judgepath . ' ' . $runcodefilepath . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'PHP':
                    exec(Base::$judgepath . ' /usr/bin/php@' . $runcodefilepath . '.php ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'JavaScript':
                    exec(Base::$judgepath . ' /usr/bin/node@' . $runcodefilepath . '.js ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'TypeScript':
                    exec(Base::$judgepath . ' /usr/bin/node@' . $runcodefilepath . '.js ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'Ruby':
                    exec(Base::$judgepath . ' /usr/bin/ruby@' . $runcodefilepath . '.rb ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                    break;
                case 'C#':
                    exec(Base::$judgepath . ' /usr/bin/mono@' . $runcodefilepath . ' ' . $limittime * 2 . ' ' . $limitmemory * 2 . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                case 'Rust':
                    exec(Base::$judgepath . ' ' . $runcodefilepath . ' ' . $limittime . ' ' . $limitmemory . ' ' . $inpath . ' ' . $outpath . ' ' . $errpath . ' 2>&1', $out);
                default:
                    break;
            }
        } catch (Exception $e) {
            return $out;
        }
        return $out;
    }

    /**
     * 获取文本
     */
    static public function getFileText($file_path)
    {
        $text = '';
        if (file_exists($file_path)) {
            $text = file_get_contents($file_path);
            try {
                $encoding = mb_detect_encoding($text);
                if (!$encoding) {
                    $encoding = Base::$str_encoding;
                }
                $text = mb_convert_encoding($text, 'UTF-8', $encoding);
            } catch (Exception $e) {
                return '';
            }
        }
        return $text;
    }

    /**
     * 获取用户代码状态，消耗的时间和内存
     * @param string $str
     * @return array $res
     */
    static public function getCodeTimeMemory($str)
    {
        $status = 0;
        $time_used = 0;
        $memory_used = 0;
        $msg = '';
        try {
            $res = json_decode($str, true);
            $status = (int) $res['status'] ?? 0;
            $time_used = (int) $res['time_used'] ?? 0;
            $memory_used = (int) $res['memory_used'] ?? 0;
            $msg = $res['msg'] ?? '';
        } catch (Exception $e) {
            // 触发错误的情况是判题机输出 Segmentation fault (core dumped) 导致解析json失败 而判题机触发该错误是不断分配内存不回收触发安全机制导致程序崩溃
            // 由于具体分配内存大小不确定，所以按照 RE 处理
            return ['status' => 4, 'time_used' => $time_used, 'memory_used' => $memory_used, 'msg' => $msg];
        }
        return ['status' => $status, 'time_used' => $time_used, 'memory_used' => $memory_used, 'msg' => $msg];
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
     * 获取GPT JSON对应配置
     * @param string $key 读取key名称
     * @return string|array $api_url|$key_list
     */
    static public function getChatGptJSON($key)
    {
        try {
            if ($key != Base::$chat_gpt_api_url_key && $key != Base::$redis_chatgpt_json_key) {
                return '';
            }
            $redis35 = Redis::connection('db35');
            $cache_list = $redis35->get(Base::$redis_chatgpt_json_key);
            if ($cache_list) {
                return json_decode($cache_list, true)[$key];
            }
            // 存放路径
            $path = Base::$LTPP_path . Base::$chat_gpt_file_name;
            if (!file_exists($path)) {
                Base::writeToFile($path, '{"' . Base::$chat_gpt_api_url_key . '":"","' . Base::$redis_chatgpt_json_key . '":[]}');
                return [
                    Base::$chat_gpt_api_url_key => '',
                    Base::$redis_chatgpt_json_key => [],
                ][$key];
            }
            // 读取Key数组
            $json_str = file_get_contents($path);
            $redis35->set(Base::$redis_chatgpt_json_key, $json_str);
            $json = json_decode($json_str, true);
            return $json[$key];
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '获取GPT JSON对应配置出错：' . $e->getMessage());
        }
        return [
            Base::$chat_gpt_api_url_key => '',
            Base::$redis_chatgpt_json_key => [],
        ][$key];
    }

    /**
     * 获取GPT KEY LIST
     * @return array key_list
     */
    static public function getChatGptKeyList()
    {
        try {
            $key_list = Base::getChatGptJSON(Base::$redis_chatgpt_json_key);
            if (!$key_list) {
                return [];
            }
            return $key_list;
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '获取GPT KEY LIST出错：' . $e->getMessage());
        }
        return [];
    }

    /**
     * 获取GPT接口地址
     */
    static public function getChatGptUrl()
    {
        try {
            $api_url = Base::getChatGptJSON(Base::$chat_gpt_api_url_key);
            if (!$api_url) {
                return '';
            }
            return $api_url;
        } catch (Exception $e) {
            Robot::sendChatToOneUserMsg(Base::getRootId(), '获取GPT接口地址出错：' . $e->getMessage());
        }
        return '';
    }
}
;