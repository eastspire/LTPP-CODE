/*
 * @Author: wmzn-ltpp 1491579574@qq.com
 * @Date: 2023-08-07 18:43:57
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-08 22:06:49
 * @FilePath: \LTPP-CODE\Frontend\src\main.js
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2024 by SQS, All Rights Reserved. 
 */
import Vue from 'vue'
import './plugins/axios' //axios
import App from './App.vue'
import router from './router'
import './plugins/element.js' //UI
import '../updateCompoents/animate.css' //动画
import store from './plugins/vuex.js'
import SqsGlobal from './plugins/SqsGlobal.js'
/* md */
import mavonEditor from '../public/md/mavon-editor';
import '../public/md/css/index.css';
import '../updateCompoents/highlight.js/styles/googlecode.css';
import "../public/md/markdown/github-markdown.min.css";
/* m3u8视频流 */
import "../updateCompoents/video.js/dist/video-js.css"
import VueWorker from 'vue-worker';

Vue.config.errorHandler = () => { }

try {
    const is_dev = window?.location?.href?.indexOf('http://localhost') !== -1;
    window.addEventListener('error', function (error) {
        error.preventDefault();
        is_dev && console.log(error);
    });
    window.addEventListener('unhandledrejection', function (error) {
        error.preventDefault();
        is_dev && console.log(error);
    });
} catch (err) { }

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
    config.headers.Authorization = "Bearer " + window.localStorage.getItem('authorization');
    config.headers.Key = window.localStorage.getItem('key');
    return config;
});

Vue.config.productionTip = false;

Vue.prototype.$ajax = axios;
Vue.prototype.$SqsGlobal = SqsGlobal;
Vue.prototype.$EventBus = EventBus;

// 拖拽
Vue.directive('domDrag', {
    bind(el) {
        //el即为当前元素，添加可拖拽标识
        el.style.cursor = 'move'
        // 获取原有属性 ie dom.currentStyle 火狐谷歌 window.getComputedStyle(dom, null);
        const sty = el.currentStyle || window.getComputedStyle(el, null)
        el.onmousedown = (e) => {
            //获取鼠标按下位置
            const disX = e.clientX
            const disY = e.clientY
            // 获取当前元素的定位信息
            // 获取到的值带px 正则匹配替换
            let styL, styT
            // 注意在ie中 第一次获取到的值为组件自带50% 移动之后赋值为px
            // +的作用是将字符串转为数字
            if (sty.left.includes('%')) {
                styL = +document.body.clientWidth * (+sty.left.replace(/\%/g, '') / 100)
                styT = +document.body.clientHeight * (+sty.top.replace(/\%/g, '') / 100)
            } else {
                styL = +sty.left.replace(/\px/g, '')
                styT = +sty.top.replace(/\px/g, '')
            }
            document.onmousemove = function (e) {
                // 通过事件委托，计算移动的距离
                const l = e.clientX - disX
                const t = e.clientY - disY
                // 移动当前元素
                el.style.left = `${l + styL}px`
                el.style.top = `${t + styT}px`
            }
            //鼠标弹起，移除相应事件
            document.onmouseup = function (e) {
                document.onmousemove = null
                document.onmouseup = null
            }
        }
    }
});

// 从上传文件列表中删除上传的文件
Vue.prototype.deleteOneFileHistoryFromUpList = function (file, file_list) {
    try {
        const index = file_list.indexOf(file);
        if (index !== -1) {
            file_list.splice(index, 1);
        }
    } catch (err) {
    }
}

// 获取CSS配置并生效
Vue.prototype.getCss = async function () {
    const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/getCss",
        portType: {
            process: "8793",
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
                this.setRootCSS(tem_theme_obj?.css, "ltpp-code-ide");
                return;
            }
        }
    } catch (err) { }
};

Vue.prototype.setRootCSS = function (data = '', ltpp_css_dom_id = 'ltpp-css') {
    let style_tag = document.getElementById(ltpp_css_dom_id);
    if (!style_tag) {
        style_tag = document.createElement("style");
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
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let characters_length = characters.length;
    for (let i = 0; i < length; i++) {
        random_number += characters.charAt(Math.floor(Math.random() * characters_length));
    }
    let s_timestamp = new Date().getTime();
    return f_timestamp + random_number + s_timestamp;
};

// 全屏
Vue.prototype.fullscreen = function (id, fn = () => { }) {
    let element_dom = document.getElementById(id);
    if (!element_dom) {
        return;
    }
    if (!document.fullscreenElement && !document.mozFullScreenElement &&
        !document.webkitFullscreenElement && !document.msFullscreenElement) {
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
            navigator.clipboard.writeText(text).then(() => {
                this.$msg({
                    type: "success",
                    message: "已复制到剪贴板",
                    duration: 3600,
                    offset: 80,
                });
            }).catch(err => {
                try {
                    let target = document.createElement("textarea");
                    target.setAttribute("id", "LTPPSQScopyTextID");
                    target.value = text;
                    document.body.appendChild(target);
                    target.select();
                    document.execCommand("Copy");
                    this.$msg({
                        type: "success",
                        message: "已复制到剪贴板",
                        duration: 3600,
                        offset: 80,
                    });
                    let deldom = document.getElementById("LTPPSQScopyTextID");
                    deldom.parentNode.removeChild(deldom);
                } catch (err) {
                    this.$msg({
                        type: "error",
                        message: "复制失败",
                        duration: 1800,
                        offset: 80,
                    });
                }
            });
        } else {
            let target = document.createElement("textarea");
            target.setAttribute("id", "LTPPSQScopyTextID");
            target.value = text;
            document.body.appendChild(target);
            target.select();
            document.execCommand("Copy");
            this.$msg({
                type: "success",
                message: "已复制到剪贴板",
                duration: 3600,
                offset: 80,
            });
            let deldom = document.getElementById("LTPPSQScopyTextID");
            deldom.parentNode.removeChild(deldom);
        }
    } catch (err) {
        this.$msg({
            type: "error",
            message: "复制失败",
            duration: 1800,
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
        method: "post",
        url: "/Url/getFrontUrl",
        portType: {
            process: "8797",
        },
    }).catch((t) => {
        this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
        });
    });
    if (res?.code == 1) {
        window.sessionStorage.setItem("FrontUrl", res?.data);
        return res?.data;
    }
    return '';
};

Vue.prototype.getBackurl = async function () {
    let cache = window.sessionStorage.getItem("linuxurl");
    if (cache && cache != undefined && cache != null) {
        return cache;
    }
    const { data: res } = await this.$ajax({
        method: "post",
        url: "/Url/getBackUrl",
        portType: {
            process: "8797",
        },
    }).catch((t) => {
        this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
        });
    });
    if (res?.code == 1) {
        window.sessionStorage.setItem("linuxurl", res?.data);
        return res?.data;
    }
    return '';
};

// 返回顶部
Vue.prototype.totop = function () {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

// 解析token
Vue.prototype.getMyId = function () {
    let token = localStorage.getItem("authorization");
    if (!token) {
        return null;
    }
    let strings = token.split(".");
    let userinfo = JSON.parse(
        decodeURIComponent(escape(window.atob(strings[1].replace(/-/g, "+").replace(/_/g, "/"))))
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
            method: "post",
            url: "/Cloudfile/loadCharset",
            portType: {
                process: "8795",
            },
            isNoInitRequest: true
        }).catch((t) => {
            this.$msg({
                type: "error",
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
        store.commit("updateObj", { now_width: now_width });
    }
    const max_width = ((Math.min(1920, window.screen.width) - store.state.default_home_to_left_right * 2) / 100) * 86.3;
    if (store.state.max_width != max_width) {
        store.commit("updateObj", {
            max_width: max_width,
        });
    }
};

Vue.prototype.logoutRemove = function (is_force = false) {
    try {
        store.commit("reset");
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
                    window.localStorage.removeItem(key);
                }
            }
            window.sessionStorage.clear();
        } catch (err) {
        }
        EventBus.$emit('closeWs');
        if (
            router?.history?.current?.fullPath != '/login' &&
            router?.history?.current?.fullPath != '/register'
        ) {
            router.replace({
                path: "/login",
                replace: true,
            });
        }
    } catch (err) {
    }
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
    const is_mobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(user_agent);
    return is_mobile;
};

Vue.prototype.downloadNoUrlContent = async function (type = 'text/html', data, download_name = '') {
    this.$msg({
        type: "success",
        message: '开始下载！请耐心等待！',
        duration: 1600,
        offset: 80,
    });
    let blob = new Blob([data], { type: type });
    const { data: res } = await this.$ajax({
        method: "post",
        url: URL.createObjectURL(blob),
        responseType: "blob",
        headers: {
            "Content-Type": "application/json; application/octet-stream;",
        },
        portType: {
            process: "8795",
        },
        data: data
    }).catch((t) => {
        this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
        });
    });
    if (res?.code && res?.code == -1) {
        this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
        });
        return res;
    }
    if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        const blob = new Blob([res], {
            type: "application/octet-stream;application/zip",
        });
        window.navigator.msSaveOrOpenBlob(blob, download_name);
    } else {
        /* 火狐谷歌的文件下载方式 */
        const blob = new Blob([res], {
            type: "application/octet-stream;application/zip",
        });
        let url = window.URL.createObjectURL(blob);
        const link = document.createElement("a"); // 创建a标签
        link.href = url;
        link.download = download_name; // 重命名文件
        link.click();
        URL.revokeObjectURL(url); // 释放内存
    }
    this.$msg({
        type: "success",
        message: '下载完成！',
        duration: 1600,
        offset: 80,
    });
    return res;
};

Vue.prototype.downloadFile = function (url, filename) {
    this.$msg({
        type: "success",
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
        type: "success",
        message: '开始下载！请耐心等待！',
        duration: 1600,
        offset: 80,
    });
    const { data: res } = await this.$ajax({
        method: "post",
        url: url,
        responseType: "blob",
        headers: {
            "Content-Type": "application/json; application/octet-stream;",
        },
        portType: {
            process: "8795",
        },
        data: data
    }).catch((t) => {
        this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
        });
    });
    if (res?.code && res?.code == -1) {
        this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
        });
        return res;
    }
    if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        const blob = new Blob([res], {
            type: "application/octet-stream;application/zip",
        });
        window.navigator.msSaveOrOpenBlob(blob, download_name);
    } else {
        /* 火狐谷歌的文件下载方式 */
        const blob = new Blob([res], {
            type: "application/octet-stream;application/zip",
        });
        let url = window.URL.createObjectURL(blob);
        const link = document.createElement("a"); // 创建a标签
        link.href = url;
        link.download = download_name; // 重命名文件
        link.click();
        URL.revokeObjectURL(url); // 释放内存
    }
    this.$msg({
        type: "success",
        message: '下载完成！',
        duration: 1600,
        offset: 80,
    });
    return res;
};

new Vue({
    router,
    store,
    render: h => h(App),
}).$mount('#app');

export default router;