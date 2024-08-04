/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: ltpp-universe 1491579574@qq.com
 * @LastEditTime: 2023-12-30 19:13:03
 * @FilePath: \LTPP-CODE\Frontend\src\plugins\axios.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved.
 */
'use strict';

import Vue from 'vue';
import axios from 'axios';
import store from '../plugins/vuex.js';

// Full config:  https://github.com/axios/axios#request-config
// axios.defaults.baseURL = process.env.baseURL || process.env.apiUrl || '';
// axios.defaults.headers.common['Authorization'] = AUTH_TOKEN;
// axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
const skip_key_list = ['timerstamp='];
const skip_key_list_len = skip_key_list?.length;

let config = {
  baseURL: store.state.backend_network_url, //process.env.baseURL || process.env.apiUrl || ""
  timeout: 6666666666, // Timeout
  // withCredentials: true, // Check cross-site Access-Control
};

const _axios = axios.create(config);
let musicbkurl = '';
let request_url = '';

_axios.interceptors.request.use(
  async function (config) {
    try {
      config.baseURL =
        store.state.backend_network_url ||
        store.state.default_backend_network_url;
      request_url = config?.baseURL + config?.url;
      // Do something before request is sent
      let char_set = [];
      try {
        char_set = window.sessionStorage('cloud_charset');
        char_set = JSON.parse(char_set);
      } catch (err) {
        char_set = [];
      }
      if (!char_set?.length) {
        char_set = await Vue.prototype.loadCloudCharset(
          !config?.isNoInitRequest
        );
      }
      if (config?.dataType !== 'jsonp') {
        // 精确到毫秒
        const now = new Date().getTime();
        config.headers.Requestid = Vue.prototype.Base64Encode(now, char_set);
      }
      /* 存在则不进行请求后端音乐地址 */
      if (
        config.portType &&
        config.portType.process &&
        config.portType.process
      ) {
        // 端口前统一加4与后端对应
        // let T_port = ":4" + config.portType.process;
        // config.baseURL += T_port;
        return config;
      } else {
        // config.baseURL += ":48787";
        if (config.dataType == 'jsonp') {
          musicbkurl = window.sessionStorage.getItem('musicbkurl');
          if (!musicbkurl) {
            const { data: res } = await axios({
              method: 'post',
              url: config.baseURL + '/Url/getMusicBkUrl',
              dataType: 'jsonp',
            }).catch((e) => {
              return config;
            });
            if (res?.code == 1) {
              config.baseURL = res?.data;
              musicbkurl = res?.data;
              try {
                window.sessionStorage.setItem('musicbkurl', res?.data);
              } catch (err) {}
            }
            return config;
          } else {
            config.baseURL = musicbkurl;
            return config;
          }
        }
      }
    } catch (err) {
      console.log(err);
    }
    return config;
  },
  function (error) {
    return Promise.reject(error);
  }
);

// Add a response interceptor
_axios.interceptors.response.use(
  function (response) {
    if (response?.data?.code == 500) {
      Vue.prototype.logoutRemove(true);
      return response;
    }

    let skip = false;
    const key = response?.request?.responseURL;
    if (!key) {
      return response;
    }
    for (let i = 0; i < skip_key_list_len; ++i) {
      if (key?.indexOf(skip_key_list[i]) != -1) {
        skip = true;
        break;
      }
    }
    if (!skip) {
      try {
        window.localStorage.setItem(key, JSON.stringify(response?.data));
      } catch (err) {}
    }
    return response;
  },
  function (error) {
    try {
      // Do something with response error
      if (request_url) {
        const cache_data = window.localStorage.getItem(request_url);
        const cache_res = JSON.parse(cache_data) || {
          data: [],
          code: -1,
          msg: `【请求失败】${error?.message}`,
        };
        const res = {
          data: cache_res,
        };
        return res;
      }
    } catch (err) {}
    const res = {
      data: {
        code: -1,
        msg: error?.message || '未知错误',
        data: [],
      },
    };
    return res;
  }
);

Plugin.install = function (Vue, options) {
  Vue.axios = _axios;
  window.axios = _axios;
  Object.defineProperties(Vue.prototype, {
    axios: {
      get() {
        return _axios;
      },
    },
    $axios: {
      get() {
        return _axios;
      },
    },
  });
};

Vue.use(Plugin);

export default Plugin;
