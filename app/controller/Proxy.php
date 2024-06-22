<?php

namespace app\controller;

use support\Request;

class Proxy
{
    /**
     * 接口路径
     */
    static $path_method = '/Proxy/proxyRequest';

    /**
     * 参数source_url名称
     */
    static $source_url_key_name = 'ltpp_source_url';

    /**
     * 参数source_request_header名称
     */
    static $source_request_header_key_name = 'ltpp_source_request_header';

    /**
     * 参数source_response_header名称
     */
    static $source_response_header_key_name = 'ltpp_source_response_header';

    /**
     * 参数source_data名称
     */
    static $source_data_key_name = 'ltpp_source_data';

    /**
     * 获取响应结果
     */
    protected function getResponse(&$file_data, &$response_header)
    {
        $data_len = strlen($file_data);
        $response_header['Content-Length'] = $data_len;
        $response_header['Content-Range'] = 'bytes 0-' . $data_len . '/' . $data_len;
        return Response($file_data, 200, $response_header);
    }

    /**
     * 加载代理抖音视频
     */
    public function proxyRequest(Request $request)
    {
        $url = urldecode($request->get(Proxy::$source_url_key_name, ''));
        // 请求头使用编码后的&字符串分割
        $request_header = urldecode($request->get(Proxy::$source_request_header_key_name, ''));
        // 响应头使用编码后的&字符串分割
        $tem_response_header = urldecode($request->get(Proxy::$source_response_header_key_name, ''));
        $tem_response_header = explode('&', $tem_response_header);
        $response_header = [];
        foreach ($tem_response_header as &$tem) {
            if (strpos($tem, ':') === false) {
                continue;
            }
            // 去除空格
            $tem = str_replace(' ', '', $tem);
            // :分割
            list($key, $value) = explode(':', $tem);
            if (!$key) {
                $key = '';
            }
            if (!$value) {
                $value = '';
            }
            $response_header[$key] = $value;
        }
        // 数据使用编码后的&字符串分割
        $data = urldecode($request->get(Proxy::$source_data_key_name, ''));
        // 是否是GET
        $is_get = !!!$data;
        // 根据解码后的&分割数据
        $request_header = explode('&', $request_header);
        // 根据解码后的&分割数据
        $data = explode('&', $data);
        $file_data = Base::notFoundPage();
        // GET
        if ($is_get) {
            $file_data = Base::getRequest($url, $request_header);
            if (!$file_data) {
                // 兜底
                $file_data = file_get_contents($url);
            }
            return Proxy::getResponse($file_data, $response_header);
        }
        $body = [];
        foreach ($data as &$param) {
            if (strpos($param, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $param);
            if (!$key) {
                $key = '';
            }
            if (!$value) {
                $value = '';
            }
            $body[$key] = $value;
        }
        // POST
        $file_data = Base::postRequest($url, $request_header, $body, false);
        return Proxy::getResponse($file_data, $response_header);
    }
}
