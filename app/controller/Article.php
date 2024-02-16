<?php

namespace app\controller;

use Exception;
use support\Db;
use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;

class Article extends Image
{
    /**
     * 文章数据库选择的字段
     * @var array $article_db_key 文章数据库
     */
    static $article_db_key = [
        'id',
        'problemid',
        'name',
        'fabulous',
        'collection',
        'releasetime',
        'lastchangetime',
        'article',
        'image',
        'public',
        'writerid'
    ];

    /**
     * 判断是否是自己的文章
     * @param int $writer_id 用户id
     * @param int $article_id 文章id
     * @return bool
     */
    protected function judgeArticleWritenByMe($writer_id, $article_id)
    {
        $db = Db::table('article')
            ->where('id', $article_id)
            ->where('writerid', $writer_id)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否是自己的文章或者是否是管理员
     * @param Request $request 请求
     * @return string $res json
     */
    public function isCanSeeThisArticle(Request $request)
    {
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $db = Db::table('article')
            ->where('id', $article_id)
            ->where('writerid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => 1]);
        } else {
            $db = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->select('grade')
                ->first();
            if ($db) {
                if ($db->grade == 2 || $db->grade == 3) {
                    return json(['code' => 1]);
                } else {
                    return json(['code' => 0]);
                }
            }
        }
        return json(['code' => 0]);
    }

    /**
     * 加载一篇文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function loadOneArticle(Request $request)
    {
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $info = Base::getArticleData($article_id);
        if (!$info) {
            return \json(['code' => -1, 'msg' => '无该文章']);
        }
        $db = Base::getUserData($info->writerid);
        if (!$db) {
            return \json(['code' => -1, 'msg' => '该用户已注销，无法查看']);
        }
        //权限验证
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        $can_edit = ($info->writerid == $my_aid) || $isroot;
        if ($info->public != 1 && $info->writerid != $my_aid && !$isroot) {
            return \json(['code' => -1, 'msg' => '私密文章不可见']);
        }
        $info->writer = $db->name;
        $islove = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 0)
            ->exists();
        $isfabulous = Db::table('fabulousarticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 0)
            ->exists();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'love' => $islove, 'fabulous' => $isfabulous, 'edit' => $can_edit, 'msg' => '加载文章成功']);
    }

    /**
     * 加载HTML文章
     */
    public function oneArticle(Request $request)
    {
        try {
            $article_uid = $request->get('path');
            if (!$article_uid) {
                return Base::notFoundPage();
            }
            return Base::getHTMLArticle($article_uid);
        } catch (Exception $e) {
            return Base::notFoundPage();
        }
    }

    /**
     * 查看指定用户写的文章
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadUserHomeArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $user_uid = $request->post('user_id');
        $user_id = Base::getIdByUid($user_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $limit = $request->post('limit');
        Base::judgeLimitIsSafe($limit);
        $isroot = Base::judgeIsRoot($my_aid);
        $res = [];
        if ($isroot) {
            if (!$article_id) {
                $res = Db::table('article')
                    ->where('writerid', $user_id)
                    ->where('isdel', 0)
                    ->select(Article::$article_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $res = Db::table('article')
                    ->where('writerid', $user_id)
                    ->where('isdel', 0)
                    ->where('id', '<', $article_id)
                    ->select(Article::$article_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            }
        } else {
            if (!$article_id) {
                $res = Db::table('article')
                    ->where('writerid', $user_id)
                    ->where('public', 1)
                    ->where('isdel', 0)
                    ->select(Article::$article_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            } else {
                $res = Db::table('article')
                    ->where('writerid', $user_id)
                    ->where('public', 1)
                    ->where('isdel', 0)
                    ->where('id', '<', $article_id)
                    ->select(Article::$article_db_key)
                    ->orderBy('id', 'desc')
                    ->limit($limit)
                    ->get();
            }
        }
        $user_db = Base::getUserData($user_id);
        if (!$user_db) {
            return \json(['code' => -1, 'data' => [], 'msg' => '该用户已注销，无法查看']);
        }
        foreach ($res as &$tem) {
            $tem->writer = $user_db->name;
        }
        Base::dataToSafe($res);
        return \json(['code' => 1, 'data' => $res]);
    }

    /**
     * 查找收藏的文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function findLoveArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key'); //article key
        $lovearticles = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->get();
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $res = array();

        foreach ($lovearticles as &$tem) {
            $temarticle = Db::table('article')
                ->where('id', $tem->articleid)
                ->where('isdel', 0)
                ->where('name', 'like', '%' . $key . '%')
                ->select(Article::$article_db_key)
                ->first();
            if ($temarticle) {
                if ($temarticle->public != 1) {
                    if ($temarticle->writerid == $my_aid || Base::judgeIsRoot($my_aid)) {
                        $res[] = $temarticle;
                    }
                } else {
                    $res[] = $temarticle;
                }
            }
        }
        $allnum = sizeof($res);
        $resdata = array();
        for ($i = $limit * ($page - 1); $i < $limit * $page && $i < $allnum; ++$i) {
            $db = Base::getUserData($res[$i]->writerid);
            if (!$db) {
                continue;
            }
            $res[$i]->writer = $db->name;
            $resdata[] = $res[$i];
        }
        Base::dataToSafe($resdata);
        return \json(['code' => 1, 'data' => $resdata, 'allnum' => $allnum, 'msg' => "找到符合条件的文章有：$allnum 条"]);
    }

    /**
     * 加载收藏的文章列表
     * @param $request 请求
     * @return string $res json 
     */
    public function loadLoveArticleList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $lovearticles = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->get();
        $publicarticlelist = array();

        $allnum = 0;
        foreach ($lovearticles as &$tem) {
            $db = Db::table('article')
                ->where('id', $tem->articleid)
                ->where('isdel', 0)
                ->select(Article::$article_db_key)
                ->first();
            if ($db) {
                if ($db->public != 1) {
                    if ($db->writerid == $my_aid || Base::judgeIsRoot($my_aid)) {
                        $publicarticlelist[] = $db;
                    }
                } else {
                    $publicarticlelist[] = $db;
                }
            }
        }
        $allnum = sizeof($publicarticlelist);
        $res = array();
        for ($i = ($page - 1) * $limit, $j = 0; $j < $limit && $i < sizeof($publicarticlelist); ++$i, ++$j) {
            $db = Base::getUserData($publicarticlelist[$i]->writerid);
            if (!$db) {
                continue;
            }
            $publicarticlelist[$i]->writer = $db->name;
            $res[] = $publicarticlelist[$i];
        }
        $nowtime = date('Y-m-d H:i:s', time());
        Base::dataToSafe($res);
        return json(['code' => 1, 'data' => $res, 'allnum' => $allnum, 'msg' => "截至到 $nowtime 您一共收藏了 $allnum 篇文章"]);
    }

    /**
     * 取消收藏一篇文章
     * @param Request $request 请求
     * @return string $res json
     */
    public function deleteLoveArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $db = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            return \json(['code' => 0, 'msg' => "您没有收藏该文章，无法取消收藏"]);
        }

        $lovearticles = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->update(['isdel' => 1]);

        if ($lovearticles) {
            Db::table('article')
                ->where('id', $article_id)
                ->where('collection', '>', 0)
                ->decrement('collection', 1);
            Base::updateArticleDataRedis($article_id);
            return \json(['code' => 1, 'msg' => '取消收藏成功']);
        }
        return \json(['code' => 0, 'msg' => "您没有收藏该文章，无法取消收藏"]);
    }

    /**
     * 更新一篇文章
     * @param Request $request 请求
     * @return string $res json
     */
    public function updataOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $tabledata = $request->post('data');
        Base::dataToUnSafe($tabledata);
        $isroot = Base::judgeIsRoot($my_aid);
        $isme = $this->judgeArticleWritenByMe($my_aid, $tabledata['id']);
        if (!$isme && !$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }

        $data = [
            'name' => $tabledata['name'],
            'article' => Base::utfsubstr(strip_tags($tabledata['article']), 0, Base::$home_one_article_max_length),
            'lastchangetime' => date('Y-m-d H:i:s', time()),
            'image' => $tabledata['image'],
            'public' => $tabledata['public']
        ];
        Db::table('article')
            ->where('id', $tabledata['id'])
            ->update($data);
        Db::table('article_data')
            ->where('article_id', $tabledata['id'])
            ->update([
                'data' => $data
            ]);
        Base::updateArticleDataRedis($tabledata['id']);
        return json(['code' => 1, 'msg' => '更新成功']);
    }

    /**
     * 加载自己发布的文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function loadMyArticleList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $info = Db::table('article')
            ->where('writerid', $my_aid)
            ->where('isdel', 0)
            ->select(Article::$article_db_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('article')
            ->where('writerid', $my_aid)
            ->where('isdel', 0)
            ->count();
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "您一共发布 $allnum 篇文章"]);
    }

    /**
     * 加载所有文章（root可以查看私密文章）
     * @param Request $request 请求
     * @return string $res json 
     */
    public function loadAllArticleList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $limit = $request->post('limit');
        $do = $request->post('do');
        $info = [];
        Base::judgeLimitIsSafe($limit);
        if (Base::judgeIsRoot($my_aid)) {
            if ($do == 'down') {
                if ($article_uid == '' || !$article_uid) {
                    $info = Db::table('article')
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                } else {
                    $info = Db::table('article')
                        ->where('id', '<', $article_id)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                }
            } else if ($do === 'up') {
                if ($article_uid == '' || !$article_uid) {
                    return json(['code' => 1, 'data' => [], 'msg' => "加载成功"]);
                } else {
                    $info = Db::table('article')
                        ->where('id', '>', $article_id)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'asc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                    $info = array_reverse($info);
                }
            }
        } else {
            if ($do == 'down') {
                if ($article_uid == '' || !$article_uid) {
                    $info = Db::table('article')
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                } else {
                    $info = Db::table('article')
                        ->where('id', '<', $article_id)
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                }
            } else if ($do === 'up') {
                if ($article_uid == '' || !$article_uid) {
                    return json(['code' => 1, 'data' => [], 'msg' => "加载成功"]);
                } else {
                    $info = Db::table('article')
                        ->where('id', '>', $article_id)
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'asc')
                        ->limit($limit)
                        ->get()
                        ->toArray();
                    $info = array_reverse($info);
                }
            }
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'msg' => "加载成功"]);
    }

    /**
     * 一道题目的题目题解列表
     * @param Request $request 请求
     * @return string $res json 
     */
    public function problemSolveArticleList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $now = time();
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);
        $problem_uid = $request->post('problem_id');
        $problem_id = Base::getIdByUid($problem_uid);
        $iscontest = Db::table('contestproblem')
            ->where('problemid', $problem_id)
            ->where('isdel', 0)
            ->select('contestid')
            ->get();
        $isend = true;
        foreach ($iscontest as &$tem) {
            $temdb = Db::table('contest')
                ->where('id', $tem->contestid)
                ->where('isdel', 0)
                ->select('end')
                ->first();
            if ($temdb) {
                if (strtotime($temdb->end) >= $now) {
                    $isend = false;
                    break;
                }
            }
        }
        $isroot = Base::judgeIsRoot($my_aid);
        if (!$isroot && !$isend) {
            return json(['code' => -1, 'msg' => '无权限！竞赛未结束！即将返回！']);
        }
        $data = [];
        if ($isroot) {
            $data = Db::table('article')
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->select(Article::$article_db_key)
                ->orderBy('fabulous', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
        } else {
            $data = Db::table('article')
                ->where('problemid', $problem_id)
                ->where('isdel', 0)
                ->where('public', 1)
                ->select(Article::$article_db_key)
                ->orderBy('fabulous', 'desc')
                ->paginate($limit, '*', 'page', $page)
                ->items();
        }
        foreach ($data as &$tem) {
            $db = Base::getUserData($tem->writerid);
            if (!$db) {
                $tem->writer = '未知用户';
                continue;
            }
            $tem->writer = $db->name;
        }
        Base::dataToSafe($data);
        return json(['code' => 1, 'data' => $data, 'msg' => '加载完成！']);
    }

    /**
     * 随机一篇文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function randomOneArticle()
    {
        $writerid = Base::getRobotId();
        if (!$writerid) {
            Base::sendErrorNotice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), '机器人账号不存在！');
            $writerid = Base::getRobotId();
        }
        $db = Db::table('article')
            ->where('writerid', $writerid)
            ->where('public', 1)
            ->where('isdel', 0)
            ->select('id')
            ->inRandomOrder()
            ->first();
        $db = Base::getArticleData($db->id);
        Base::dataToSafe($db);
        return json(['data' => $db]);
    }

    /**
     * 发布文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function writeOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $now = time();
        //限制非root用户发布频率
        $user_db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('grade', 'name')
            ->first();
        if (!$user_db) {
            return json(['code' => -1, 'msg' => '用户不存在']);
        }
        $redis = Redis::connection('db10');
        if ($user_db->grade != 3) {
            if ($redis->get('limitwrite' . $my_aid)) {
                return \json(['code' => -1, 'msg' => '您的发布速度是碳基生物的操作吗？10秒后请再次尝试！']);
            }
            $redis->setEx('limitwrite' . $my_aid, 1, 10);
        }

        $releasetime = date('Y-m-d H:i:s', $now);
        $lastchangetime = date('Y-m-d H:i:s', $now);

        $temname = $request->post('name');
        if (mb_strlen($temname) > Base::$article_name_limit) {
            return \json(['code' => -1, 'msg' => '标题不能超过' . Base::$article_name_limit . '个字符']);
        }

        $temarticle = $request->post('article');
        $tem1 = preg_replace('/ /', '', $temname);
        $tem2 = preg_replace('/ /', '', $temarticle);
        if ($tem1 == '' || $tem2 == '') {
            return \json(['code' => -1, 'msg' => '标题和内容不能为空']);
        }

        if ($request->post('image') != '' && filter_var($request->post('image'), FILTER_VALIDATE_URL) == false) {
            return \json(['code' => -1, 'msg' => '图片链接不合法']);
        }

        $problemid = 0;
        if ($request->post('problem_id')) {
            $problemid = Base::getIdByUid($request->post('problem_id'));
        }
        $is_public = $request->post('public');
        if ($is_public != 0 && $is_public != 1) {
            $is_public = 0;
        }
        $tabledata = [
            'problemid' => $problemid,
            'writerid' => $my_aid,
            'image' => $request->post('image'),
            'fabulous' => 0,
            'collection' => 0,
            'releasetime' => $releasetime,
            'lastchangetime' => $lastchangetime,
            'name' => $request->post('name'),
            'article' => Base::utfsubstr(strip_tags($request->post('article')), 0, Base::$home_one_article_max_length),
            'public' => $is_public,
        ];
        $urlimage = $tabledata['image'];
        $tem = preg_replace('/ /', '', $urlimage);
        //默认随机图片
        if ($tem == "") {
            $tabledata['image'] = Image::randimage();
        }
        $article_id = Base::insertToDb('article', $tabledata);
        Base::insertToDb('article_data', [
            'article_id' => $article_id,
            'data' => $request->post('article')
        ]);
        Base::updateArticleDataRedis($article_id);
        return json(['code' => 1, 'msg' => '发布成功']);
    }

    /**
     * 删除文章
     * @param Request $request 请求
     * @return string $res json 
     */
    public function deleteOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('delete_id');
        $article_id = Base::getIdByUid($article_uid);
        $isroot = Base::judgeIsRoot($my_aid);
        $isme = $this->judgeArticleWritenByMe($my_aid, $article_id);
        if (!$isme && !$isroot) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }

        $is_exies = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->exists();
        if ($is_exies) {
            //删除文章，点赞，收藏，评论
            Db::table('article')->where('id', $article_id)->update(['isdel' => 1]);
            Db::table('fabulousarticle')->where('articleid', $article_id)->update(['isdel' => 1]);
            Db::table('lovearticle')->where('articleid', $article_id)->update(['isdel' => 1]);
            Db::table('articlecomment')->where('articleid', $article_id)->update(['isdel' => 1]);
            Base::updateArticleDataRedis($article_id);
            return json(['code' => 1, 'msg' => '删除成功']);
        }
        return json(['code' => -1, 'msg' => '该内容不存在']);
    }

    /**
     * 判断是否收藏该文章
     * @param Request $request 请求
     * @return string $res json（1为收藏，0为未收藏）
     */
    public function judgeIsLoveOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $db = Db::table('lovearticle')
            ->where('articleid', $article_id)
            ->where('userid', $my_aid)
            ->where('isdel', 0)
            ->exists();
        if ($db) {
            return json(['code' => 1]);
        }
        return json(['code' => 0]);
    }

    /**
     * 获取作者头像
     * @param Request $request 请求
     * @return string $res json
     */
    public function loadWriterHeadimage(Request $request)
    {
        $writer_uid = $request->post('writer_id');
        $writer_id = Base::getIdByUid($writer_uid);
        $headimg = Base::getUserHeadimage($writer_id);
        return \json(['code' => 1, 'image' => $headimg]);
    }

    /**
     * 个人文章搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function myArticleKeySearch(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $limit = $request->post('limit');
        $page = $request->post('page');
        Base::judgePageLimitIsSafe($page, $limit);
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return json(['code' => -1, 'msg' => '查询失败']);
        }
        $info = Db::table('article')
            ->where('writerid', $my_aid)
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->select(Article::$article_db_key)
            ->paginate($limit, '*', 'page', $page)
            ->items(); //模糊查询
        $allnum = Db::table('article')
            ->where('writerid', $my_aid)
            ->where('isdel', 0)
            ->where('name', 'like', '%' . $key . '%')
            ->count(); //模糊查询
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info, 'allnum' => $allnum, 'msg' => "查询到 $allnum 条结果"]);
    }

    /**
     * 点赞文章
     * @param Request $request 请求
     * @return string $res json
     */
    public function fabulousOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $user_db = Db::table('user')
            ->where('id', $my_aid)
            ->where('isdel', 0)
            ->select('name')
            ->first();
        if (!$user_db) {
            return \json(['code' => '0', 'msg' => '您的账号不存在！']);
        }

        $article_db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->select('public', 'writerid')
            ->first();
        if (!$article_db) {
            return \json(['code' => '-1', 'msg' => '该内容不存在']);
        }
        if ($article_db->public != 1) {
            if (!Base::judgeIsRoot($my_aid) && $article_db->writerid != $my_aid) {
                return \json(['code' => '-1', 'msg' => '该内容不可见！']);
            }
        }

        $dbfabulous = Db::table('fabulousarticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 0)
            ->exists();

        if ($dbfabulous) {
            return \json(['code' => '0', 'msg' => '请勿重复点赞']);
        }

        $dbfabulous = Db::table('fabulousarticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 1)
            ->select('id')
            ->first();

        $res = false;
        if ($dbfabulous) {
            $res = Db::table('fabulousarticle')
                ->where('id', $dbfabulous->id)
                ->update([
                    'isdel' => 0
                ]);
        } else {
            $res = Db::table('fabulousarticle')
                ->insert([
                    'userid' => $my_aid,
                    'articleid' => $article_id
                ]);
        }

        if ($my_aid != $article_db->writerid) {
            Base::insertToDb('usernotice', [
                'userid' => $article_db->writerid,
                'notice' => $user_db->name . '点赞了你的文章',
                'articleid' => $article_id,
                'fanuserid' => 0,
                'time' => date('Y-m-d H:i:s', time())
            ]);
        }

        if ($res) {
            Db::table('article')
                ->where('id', $article_id)
                ->increment('fabulous', 1);
            Base::updateArticleDataRedis($article_id);
            return \json(['code' => 1, 'msg' => '点赞成功']);
        }
        return \json(['code' => '-1', 'msg' => '点赞失败']);
    }

    /**
     * 收藏文章
     * @param Request $request 请求
     * @return string $res json
     */
    public function collectionOneArticle(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $article_db = Db::table('article')
            ->where('id', $article_id)
            ->where('isdel', 0)
            ->select('public', 'writerid')
            ->first();
        if (!$article_db) {
            return \json(['code' => '-1', 'msg' => '该内容不存在']);
        }
        if ($article_db->public != 1) {
            if (Base::judgeIsRoot($my_aid) && $article_db->writerid != $my_aid) {
                return \json(['code' => '-1', 'msg' => '该内容不可见！']);
            }
        }
        $dbcollection = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 0)
            ->exists();

        if ($dbcollection) {
            return \json(['code' => '0', 'msg' => '请勿重复收藏']);
        }

        $dbcollection = Db::table('lovearticle')
            ->where('userid', $my_aid)
            ->where('articleid', $article_id)
            ->where('isdel', 1)
            ->select('id')
            ->first();

        $res = false;
        if ($dbcollection) {
            $res = Db::table('lovearticle')
                ->where('id', $dbcollection->id)
                ->update([
                    'isdel' => 0,
                ]);
        } else {
            $res = Base::insertToDb('lovearticle', [
                'articleid' => $article_id,
                'userid' => $my_aid
            ]);
        }

        if ($res) {
            Db::table('article')
                ->where('id', $article_id)
                ->increment('collection', 1);
            $userdb = Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->select('name')
                ->first();
            if ($my_aid != $article_db->writerid) {
                Base::insertToDb('usernotice', [
                    'userid' => $article_db->writerid,
                    'notice' => $userdb->name . '收藏了你的文章',
                    'articleid' => $article_id,
                    'fanuserid' => 0,
                    'time' => date('Y-m-d H:i:s', time())
                ]);
            }
            Base::updateArticleDataRedis($article_id);
            return \json(['code' => 1, 'msg' => '收藏成功']);
        }

        return \json(['code' => '-1', 'msg' => '收藏失败']);
    }

    /**
     * 所有文章搜索
     * @param Request $request 请求
     * @return string $res json
     */
    public function allArticleKeySearch(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $article_uid = $request->post('article_id');
        $article_id = Base::getIdByUid($article_uid);
        $limit = $request->post('limit');
        $do = $request->post('do');
        if ($limit > Base::$db_get_limit) {
            $limit = Base::$db_get_limit;
        }
        $key = $request->post('key');
        if (!isset($key) || empty($key)) {
            return json(['code' => -1, 'msg' => '参数错误']);
        }
        $info = [];

        if (Base::judgeIsRoot($my_aid)) {
            if (!$article_uid || $article_uid == '') {
                if ($do == 'up') {
                    return json(['code' => 1, 'data' => []]);
                } else if ($do == 'down') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->paginate($limit, '*', 'page', 1)
                        ->items();
                }
            } else {
                if ($do == 'up') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('id', '>', $article_id)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get();
                } else if ($do == 'down') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('id', '<', $article_id)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get();
                }
            }
        } else {
            if (!$article_uid || $article_uid == '') {
                if ($do == 'up') {
                    return json(['code' => 1, 'data' => []]);
                } else if ($do == 'down') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get();
                }
            } else {
                if ($do == 'up') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->where('id', '>', $article_id)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get();
                } else if ($do == 'down') {
                    $info = Db::table('article')
                        ->where('name', $key)
                        ->where('public', 1)
                        ->where('isdel', 0)
                        ->where('id', '<', $article_id)
                        ->select(Article::$article_db_key)
                        ->orderBy('id', 'desc')
                        ->limit($limit)
                        ->get();
                }
            }
        }
        foreach ($info as &$tem) {
            $db = Base::getUserData($tem->writerid);
            if (!$db) {
                $tem->writer = '未知用户';
                continue;
            }
            $tem->writer = $db->name;
        }
        Base::dataToSafe($info);
        return json(['code' => 1, 'data' => $info]);
    }
};
