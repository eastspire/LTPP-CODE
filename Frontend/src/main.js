/*
 * @Author: eastspire 1491579574@qq.com
 * @Date: 2023-08-07 18:43:57
 * @LastEditors: eastspire 1491579574@qq.com
 * @LastEditTime: 2024-01-08 22:06:49
 * @FilePath: \LTPP-CODE\Frontend\src\main.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2024 by SQS, All Rights Reserved.
 */
import Vue from 'vue';
import './plugins/axios'; //axios
import App from './App.vue';
import router from './router';
import './plugins/element.js'; //UI
import '../updateCompoents/animate.css'; //动画
import store from './plugins/vuex.js';
import { root_state } from './plugins/vuex.js';
import SqsGlobal from './plugins/SqsGlobal.js';
/* md */
import mavonEditor from '../public/md/mavon-editor';
import '../public/md/css/index.css';
import '../updateCompoents/highlight.js/styles/googlecode.css';
import '../public/md/markdown/github-markdown.min.css';
/* m3u8视频流 */
import '../updateCompoents/video.js/dist/video-js.css';
import VueWorker from 'vue-worker';
import ipc from './plugins/ipc.js';

const reader = new FileReader();
Vue.config.errorHandler = () => {};
let copy_lock = false;

try {
  const is_dev =
    window?.location?.href?.indexOf('http://localhost') !== -1 ||
    window?.location?.href?.indexOf('http://127.0.0.1') !== -1;
  window.addEventListener('error', function (event) {
    event.preventDefault();
    is_dev && console.error(event);
  });
  window.addEventListener('unhandledrejection', function (event) {
    event.preventDefault();
    is_dev && console.error(event);
  });
} catch (err) {}

// use
const EventBus = new Vue();
Vue.use(mavonEditor);
Vue.use(VueWorker);

window.Hls = require('../updateCompoents/hls.js');

//每次请求头都加上authorization
axios.interceptors.request.use(async (config) => {
  /* jsonp为music区别其他的请求，防止一些请求头造成的跨域 */
  if (config.dataType == 'jsonp') {
    return config;
  }
  config.headers.Authorization =
    'Bearer ' + window.localStorage.getItem('authorization');
  config.headers.Key = window.localStorage.getItem('key');
  return config;
});

Vue.config.productionTip = false;

Vue.prototype.$ajax = axios;
Vue.prototype.$SqsGlobal = SqsGlobal;
Vue.prototype.$EventBus = EventBus;

const listenDomChange = () => {
  // 创建MutationObserver实例
  const observer = new MutationObserver((mutationsList) => {
    // 遍历每个DOM变化
    mutationsList.forEach((mutation) => {
      // 检查变化类型是否为子节点添加
      if (mutation.type === 'childList') {
        // 获取class开头为"lang-"的code标签
        const codeTags = document.querySelectorAll('code[class^="lang-"]');
        // 遍历这些code标签，为每个标签添加复制按钮
        codeTags.forEach((codeTag) => {
          // 检查是否已经添加了复制按钮
          if (
            !codeTag.querySelector('button.copy-button-has-language') &&
            !codeTag.querySelector('button.copy-button-no-language')
          ) {
            const has_language =
              !codeTag.classList.contains('lang-') &&
              codeTag.parentNode.classList.contains('hljs');
            const parent_dom = has_language
              ? codeTag?.parentNode?.parentNode
              : codeTag?.parentNode;
            if (parent_dom) {
              parent_dom.classList.add('relative');
              // 创建复制按钮元素
              const copyButton = document.createElement('button');
              copyButton.textContent = '复制';
              // 添加按钮样式
              if (has_language) {
                copyButton.className = 'copy-button-has-language';
              } else {
                copyButton.className = 'copy-button-no-language';
              }
              // 添加鼠标移入事件
              parent_dom.addEventListener('mouseenter', () => {
                copyButton.classList.remove('fade-out');
                copyButton.classList.add('show-copy-button', 'fade-in');
              });
              // 添加鼠标移出事件
              parent_dom.addEventListener('mouseleave', () => {
                copyButton.classList.remove('fade-in');
                copyButton.classList.add('fade-out');
                copyButton.addEventListener(
                  'animationend',
                  () => {
                    if (
                      copyButton.classList.contains('show-copy-button') &&
                      !copyButton.classList.contains('fade-in')
                    ) {
                      copyButton.classList.remove('show-copy-button');
                    }
                  },
                  { once: true }
                );
              });
              // 添加点击事件
              copyButton.addEventListener('click', () => {
                // 复制code标签内容
                copyButton.textContent = '';
                const contentToCopy = codeTag.textContent;
                copyButton.textContent = '复制';
                Vue.prototype.copy(contentToCopy);
              });
              // 将按钮添加到code标签中
              codeTag.appendChild(copyButton);
            }
          }
        });
      }
    });
  });
  // 监听body元素的子节点变化
  observer.observe(document.body, { childList: true, subtree: true });
};

listenDomChange();

// 拖拽
Vue.directive('domDrag', {
  bind(el) {
    //el即为当前元素，添加可拖拽标识
    el.style.cursor = 'move';
    // 获取原有属性 ie dom.currentStyle 火狐谷歌 window.getComputedStyle(dom, null);
    const sty = el.currentStyle || window.getComputedStyle(el, null);
    el.onmousedown = (e) => {
      //获取鼠标按下位置
      const disX = e.clientX;
      const disY = e.clientY;
      // 获取当前元素的定位信息
      // 获取到的值带px 正则匹配替换
      let styL, styT;
      // 注意在ie中 第一次获取到的值为组件自带50% 移动之后赋值为px
      // +的作用是将字符串转为数字
      if (sty.left.includes('%')) {
        styL =
          +document.body.clientWidth * (+sty.left.replace(/\%/g, '') / 100);
        styT =
          +document.body.clientHeight * (+sty.top.replace(/\%/g, '') / 100);
      } else {
        styL = +sty.left.replace(/\px/g, '');
        styT = +sty.top.replace(/\px/g, '');
      }
      document.onmousemove = function (e) {
        // 通过事件委托，计算移动的距离
        const l = e.clientX - disX;
        const t = e.clientY - disY;
        // 移动当前元素
        el.style.left = `${l + styL}px`;
        el.style.top = `${t + styT}px`;
      };
      //鼠标弹起，移除相应事件
      document.onmouseup = function (e) {
        document.onmousemove = null;
        document.onmouseup = null;
      };
    };
  },
});

// 从上传文件列表中删除上传的文件
Vue.prototype.deleteOneFileHistoryFromUpList = function (file, file_list) {
  try {
    const index = file_list.indexOf(file);
    if (index !== -1) {
      file_list.splice(index, 1);
    }
  } catch (err) {}
};

// 获取CSS配置并生效
Vue.prototype.getCss = async function () {
  const { data: res } = await this.$ajax({
    method: 'post',
    url: '/User/getCss',
    portType: {
      process: '8793',
    },
  }).catch(() => {
    return;
  });
  if (res?.code == 1) {
    this.setRootCSS(res?.data);
  }
};

// 改变代码编辑器主题配色
Vue.prototype.changeCodeCSS = function (will_theme) {
  try {
    const theme_obj_list = this.$SqsGlobal.themelist;
    const theme_obj_list_len = theme_obj_list?.length;
    for (let i = 0; i < theme_obj_list_len; ++i) {
      const tem_theme_obj = theme_obj_list[i];
      if (tem_theme_obj?.value === will_theme) {
        this.setRootCSS(tem_theme_obj?.css, 'ltpp-code-ide');
        return;
      }
    }
  } catch (err) {}
};

Vue.prototype.setRootCSS = function (data = '', ltpp_css_dom_id = 'ltpp-css') {
  let style_tag = document.getElementById(ltpp_css_dom_id);
  if (!style_tag) {
    style_tag = document.createElement('style');
    style_tag.id = ltpp_css_dom_id;
    document.head?.appendChild(style_tag);
  }
  try {
    style_tag.innerHTML = `:root{${data}}`;
  } catch (e) {
    // 异常情况下删除新增的CSS配置，使用默认的配置
    style_tag.parentNode.removeChild(style_tag);
  }
};

// 随机字符串
Vue.prototype.randomString = function (length = 32) {
  let f_timestamp = new Date().getTime();
  let random_number = '';
  let characters =
    'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let characters_length = characters.length;
  for (let i = 0; i < length; i++) {
    random_number += characters.charAt(
      Math.floor(Math.random() * characters_length)
    );
  }
  let s_timestamp = new Date().getTime();
  return f_timestamp + random_number + s_timestamp;
};

// 全屏
Vue.prototype.fullscreen = function (id, fn = () => {}) {
  let element_dom = document.getElementById(id);
  if (!element_dom) {
    return;
  }
  if (
    !document.fullscreenElement &&
    !document.mozFullScreenElement &&
    !document.webkitFullscreenElement &&
    !document.msFullscreenElement
  ) {
    if (element_dom.requestFullscreen) {
      document.addEventListener('fullscreenchange', fn.call(this));
      element_dom.requestFullscreen();
    } else if (element_dom.mozRequestFullScreen) {
      document.addEventListener('mozfullscreenchange', fn.call(this));
      element_dom.mozRequestFullScreen();
    } else if (element_dom.webkitRequestFullscreen) {
      document.addEventListener('webkitfullscreenchange', fn.call(this));
      element_dom.webkitRequestFullscreen();
    } else if (element_dom.msRequestFullscreen) {
      document.addEventListener('msfullscreenchange', fn.call(this));
      element_dom.msRequestFullscreen();
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
  }
};

// 复制
Vue.prototype.copy = async function (text) {
  try {
    if (navigator.clipboard) {
      navigator.clipboard
        .writeText(text)
        .then(() => {
          this.$msg({
            type: 'success',
            message: '复制成功',
            duration: 888,
            offset: 80,
          });
        })
        .catch((err) => {
          try {
            let target = document.createElement('textarea');
            target.setAttribute('id', 'LTPPSQScopyTextID');
            target.value = text;
            document.body.appendChild(target);
            target.select();
            document.execCommand('Copy');
            this.$msg({
              type: 'success',
              message: '复制成功',
              duration: 888,
              offset: 80,
            });
            let deldom = document.getElementById('LTPPSQScopyTextID');
            deldom.parentNode.removeChild(deldom);
          } catch (err) {
            this.$msg({
              type: 'error',
              message: '复制失败',
              duration: 888,
              offset: 80,
            });
          }
        });
    } else {
      let target = document.createElement('textarea');
      target.setAttribute('id', 'LTPPSQScopyTextID');
      target.value = text;
      document.body.appendChild(target);
      target.select();
      document.execCommand('Copy');
      this.$msg({
        type: 'success',
        message: '复制成功',
        duration: 888,
        offset: 80,
      });
      let deldom = document.getElementById('LTPPSQScopyTextID');
      deldom.parentNode.removeChild(deldom);
    }
  } catch (err) {
    this.$msg({
      type: 'error',
      message: '复制失败',
      duration: 888,
      offset: 80,
    });
  }
};

Vue.prototype.getFronturl = async function () {
  let cache = window.sessionStorage.getItem('FrontUrl');
  if (cache && cache != undefined && cache != null) {
    return cache;
  }
  const { data: res } = await this.$ajax({
    method: 'post',
    url: '/Url/getFrontUrl',
    portType: {
      process: '8797',
    },
  }).catch((t) => {
    this.$msg({
      type: 'error',
      message: t,
      duration: 1600,
      offset: 80,
    });
  });
  if (res?.code == 1) {
    window.sessionStorage.setItem('FrontUrl', res?.data);
    return res?.data;
  }
  return '';
};

Vue.prototype.getBackurl = async function () {
  let cache = window.sessionStorage.getItem('linuxurl');
  if (cache && cache != undefined && cache != null) {
    return cache;
  }
  const { data: res } = await this.$ajax({
    method: 'post',
    url: '/Url/getBackUrl',
    portType: {
      process: '8797',
    },
  }).catch((t) => {
    this.$msg({
      type: 'error',
      message: t,
      duration: 1600,
      offset: 80,
    });
  });
  if (res?.code == 1) {
    window.sessionStorage.setItem('linuxurl', res?.data);
    return res?.data;
  }
  return '';
};

// 返回顶部
Vue.prototype.totop = function () {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  });
};

// 解析token
Vue.prototype.getMyId = function () {
  let token = localStorage.getItem('authorization');
  if (!token) {
    return null;
  }
  let strings = token.split('.');
  let userinfo = JSON.parse(
    decodeURIComponent(
      escape(window.atob(strings[1].replace(/-/g, '+').replace(/_/g, '/')))
    )
  );
  if (userinfo && userinfo.extend && userinfo.extend.id) {
    return userinfo.extend.id;
  }
  return null;
};

Vue.prototype.uidToString = function (msg_uid) {
  return msg_uid + msg_uid;
};

// 编码
Vue.prototype.Base64Encode = function (str, char_set) {
  if (!char_set) {
    try {
      char_set = JSON.parse(window.sessionStorage.getItem('cloud_charset'));
    } catch (err) {
      char_set = [];
    }
  }
  if (!char_set?.length) {
    return '';
  }
  str = str.toString();
  let len = str.length;
  let bin = '';
  for (let i = 0; i < len; ++i) {
    bin += str[i].charCodeAt().toString(2).padStart(24, '0');
  }
  len = bin.length;
  let base64_encode = '';
  for (let i = 0; i < len; i += 6) {
    let tem_bin = '';
    for (let j = i; j - i < 6 && j < len; ++j) {
      tem_bin += bin[j];
    }
    base64_encode += char_set[parseInt(tem_bin, 2)];
  }
  return base64_encode;
};

// 解码
Vue.prototype.Base64Decode = function (str, char_set) {
  if (!char_set) {
    try {
      char_set = JSON.parse(window.sessionStorage.getItem('cloud_charset'));
    } catch (err) {
      char_set = [];
    }
  }
  if (!char_set?.length) {
    return '';
  }
  str = str.toString();
  let bin = '';
  let len = str.length;
  for (let i = 0; i < len; ++i) {
    let tem_num = 0;
    for (let j = 0; j < char_set.length; ++j) {
      if (str[i] == char_set[j]) {
        tem_num = j;
        break;
      }
    }
    bin += tem_num.toString(2).padStart(6, '0');
  }
  let base64_decode = '';
  len = bin.length;
  for (let i = 0; i < len; i += 24) {
    let tem_bin = '';
    for (let j = i; j - i < 24 && j < len; ++j) {
      tem_bin += bin[j];
    }
    base64_decode += String.fromCharCode(parseInt(tem_bin, 2));
  }
  return base64_decode;
};

router.beforeEach((to, from, next) => {
  /* 路由发生变化修改页面title */
  if (to.meta.title) {
    document.title = to.meta.title;
  }
  next();
});

// 获取字符集
Vue.prototype.loadCloudCharset = async function (is_init = false) {
  let char_set = window.sessionStorage.getItem('cloud_charset');
  if (char_set) {
    try {
      const json_res = JSON.parse(char_set);
      return json_res;
    } catch (err) {
      window.sessionStorage.removeItem('cloud_charset');
    }
  }
  while (!char_set?.length && is_init) {
    const { data: res } = await this.$ajax({
      method: 'post',
      url: '/Cloudfile/loadCharset',
      portType: {
        process: '8795',
      },
      isNoInitRequest: true,
    }).catch((t) => {
      this.$msg({
        type: 'error',
        message: t,
        duration: 1600,
        offset: 80,
      });
      return;
    });
    if (res && res?.code && res?.code == 1) {
      char_set = res.data;
    }
    window.sessionStorage.setItem('cloud_charset', JSON.stringify(char_set));
    return char_set;
  }
  return char_set;
};

Vue.prototype.sleep = function (delay) {
  return new Promise((re) => {
    setTimeout(re, delay);
  });
};

Vue.prototype.waitDomLoad = function (id, delay) {
  let timer = null;
  return new Promise((re) => {
    try {
      timer = setInterval(() => {
        const dom = document.getElementById(id);
        if (dom) {
          re();
          clearInterval(timer);
          timer = null;
          return;
        }
      }, delay);
    } catch (err) {
      re();
      clearInterval(timer);
      timer = null;
    }
  });
};

Vue.prototype.initDevice = function () {
  const now_width = window.screen.width;
  if (store.state.now_width != now_width) {
    store.commit('updateObj', { now_width: now_width });
  }
  const is_mobile_view = now_width <= 888;
  const max_width =
    is_mobile_view && !store.state.login
      ? window.screen.width
      : ((Math.min(1920, window.screen.width) -
          store.state.default_home_to_left_right * 2) /
          100) *
        86.3;
  if (is_mobile_view && store.state.lookmusic) {
    store.commit('updateObj', {
      lookmusic: false,
    });
  } else if (!is_mobile_view && !store.state.lookmusic) {
    store.commit('updateObj', {
      lookmusic: true,
    });
  }
  if (store.state.max_width != max_width) {
    store.commit('updateObj', {
      max_width: max_width,
    });
  }
  if (
    is_mobile_view &&
    (store.state.default_home_to_left_right !== 0 ||
      store.state.default_margin_top_bottom !== 0 ||
      store.state.default_md_page_to_left_right !== 0)
  ) {
    store.commit('updateObj', {
      default_home_to_left_right: 0,
      default_margin_top_bottom: 0,
      default_md_page_to_left_right: 0,
    });
  } else if (
    !is_mobile_view &&
    (store.state.default_home_to_left_right !=
      root_state.default_home_to_left_right ||
      store.state.default_margin_top_bottom !=
        root_state.default_margin_top_bottom ||
      store.state.default_md_page_to_left_right !=
        root_state.default_md_page_to_left_right)
  ) {
    store.commit('updateObj', {
      default_home_to_left_right: root_state.default_home_to_left_right,
      default_margin_top_bottom: root_state.default_margin_top_bottom,
      default_md_page_to_left_right: root_state.default_md_page_to_left_right,
    });
  }
};

Vue.prototype.logoutRemove = function (is_force = false) {
  try {
    store.commit('reset');
    try {
      window.localStorage.removeItem('key');
      window.localStorage.removeItem('authorization');
      let storage = window.localStorage;
      for (let i = 0, len = storage.length; i < len; i++) {
        let key = storage.key(i);
        if (
          key?.indexOf('Chat') != -1 ||
          key?.indexOf('://') != -1 ||
          is_force === true
        ) {
          if (key != 'backend_network_url' || key != 'time') {
            window.localStorage.removeItem(key);
          }
        }
      }
      window.sessionStorage.clear();
      // 清空自定义系统UI
      this.setRootCSS('');
    } catch (err) {}
    EventBus.$emit('closeWs');
    if (
      router?.history?.current?.fullPath != '/login' &&
      router?.history?.current?.fullPath != '/register'
    ) {
      router.replace({
        path: '/login',
        replace: true,
      });
    }
  } catch (err) {}
};

Vue.prototype.judgeIsType = function (ShowStaticFileUrl, type_index) {
  for (const key in this.$SqsGlobal.extion_map_number) {
    if (Object.hasOwnProperty.call(this.$SqsGlobal.extion_map_number, key)) {
      const value = this.$SqsGlobal.extion_map_number[key];
      if (value == type_index && ShowStaticFileUrl.endsWith(key)) {
        return true;
      }
    }
  }
  return false;
};

Vue.prototype.judgeSystemIsWin = function () {
  const user_agent = window.navigator.userAgent.toLowerCase();
  if (user_agent.indexOf('win') != -1) {
    return true;
  }
  return false;
};

Vue.prototype.judgeSystemIsMac = function () {
  const user_agent = window.navigator.userAgent.toLowerCase();
  if (user_agent.indexOf('mac') != -1) {
    return true;
  }
  return false;
};

Vue.prototype.judgeSystemIsPhone = function () {
  const user_agent = window.navigator.userAgent.toLowerCase();
  const is_mobile =
    /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(
      user_agent
    );
  return is_mobile;
};

Vue.prototype.downloadNoUrlContent = async function (
  type = 'text/html',
  download_name = '',
  data = {}
) {
  this.$msg({
    type: 'success',
    message: '开始下载！请耐心等待！',
    duration: 1600,
    offset: 80,
  });
  let blob = new Blob([data], { type: type });
  const { data: res } = await this.$ajax({
    method: 'post',
    url: URL.createObjectURL(blob),
    responseType: 'blob',
    headers: {
      'Content-Type': 'application/json; application/octet-stream;',
    },
    portType: {
      process: '8795',
    },
    data: data,
  }).catch((t) => {
    this.$msg({
      type: 'error',
      message: t,
      duration: 1600,
      offset: 80,
    });
  });
  if (res?.code && res?.code == -1) {
    this.$msg({
      type: 'error',
      message: res?.msg,
      duration: 1600,
      offset: 80,
    });
    return res;
  }
  if (window.navigator && window.navigator.msSaveOrOpenBlob) {
    const blob = new Blob([res], {
      type: 'application/octet-stream;application/zip',
    });
    window.navigator.msSaveOrOpenBlob(blob, download_name);
  } else {
    /* 火狐谷歌的文件下载方式 */
    const blob = new Blob([res], {
      type: 'application/octet-stream;application/zip',
    });
    let url = window.URL.createObjectURL(blob);
    const link = document.createElement('a'); // 创建a标签
    link.href = url;
    link.download = download_name; // 重命名文件
    link.click();
    URL.revokeObjectURL(url); // 释放内存
  }
  this.$msg({
    type: 'success',
    message: '下载完成！',
    duration: 1600,
    offset: 80,
  });
  return res;
};

Vue.prototype.downloadFile = function (url, filename) {
  this.$msg({
    type: 'success',
    message: '开始下载！请等待！',
    duration: 1600,
    offset: 80,
  });
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url);
    xhr.responseType = 'blob';
    xhr.onload = () => {
      if (xhr.status === 200) {
        const blob = xhr.response;
        const a = document.createElement('a');
        const url = URL.createObjectURL(blob);
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
        resolve();
      } else {
        reject(new Error(`下载失败！HTTP 状态码：${xhr.status}！`));
      }
    };
    xhr.onerror = () => {
      reject(new Error('下载失败！网络异常！'));
    };
    xhr.send();
  });
};

Vue.prototype.downloadUrlContent = async function (url, data, download_name) {
  this.$msg({
    type: 'success',
    message: '开始下载！请耐心等待！',
    duration: 1600,
    offset: 80,
  });
  const { data: res } = await this.$ajax({
    method: 'post',
    url: url,
    responseType: 'blob',
    headers: {
      'Content-Type': 'application/json; application/octet-stream;',
    },
    portType: {
      process: '8795',
    },
    data: data,
  }).catch((t) => {
    this.$msg({
      type: 'error',
      message: t,
      duration: 1600,
      offset: 80,
    });
  });
  if (res?.code && res?.code == -1) {
    this.$msg({
      type: 'error',
      message: res?.msg,
      duration: 1600,
      offset: 80,
    });
    return res;
  }
  if (window.navigator && window.navigator.msSaveOrOpenBlob) {
    const blob = new Blob([res], {
      type: 'application/octet-stream;application/zip',
    });
    window.navigator.msSaveOrOpenBlob(blob, download_name);
  } else {
    /* 火狐谷歌的文件下载方式 */
    const blob = new Blob([res], {
      type: 'application/octet-stream;application/zip',
    });
    let url = window.URL.createObjectURL(blob);
    const link = document.createElement('a'); // 创建a标签
    link.href = url;
    link.download = download_name; // 重命名文件
    link.click();
    URL.revokeObjectURL(url); // 释放内存
  }
  this.$msg({
    type: 'success',
    message: '下载完成！',
    duration: 1600,
    offset: 80,
  });
  return res;
};

Vue.prototype.sendNotification = function (
  title = '通知',
  body = '',
  icon = '/logo.png'
) {
  try {
    if (!this.$store.state.open_system_notice) {
      return;
    }
    body = this.removeHtmlTags(body);
    const send = () => {
      new Notification(title, {
        body: body,
        icon: icon,
      });
    };
    if (window.Notification.permission == 'granted') {
      // 判断是否有权限
      send();
    } else if (window.Notification.permission != 'denied') {
      // 没有权限发起请求
      window.Notification.requestPermission((permission) => {
        send();
      });
    }
  } catch (err) {}
};

Vue.prototype.getFileExtensionName = function (name) {
  try {
    const arr = name.split('.');
    const len = arr.length;
    const res = arr[len - 1];
    return res;
  } catch (e) {}
  return '';
};

Vue.prototype.imgAddBase64 = async function (pos, $file, $ref_name) {
  reader.onload = (event) => {
    const base64_string = event.target.result;
    this.$refs[$ref_name].$img2Url(pos, base64_string);
  };
  reader.readAsDataURL($file);
};

Vue.prototype.imgAddRemoteUrl = async function (pos, $file, $ref_name) {
  let formdata = new FormData();
  formdata.append('file', $file);
  await this.$ajax({
    url: '/File/saveImage',
    method: 'post',
    data: formdata,
    headers: { 'Content-Type': 'multipart/form-data' },
  })
    .then((res) => {
      this.$refs[$ref_name].$img2Url(pos, res?.data.url);
    })
    .catch((t) => {
      this.$msg({
        type: 'error',
        message: t,
        duration: 1600,
        offset: 80,
      });
    });
};

Vue.prototype.imgAddMiddleware = function (pos, $file, $ref_name) {
  if (this.$store.state.image_use_remote) {
    this.imgAddRemoteUrl(pos, $file, $ref_name);
  } else {
    this.imgAddBase64(pos, $file, $ref_name);
  }
};

// 更新图片保存方式配置
Vue.prototype.changeImageSaveType = function () {
  store.commit('updateObj', {
    image_use_remote: !!!store.state.image_use_remote,
  });
  Vue.prototype.$msg({
    type: 'success',
    message: `当前图片保存方式为${
      store.state.image_use_remote ? '静态资源' : 'Base64编码'
    }`,
    duration: 3600,
    offset: 80,
  });
  if (!store.state.login) {
    return;
  }
  // 更新服务端用户配置
  axios({
    url: '/User/changeImageSaveType',
    method: 'post',
    data: {
      image_use_remote: store.state.image_use_remote,
    },
  })
    .then((res) => {})
    .catch((t) => {});
};

// 获取图片保存方式配置
Vue.prototype.getImageSaveType = function () {
  this.$ajax({
    url: '/User/getImageSaveType',
    method: 'post',
  })
    .then((res) => {
      if (res?.data?.code == 1) {
        this.$store.commit('updateObj', { image_use_remote: res?.data?.data });
      } else {
        this.$store.commit('updateObj', { image_use_remote: 0 });
      }
    })
    .catch((t) => {
      this.$store.commit('updateObj', { image_use_remote: 0 });
    });
};

// 获取系统通知配置
Vue.prototype.getSystemNoticeConfig = function () {
  this.$ajax({
    url: '/User/getSystemNoticeConfig',
    method: 'post',
  })
    .then((res) => {
      if (res?.data?.code == 1) {
        this.$store.commit('updateObj', {
          open_system_notice: res?.data?.data,
        });
      } else {
        this.$store.commit('updateObj', {
          open_system_notice: 1,
        });
      }
    })
    .catch((t) => {});
};

// 去除HTML标签
Vue.prototype.removeHtmlTags = function (html) {
  return html.replace(/<[^>]*>/g, '');
};

// 打开外部网站
Vue.prototype.openOuterUrl = function (url) {
  url && window.open(url, '_blank', 'noopener,noreferrer');
};

// 使用a标签打开网站
Vue.prototype.openUrlUseATag = function (url) {
  const link = document.createElement('a');
  try {
    link.href = url;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
  } finally {
    document.body.removeChild(link);
  }
};

/**
 * 打开视频新页面
 * @param {string} param_url
 */
Vue.prototype.openVideoUrlPage = function (param_url) {
  const obj = new URL(param_url);
  let search_obj = new URLSearchParams(obj?.search ?? '');
  let url = search_obj.get('url') ?? '';
  const res_url = url
    ? `${location?.origin ?? ''}${location.pathname ?? ''}?redirect_url=${
        encodeURI(url) ?? ''
      }`
    : param_url;
  this.copy(res_url);
  Vue.prototype.openUrlUseATag(res_url);
};

Vue.prototype.captureScreen = function () {
  // 确保浏览器支持HTML5的屏幕捕获API
  if (!navigator.mediaDevices.getDisplayMedia) {
    this.$msg({
      type: 'warning',
      message: '系统检测到当前环境不支持截屏！',
      duration: 1600,
      offset: 80,
    });
    return;
  }

  const canvas_screen_id = 'canvas_screen';
  const video_screen_id = 'video_screen';
  const a_screen_id = 'a_screen';

  // 定义视频流的约束条件，请求屏幕共享
  const constraints = {
    video: {
      width: { ideal: screen.width }, // 可以根据需要调整分辨率
      height: { ideal: screen.height },
      cursor: 'always', // 确保捕获鼠标指针
    },
    audio: false, // 不需要音频
  };

  const canvas =
    document.getElementById(canvas_screen_id) ||
    document.createElement('canvas');
  canvas.setAttribute('id', canvas_screen_id);
  const video =
    document.getElementById(video_screen_id) || document.createElement('video');
  video.setAttribute('id', video_screen_id);
  const link =
    document.getElementById(a_screen_id) || document.createElement('a');
  link.setAttribute('id', a_screen_id);

  // 创建下载链接
  const ctx = canvas.getContext('2d');

  let idx = 1;

  navigator.mediaDevices.getDisplayMedia(constraints).then(
    (stream) => {
      video.srcObject = stream;
      video.onloadedmetadata = () => {
        video.play(); // 播放视频流
        // 每秒钟获取一次截图
        const capture = () => {
          if (idx > 9 || idx < 1) {
            idx = 1;
          }
          canvas.width = video.videoWidth;
          canvas.height = video.videoHeight;
          // 绘制视频帧到Canvas
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          // 将Canvas转换为图片数据URL
          const imageData = canvas.toDataURL('image/jpeg', 0.6);
          const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
          link.href = imageData;
          link.download = `ltpp-screenshot-${timestamp}-${idx}.jpg`;
          // 模拟点击下载
          link.click();
          canvas.width = 0;
          canvas.height = 0;
          ctx.clearRect(0, 0, canvas.width, canvas.height);
        };
      };
    },
    (err) => {}
  );
};

Vue.prototype.videoScreen = async function (is_no_need_save = true) {
  const ipc_renderer_send = window.bridge?.sendListen;
  const ipc_renderer_on = window.bridge?.onListen;
  const ipc_renderer_has = window.bridge?.hasListen;
  const listen_name = 'get_video_recording';
  if (is_no_need_save) {
    if (!ipc_renderer_on || !ipc_renderer_send || !ipc_renderer_has) {
      ipc.startVideoScreen.call(this);
      return;
    }
    if (!ipc_renderer_has(listen_name)) {
      ipc_renderer_on(listen_name, (event, source) => {
        ipc.startVideoScreen.call(this, source);
      });
    }
    ipc_renderer_send(listen_name);
    return;
  }
  ipc.stopVideoScreen.call(this);
};

// 访问外部数据
Vue.prototype.fetchData = async function (url = '', func = () => {}) {
  try {
    this.$msg({
      type: 'success',
      message: '资源检测中！系统检测通过后将自动访问！',
      duration: 1600,
      offset: 80,
    });
    if (!url) {
      return;
    }
    // 获取当前页面的 URL 作为 Referer
    const referer = window.location.href;

    // 构造请求头对象
    const headers = new Headers();
    headers.append('Referer', referer);

    // 发起 Fetch 请求
    fetch(url, {
      headers: headers,
    })
      .then(async (response) => {
        let res = '';
        const reader = response?.body?.getReader();
        const text_decoder = new TextDecoder();
        while (true) {
          const { done, value } = await reader?.read();
          if (done) {
            break;
          }
          res += text_decoder?.decode(value);
        }
        this.$msg({
          type: 'success',
          message: '系统检测通过！',
          duration: 1600,
          offset: 80,
        });
        func(res);
      })
      .catch((error) => {
        this.$msg({
          type: 'error',
          message: '系统检测未通过！',
          duration: 1600,
          offset: 80,
        });
      });
  } catch (error) {
    this.$msg({
      type: 'success',
      message: '系统检测未通过！',
      duration: 1600,
      offset: 80,
    });
  }
};

/**
 * URL拼接参数
 * @param {object} query_params
 * @param {string} original_url
 * @returns {string} res
 */
Vue.prototype.appendOrOverrideQueryParam = function (
  query_params,
  original_url = window.location.href
) {
  try {
    let url = new URL(original_url);
    let params = new URLSearchParams(url.search);
    for (const [key, value] of Object.entries(query_params)) {
      if (params.has(key)) {
        params.set(key, value);
      } else {
        params.append(key, value);
      }
    }
    url.search = params.toString();
    return url.href;
  } catch (err) {}
  return original_url;
};

Vue.prototype.copyText = async (e) => {
  if (copy_lock) {
    return;
  }
  let clipboardData = e.clipboardData || window.clipboardData;
  // 如果 未复制或者未剪切，直接 return
  if (!clipboardData) {
    return;
  }
  const text = window.getSelection().toString();
  if (!text) {
    return;
  }
  copy_lock = true;
  e.preventDefault();
  Vue.prototype.copy(text.trim());

  setTimeout(() => {
    copy_lock = false;
  }, 0);
};

new Vue({
  router,
  store,
  render: (h) => h(App),
}).$mount('#LTPP');

export default router;
