<?php

namespace process;

use Workerman\Crontab\Crontab;
use app\controller\Base;
use Exception;
use support\Db;

class DouYinCrontab
{
    /**
     * 重命名
     */
    private function rename($preview_title = '')
    {
        try {
            $preview_title = strval($preview_title);
            // 去除双引号
            $preview_title = str_replace('"', '-', $preview_title);
            // 转义单引号
            $preview_title = str_replace("'", "''", $preview_title);
            // 转义\
            $preview_title = str_replace("\\", '-', $preview_title);
            // 转义/
            $preview_title = str_replace("/", '-', $preview_title);
            // 去除特殊字符
            $preview_title = preg_replace("/[^\w\x{4e00}-\x{9fa5}]/u", '', $preview_title);
            // 符号替换
            $preview_title = preg_replace("/[:+\s]/", '-', $preview_title);
            // 长度限制
            $preview_title = Base::utfsubstr($preview_title, 0, Base::$video_name_limit);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
        }
        return $preview_title;
    }

    /**
     * 重命名视频
     * @param string $aweme_id
     * @param string $preview_title
     * @return string
     */
    private function renameVideoName($aweme_id = '', $preview_title = '')
    {
        try {
            $preview_title = $this->rename($preview_title);
            if (empty($preview_title)) {
                $preview_title = preg_replace("/[:+\s]/", '-', strval($aweme_id));
            }
            if (empty($preview_title)) {
                $preview_title = md5(time());
            }
            $preview_title = Base::utfsubstr($preview_title, 0, Base::$video_name_limit);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
        }
        return $preview_title;
    }

    /**
     * 保存视频到本地
     * @return array $path[本地路径，url路径]
     */
    private function getSaveFilePath()
    {
        $path = Base::creatFilePath('mp4');
        Base::$GLOBlinuxurl = Base::getGLOBlinuxurl();
        return [$path, Base::$GLOBlinuxurl . $path];
    }

    /**
     * 删除抖音过期视频
     */
    private function deleteDouYinTimeoutVideo()
    {
        try {
            $noupdate_limit_seconds = Base::getSettingKeyData('douyin_noupdate_limit_seconds');
            // 删除过期的抖音视频
            Db::table('video')
                ->where('isdouyin', 1)
                ->where('isdel', 0)
                ->where('time', '<', date('Y-m-d H:i:s', time() - $noupdate_limit_seconds))
                ->update(['isdel' => 1]);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
        }
    }

    /**
     * 获取收藏列表
     * @param {*} count 
     * @param {*} cursor
     * @return {*} res
     */
    private function getListcollection($count, $cursor)
    {
        $res = [];
        try {
            $url = Base::getSettingKeyData('douyin_listcollection_url');
            $cookie = Base::getSettingKeyData('douyin_cookie');
            $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 Edg/119.0.0.0';
            $headers = [
                'Content-Type:application/x-www-form-urlencoded',
                'Cookie:' . $cookie,
                'Referer:https://www.douyin.com/',
                'User-Agent' . $user_agent
            ];
            $data = [
                'count' => $count,
                'cursor' => $cursor
            ];
            $res = json_decode(Base::postRequest($url, $headers, $data), true);
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
        }
        return $res;
    }

    /**
     * 运行
     */
    private function run()
    {
        try {
            $cnt = 0;
            $count = 10;
            $cursor = 0;
            while (true) {
                $this->deleteDouYinTimeoutVideo();
                // 每轮请求重新读取，root更新配置尽可能早生效
                $is_save_file = Base::getSettingKeyData('douyin_save_file');
                $save_limit = Base::getSettingKeyData('douyin_save_limit');
                $res = $this->getListcollection($count, $cursor);
                if (
                    !$res ||
                    !isset($res['aweme_list']) ||
                    !$res['aweme_list'] ||
                    !sizeof($res['aweme_list']) ||
                    !isset($res['cursor'])
                ) {
                    return;
                }
                $cursor = $res['cursor'];
                $aweme_list = $res['aweme_list'];
                foreach ($aweme_list as &$tem) {
                    if (
                        !isset($tem['video']['bit_rate'][0]['play_addr']['url_list'][0]) ||
                        !isset($tem['aweme_id']) ||
                        !isset($tem['desc']) ||
                        !isset($tem['video_tag']) ||
                        !isset($tem['statistics']['collect_count']) ||
                        !isset($tem['statistics']['digg_count'])
                    ) {
                        continue;
                    }
                    $video_url = $tem['video']['bit_rate'][0]['play_addr']['url_list'][0];
                    $video_name = $this->renameVideoName($tem['aweme_id'], $tem['desc']);
                    $video_tag_list = $tem['video_tag'];
                    $tag_len = sizeof($video_tag_list);
                    $tag = '';
                    foreach ($video_tag_list as $key => &$tem_tag) {
                        if (!isset($tem_tag['tag_name'])) {
                            continue;
                        }
                        $tag .= $tem_tag['tag_name'];
                        if ($key < $tag_len - 1) {
                            $tag .= ' ';
                        }
                    }
                    $tag = $this->rename($tag);
                    if (!$tag) {
                        $tag = $video_name;
                    }
                    $collect_count = (int)$tem['statistics']['collect_count'];
                    $digg_count = (int)$tem['statistics']['digg_count'];
                    if (!$collect_count) {
                        $collect_count = 0;
                    }
                    if (!$digg_count) {
                        $digg_count = 0;
                    }
                    $has = Db::table('video')
                        ->where('name', $video_name)
                        ->where('isdouyin', 1)
                        ->where('isdel', 0)
                        ->select('id')
                        ->first();
                    if ($has) {
                        Db::table('video')
                            ->where('id', $has->id)
                            ->update([
                                'tag' => $tag,
                                'url' => $video_url,
                                'fabulous' => $digg_count,
                                'love' => $collect_count,
                                'time' => date('Y-m-d H:i:s', time())
                            ]);
                    } else {
                        // 无论是否保存文件，先插入抖音的视频地址
                        Base::insertToDb('video', [
                            'isdouyin' => 1,
                            'name' => $video_name,
                            'tag' => $tag,
                            'url' => $video_url,
                            'fabulous' => $digg_count,
                            'love' => $collect_count
                        ]);
                        if ($is_save_file) {
                            $has = Db::table('video')
                                ->where('name', $video_name)
                                ->where('isdouyin', 0)
                                ->where('isdel', 0)
                                ->select('id')
                                ->exists();
                            if (!$has) {
                                $path_arr = $this->getSaveFilePath();
                                $local_path = $path_arr[0];
                                // 保存视频到本地
                                $save_res = Base::saveNetworkFileToDb(Base::getRobotId(), $video_url, $local_path);
                                if ($save_res) {
                                    // 替换视频地址为本地地址
                                    $video_url = $path_arr[1];
                                    // 已经保存本地，所以不是抖音
                                    Base::insertToDb('video', [
                                        'isdouyin' => 0,
                                        'name' => $video_name,
                                        'tag' => $tag,
                                        'url' => $video_url,
                                        'fabulous' => $digg_count,
                                        'love' => $collect_count
                                    ]);
                                }
                            }
                        }
                    }
                    ++$cnt;
                    if ($cnt > $save_limit) {
                        return;
                    }
                }
            }
        } catch (Exception $e) {
            Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
        }
    }

    public function onWorkerStart()
    {
        // 每6秒钟执行一次
        new Crontab('*/6 * * * * *', function () {
            try {
                $this->run();
            } catch (Exception $e) {
                Base::sendErrorNotice($e->getTraceAsString(), '定时任务进程<strong>【DouYinCrontab】</strong>运行出错：' . $e->getMessage());
            }
        });
    }
}
