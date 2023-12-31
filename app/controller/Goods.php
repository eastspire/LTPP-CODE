<?php

namespace app\controller;


use support\Request;
use support\Db;
use Tinywan\Jwt\JwtToken;


class Goods
{
    /**
     * 数量上限
     */
    static $limit = 50;

    /**
     * 商品文件根路径
     */
    static $root_path = '/home/LTPP/public/static/goods/';

    /**
     * 列表展示数据库字段
     */
    static $goods_db_list_key = [
        'id',
        'name',
        'money',
        'time',
        'type',
        'size',
        'times'
    ];

    /**
     * 详情页展示数据库字段
     */
    static $goods_db_key = [
        'id',
        'name',
        'money',
        'time',
        'blurb',
        'type',
        'size',
        'times'
    ];

    /**
     *  商品UID获取文件夹名称
     */
    private function getNameByUid($goods_uid)
    {
        return md5($goods_uid);
    }

    /**
     * 获取列表
     */
    public function getList(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);

        $db = Db::table('goods')
            ->where('isdel', 0)
            ->select(Goods::$goods_db_list_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('goods')
            ->where('isdel', 0)
            ->count();
        $isroot = Base::judgeIsRoot($my_aid);
        foreach ($db as &$t) {
            $t->money = rtrim(rtrim($t->money, '0'), '.');
            if ($isroot) {
                $t->has_buy = true;
            } else {
                $t->has_buy = Db::table('orderforgoods')
                    ->where('isdel', 0)
                    ->where('userid', $my_aid)
                    ->where('goodsid', $t->id)
                    ->exists();
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'allnum' => $allnum]);
    }

    /**
     * 关键字搜索
     */
    public function keySearch(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $key = $request->post('key');
        $page = $request->post('page');
        $limit = $request->post('limit');
        Base::judgePageLimitIsSafe($page, $limit);

        $db = Db::table('goods')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->select(Goods::$goods_db_list_key)
            ->orderBy('id', 'desc')
            ->paginate($limit, '*', 'page', $page)
            ->items();
        $allnum = Db::table('goods')
            ->where('name', 'like', '%' . $key . '%')
            ->where('isdel', 0)
            ->count();
        $isroot = Base::judgeIsRoot($my_aid);
        foreach ($db as &$t) {
            $t->money = rtrim(rtrim($t->money, '0'), '.');
            if ($isroot) {
                $t->has_buy = true;
            } else {
                $t->has_buy = Db::table('orderforgoods')
                    ->where('isdel', 0)
                    ->where('userid', $my_aid)
                    ->where('goodsid', $t->id)
                    ->exists();
            }
        }
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db, 'allnum' => $allnum]);
    }


    /**
     * 查看详情
     */
    public function lookOne(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $goods_uid = $request->post('id') ?? '';
        $goods_id = Base::getIdByUid($goods_uid);
        $db = Db::table('goods')
            ->where('id', $goods_id)
            ->where('isdel', 0)
            ->select(Goods::$goods_db_key)
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '内容不存在！']);
        }
        $isroot = Base::judgeIsRoot($my_aid);

        if ($isroot) {
            $db->has_buy = true;
        } else {
            $db->has_buy = Db::table('orderforgoods')
                ->where('isdel', 0)
                ->where('userid', $my_aid)
                ->where('goodsid', $db->id)
                ->exists();
        }
        $db->money = rtrim(rtrim($db->money, '0'), '.');
        Base::dataToSafe($db);
        return json(['code' => 1, 'data' => $db]);
    }

    /**
     * 更新
     */
    public function updateOne(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足！']);
        }
        $goods = $request->post('goods') ?? '';
        Base::dataToUnSafe($goods);
        if (!is_numeric($goods['money'])) {
            return json(['code' => '-1', 'msg' => '价格只能是数字！']);
        }
        $db = Db::table('goods')
            ->where('id', $goods['id'])
            ->where('isdel', 0)
            ->select(Goods::$goods_db_key)
            ->first();
        if (!$db) {
            return json(['code' => -1, 'msg' => '内容不存在！']);
        }
        Db::table('goods')
            ->where('id', $goods['id'])
            ->update([
                'name' => substr($goods['name'] ?? '未命名商品', 0, Base::$other_name_len_limit),
                'money' => max(0, $goods['money']),
                'blurb' => $goods['blurb'] ?? '暂无该商品的相关介绍',
                'time' => date('Y-m-d H:i:s', time())
            ]);
        return json(['code' => 1, 'data' => $db, 'msg' => '更新成功！']);
    }

    /**
     * 判断余额
     */
    public function judgeCanBuy(Request $request)
    {
        $goods_uid = $request->post('id') ?? '';
        $goods_id = Base::getIdByUid($goods_uid);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $goods = Db::table('goods')
            ->where('id', $goods_id)
            ->where('isdel', 0)
            ->select('money', 'path')
            ->first();
        if (!$goods) {
            return json(['code' => -1, 'msg' => '商品不存在！']);
        }
        $db = Db::table('orderforgoods')
            ->where('isdel', 0)
            ->where('userid', $my_aid)
            ->where('goodsid', $goods_id)
            ->exists();
        if ($db || Base::judgeIsRoot($my_aid)) {
            return json(['code' => 1, 'msg' => '您已拥有过该商品！下载免费！']);
        }
        $my_data = Base::getUserData($my_aid);
        $money = $my_data->money;
        $goods_money = $goods->money;
        if ($money - $goods_money < 0 && !Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '余额不足！']);
        }
        return json(['code' => 1, 'msg' => '余额充足！下载自动扣费！']);
    }

    /**
     * 下载文件
     */
    public function downloadOne(Request $request)
    {
        $goods_uid = $request->post('id') ?? '';
        $goods_id = Base::getIdByUid($goods_uid);
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $goods = Db::table('goods')
            ->where('id', $goods_id)
            ->where('isdel', 0)
            ->select('id', 'money', 'path')
            ->first();
        if (!$goods) {
            return json(['code' => -1, 'msg' => '商品不存在！']);
        }
        $db = Db::table('orderforgoods')
            ->where('userid', $my_aid)
            ->where('goodsid', $goods_id)
            ->where('isdel', 0)
            ->exists();
        if ($db || Base::judgeIsRoot($my_aid)) {
            // 下载
            Db::table('goods')
                ->where('id', $goods->id)
                ->increment('times', 1);
            return $this->downloadFile($goods->path);
        }
        $my_data = Base::getUserData($my_aid);
        $money = $my_data->money;
        $goods_money = $goods->money;
        if ($money - $goods_money < 0) {
            return json(['code' => -1, 'msg' => '余额不足！']);
        }
        if ($goods_money) {
            Db::table('user')
                ->where('id', $my_aid)
                ->where('isdel', 0)
                ->decrement('money', $goods_money);

            Base::updateUserDataRedis($my_aid);
        }
        $res = Base::insertToDb('orderforgoods', [
            'userid' => $my_aid,
            'goodsid' => $goods_id,
            'time' => date('Y-m-d H:i:s', time())
        ]);
        if (!$res) {
            return json(['code' => -1, 'msg' => '购买失败！请重试！']);
        }
        Db::table('goods')
            ->where('id', $goods->id)
            ->increment('times', 1);
        return $this->downloadFile($goods->path);
    }


    /**
     * 开始下载
     * @param string $goods_uid 商品加密ID
     */
    private function downloadFile($path)
    {
        $is_safe = strripos($path, Goods::$root_path);
        if ($is_safe === false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $is_safe = strripos($path, '..');
        if ($is_safe !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        if (file_exists($path)) {
            return response('')->download($path);
        }
        return json(['code' => -1, 'msg' => '文件不存在']);
    }

    /**
     * 商品数据库保存 
     */
    public function saveFileToDb(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $data = $request->post('goods') ?? '';
        if (!$data || $data == '') {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }

        if (!isset($data['money'])) {
            return json(['code' => '-1', 'msg' => '商品价格不能为空']);
        }

        if (!is_numeric($data['money'])) {
            return json(['code' => '-1', 'msg' => '商品价格只能是数字']);
        }

        if (!isset($data['name']) || !$data['name']) {
            return json(['code' => '-1', 'msg' => '商品名称不能为空']);
        }
        $res_id = Base::insertToDb('goods', [
            'name' => substr($data['name'] ?? '未命名商品', 0, Base::$other_name_len_limit),
            'money' => max(0, $data['money'] ?? 0),
            'path' => '',
            'blurb' => $data['blurb'] ?? $data['name'],
            'time' => date('Y-m-d H:i:s', time()),
            'type' => '',
            'size' => 0,
            'times' => 0
        ]);
        if ($res_id) {
            return json(['code' => 1, 'msg' => '保存成功', 'data' => Base::getUidById($res_id)]);
        }
        return json(['code' => -1, 'msg' => '保存失败']);
    }

    /**
     * 文件上传
     * @param Request $request 请求
     * @return string $res json
     */
    public function uploadFile(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $goods_uid = $request->post('id') ?? '';
        if (!$goods_uid || $goods_uid == '') {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        $goods_id = Base::getIdByUid($goods_uid);
        $md5month = md5(date("Y-m", time()));
        $postpath = Goods::$root_path . $md5month . '/' . $this->getNameByUid($goods_id);

        $isexist = strripos($postpath, '..');
        if ($isexist !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }
        $db = Db::table('goods')
            ->where('id', $goods_id)
            ->where('isdel', 0)
            ->exists();
        if (!$db) {
            return json(['code' => -1, 'msg' => '数据库没有该商品记录']);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }

        $up_full_name = $file->getUploadName();
        if (Base::getStrLen($up_full_name) > Base::$file_name_length_limit) {
            return json(['code' => -1, 'msg' => '文件名字符数不能超过' . Base::$file_name_length_limit . '个']);
        }

        Base::judgeCreatPath(Goods::$root_path . $md5month);
        $newName = '';
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $file->getUploadExtension();
        } while (file_exists($postpath . '/' . $newName));

        $size = $file->getSize();
        $file->move($postpath . '/' . $newName);
        Base::getChineseSize($size);
        Db::table('goods')
            ->where('id', $goods_id)
            ->update([
                'path' => $postpath . '/' . $newName,
                'type' => substr($file->getUploadExtension() ?? '', 0, Base::$other_name_len_limit),
                'size' => $size
            ]);
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return json(['code' => 1, 'msg' => '上传成功', 'filename' => $newName]);
    }

    /**
     * 批量上传
     */
    public function uploadMoreFile(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        if (!Base::judgeIsRoot($my_aid)) {
            return json(['code' => -1, 'msg' => '权限不足']);
        }
        $md5month = md5(date("Y-m", time()));
        $postpath = Goods::$root_path . $md5month . '/';
        $isexist = strripos($postpath, '..');
        if ($isexist !== false) {
            return json(['code' => -1, 'msg' => '路径不合法！']);
        }

        $file = $request->file('file');

        if (!$file->isValid()) {
            return json(['code' => -1, 'msg' => '文件不存在']);
        }

        $up_full_name = $file->getUploadName();
        $type = $file->getUploadExtension();
        $size = $file->getSize();
        Base::getChineseSize($size);
        $res_id = Base::insertToDb('goods', [
            'name' => substr($up_full_name ?? '未命名商品', 0, Base::$other_name_len_limit),
            'type' => substr($type ?? '', 0, Base::$other_name_len_limit),
            'money' => 0.5,
            'path' => '',
            'blurb' => $up_full_name ?? '暂无该商品的相关介绍',
            'time' => date('Y-m-d H:i:s', time()),
            'size' => $size,
            'times' => 0
        ]);
        $postpath .= $this->getNameByUid(Base::getUidById($res_id));
        Base::judgeCreatPath(Goods::$root_path . $md5month);
        $newName = '';
        do {
            $newName = md5(uniqid() . mt_rand(1, 100000) . time()) . '.' . $type;
        } while (file_exists($postpath . '/' . $newName));

        $file->move($postpath . '/' . $newName);
        Db::table('goods')
            ->where('id', $res_id)
            ->update([
                'path' => $postpath . '/' . $newName,
            ]);
        //删除上传的临时文件
        Base::deleteAllFile($file->getRealPath());
        return json(['code' => 1, 'msg' => '上传成功', 'filename' => $newName]);
    }


    /**
     * 获取我购买的商品列表
     */
    public function getListMyBuyGoods(Request $request)
    {
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        $isroot = Base::judgeIsRoot($my_aid);
        Base::judgePageLimitIsSafe($page, $limit);
        $buy_db = [];
        $res = [];
        $allnum = 0;
        if ($isroot) {
            $res = Db::table('goods')
                ->where('isdel', 0)
                ->select(Goods::$goods_db_list_key)
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('goods')
                ->where('isdel', 0)
                ->count();
            foreach ($res as &$t) {
                $t->has_buy = true;
                $t->money = rtrim(rtrim($t->money, '0'), '.');
            }
        } else {
            $buy_db = Db::table('orderforgoods')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->select('goodsid')
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $res = [];
            foreach ($buy_db as &$tem) {
                $db = Db::table('goods')
                    ->where('id', $tem->goodsid)
                    ->where('isdel', 0)
                    ->select(Goods::$goods_db_list_key)
                    ->first();
                if ($db) {
                    $db->has_buy = true;
                    $db->money = rtrim(rtrim($db->money, '0'), '.');
                    $res[] = $db;
                }
            }
            $allnum = Db::table('orderforgoods')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->count();
        }

        Base::dataToSafe($res);
        return json(['code' => 1, 'data' => $res, 'allnum' => $allnum]);
    }

    /**
     * 我购买的商品关键字搜索
     */
    public function keySearchMyBuyGoods(Request $request)
    {
        $key = $request->post('key');
        $my_uid = JwtToken::getCurrentId();
        $my_aid = Base::getIdByUid($my_uid);
        $page = $request->post('page');
        $limit = $request->post('limit');
        $isroot = Base::judgeIsRoot($my_aid);
        Base::judgePageLimitIsSafe($page, $limit);
        $buy_db = [];
        $allnum = 0;
        if ($isroot) {
            $res = Db::table('goods')
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->paginate($limit, '*', 'page', $page)
                ->items();
            $allnum = Db::table('goods')
                ->where('name', 'like', '%' . $key . '%')
                ->where('isdel', 0)
                ->count();
            foreach ($res as &$t) {
                $t->has_buy = true;
                $t->money = rtrim(rtrim($t->money, '0'), '.');
            }
        } else {
            $buy_db = Db::table('orderforgoods')
                ->where('userid', $my_aid)
                ->where('isdel', 0)
                ->select('goodsid')
                ->get();
            $res = [];
            $allnum = 0;
            $begin = ($page - 1) * $limit;
            $end = $page * $limit;
            foreach ($buy_db as &$tem) {
                $db = Db::table('goods')
                    ->where('id', $tem->goodsid)
                    ->where('name', 'like', '%' . $key . '%')
                    ->where('isdel', 0)
                    ->select(Goods::$goods_db_list_key)
                    ->first();
                if ($db) {
                    if ($allnum >= $begin && $allnum < $end) {
                        $db->has_buy = true;
                        $db->money = rtrim(rtrim($db->money, '0'), '.');
                        $res[] = $db;
                    }
                    ++$allnum;
                }
            }
        }
        Base::dataToSafe($res);
        return json(['code' => 1, 'data' => $res, 'allnum' => $allnum]);
    }
}
