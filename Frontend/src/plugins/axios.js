/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-15 10:43:57
 * @FilePath: \LTPP-CODE\Frontend\src\plugins\axios.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
 */
"use strict";

import Vue from 'vue';
import axios from "axios";
import store from '../plugins/vuex.js'

// Full config:  https://github.com/axios/axios#request-config
// axios.defaults.baseURL = process.env.baseURL || process.env.apiUrl || '';
// axios.defaults.headers.common['Authorization'] = AUTH_TOKEN;
// axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';

let public_network_url = 'https://api.ltpp.vip';
let private_network_url = 'https://hbnuoj.ltpp.vip';

let config = {
    baseURL: store.state.is_public_network == 1 ? public_network_url : private_network_url, //process.env.baseURL || process.env.apiUrl || ""
    timeout: 6666666666, // Timeout
    // withCredentials: true, // Check cross-site Access-Control
};

const _axios = axios.create(config);
var musicbkurl = '';

_axios.interceptors.request.use(
    async function (config) {
        config.baseURL = store.state.is_public_network ? public_network_url : private_network_url;
        // Do something before request is sent
        /* 存在则不进行请求后端音乐地址 */
        if (config.portType && config.portType.process && config.portType.process) {
            // 端口前统一加4与后端对应
            // let T_port = ":4" + config.portType.process;
            // config.baseURL += T_port;
            return config;
        } else {
            // config.baseURL += ":48787";
            if (config.dataType == 'jsonp') {
                musicbkurl = window.sessionStorage.getItem("musicbkurl");
                if (!musicbkurl) {
                    const { data: res } = await axios({
                        method: 'post',
                        url: config.baseURL + '/Url/getMusicBkUrl',
                        dataType: 'jsonp'
                    }).catch((e) => {
                        return config;
                    });
                    if (res.code == 1) {
                        config.baseURL = res.data;
                        musicbkurl = res.data;
                        window.sessionStorage.setItem("musicbkurl", res.data);
                    }
                    return config;
                } else {
                    config.baseURL = musicbkurl;
                    return config;
                }
            }
        }
        return config;
    },
    function (error) {
        // Do something with request error
        return Promise.reject(error);
    }
);

// Add a response interceptor
_axios.interceptors.response.use(
    function (response) {
        if (response && response.data && response.data.code && response.data.code == 500) {
            Vue.prototype.logoutRemove(true);
        }
        return response;
    },
    function (error) {
        // Do something with response error
        return Promise.reject(error);
    }
);

Plugin.install = function (Vue, options) {
    Vue.axios = _axios;
    window.axios = _axios;
    Object.defineProperties(Vue.prototype, {
        axios: {
            get() {
                return _axios;
            }
        },
        $axios: {
            get() {
                return _axios;
            }
        },
    });
};

Vue.use(Plugin)

export default Plugin;