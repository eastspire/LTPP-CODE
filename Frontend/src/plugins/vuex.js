/*
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 18:43:57
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-09-11 09:47:31
 * @FilePath: \LTPP-CODE\Frontend\src\plugins\vuex.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved.
 */
import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);
const default_backend_network_url = 'https://api.ltpp.vip';
const default_home_to_left_right = 16;

const state = {
  default_backend_network_url: default_backend_network_url,
  backend_network_url: default_backend_network_url,
  backurl: window?.location?.href,
  login: false,
  root: false,
  admin: false,
  html_reg: /<[^>]+>|#|```/g, //HTML预览文字替换规则
  menu_width: 64, //首页菜单宽度，单位px
  default_margin_top_bottom: 1.6, //所有页面距离顶部和底部的距离，单位rem
  default_home_to_left_right: default_home_to_left_right, //单位px
  headimage: '', //头像
  bkimage: '', //图片背景
  bkvideo: '', //视频背景
  aid: 0, //用户id
  uid: '', //用户加密id
  my_uid: '', //我的加密id
  my_id: '', //我的id
  now_width: 0, //宽度
  no_scroll_height: 92, //无滚动高度，单位vw
  max_width:
    ((Math.min(1920, window.screen.width) - default_home_to_left_right * 2) /
      100) *
    86.3, //最大宽度
  server_error: false,
  image_use_remote: true,
  open_system_notice: true,
};

const root_state = {
  default_backend_network_url: default_backend_network_url,
  backend_network_url: default_backend_network_url,
  backurl: window?.location?.href,
  login: false,
  root: false,
  admin: false,
  html_reg: /<[^>]+>|#|```/g, //文章卡片预览文字替换规则
  menu_width: 64, //首页菜单宽度，单位px
  default_margin_top_bottom: 1.6, //所有页面距离顶部和底部的距离，单位rem
  default_home_to_left_right: default_home_to_left_right, //单位px
  headimage: '', //头像
  bkimage: '', //图片背景
  bkvideo: '', //视频背景
  aid: 0, //用户id
  uid: '', //用户加密id
  my_uid: '', //我的加密id
  my_id: '', //我的id
  now_width: 0, //宽度
  no_scroll_height: 92, //无滚动高度，单位vw
  max_width:
    ((Math.min(1920, window.screen.width) - default_home_to_left_right * 2) /
      100) *
    86.3, //最大宽度
  server_error: false,
  image_use_remote: true,
  open_system_notice: true,
};

let timer = null;
let old_msg = null;
let old_notice = null;
const new_notice = function (params) {};
const new_msg = function (params) {};
timer = setTimeout(function () {
  !old_msg && (old_msg = Vue.prototype.$msg);
  !old_notice && (old_notice = Vue.prototype.$notice);
  if (old_msg && old_notice) {
    clearTimeout(timer);
    timer = null;
  }
}, 0);

const mutations = {
  updateObj(state, obj) {
    let key = Object.keys(obj)[0];
    let value = Object.values(obj)[0];
    if (key != 'default_backend_network_url') {
      state[key] = value;
    }
    if (key == 'server_error' && old_msg && old_notice) {
      if (value) {
        Vue.prototype.$msg = new_msg;
        Vue.prototype.$notice = new_notice;
      } else {
        Vue.prototype.$msg = old_msg;
        Vue.prototype.$notice = old_notice;
      }
    }
  },
  reset(state) {
    for (const key in state) {
      if (Object.hasOwnProperty.call(state, key)) {
        if (
          key != 'default_backend_network_url' &&
          key != 'backend_network_url'
        ) {
          state[key] = root_state[key];
        }
      }
    }
  },
};

const store = new Vuex.Store({
  state,
  mutations,
});

export default store;
