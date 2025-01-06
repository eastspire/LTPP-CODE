<template>
  <div id="LTPP" @contextmenu.prevent="" class="no-select;">
    <div class="default-bk"></div>
    <router-view @contextmenu.prevent=""></router-view>
  </div>
</template>

<script>
import { compareVersion } from './utils/helper';
window.onload = () => {
  document.addEventListener('touchstart', function (event) {
    if (event.touches.length > 1) {
      event.preventDefault();
    }
  });
  document.addEventListener('gesturestart', function (event) {
    event.preventDefault();
  });
};
let err_times = 0;
let max_err_times = 1;

try {
  // 解决ResizeObserver报错
  const debounce = (callback, delay) => {
    let tid;
    return function (...args) {
      const ctx = self;
      tid && clearTimeout(tid);
      tid = setTimeout(() => {
        callback.apply(ctx, args);
      }, delay);
    };
  };
  const _ = window.ResizeObserver;
  window.ResizeObserver = class ResizeObserver extends _ {
    constructor(callback) {
      callback = debounce(callback, 20);
      super(callback);
    }
  };
} catch (err) {}

export default {
  name: 'LTPP',
  data() {
    return {
      version: '2.15.0',
      get_version_lock: false,
    };
  },
  beforeCreate() {
    this.initDevice();
    let authorization = window.localStorage.getItem('authorization');
    let key = window.localStorage.getItem('key');
    this.$store.commit('updateObj', { login: authorization && key });
  },
  async mounted() {
    try {
      this.init();
      let key = 'time';
      const max_time = 86400;
      const last = window.localStorage.getItem(key);
      const now = new Date().getTime();
      if (!last || (parseInt(now) - parseInt(last)) / 1000 > max_time) {
        this.$notice({
          title: '系统资源检查',
          dangerouslyUseHTMLString: true,
          message: '系统资源更新中',
          duration: 1600,
          offset: 80,
        });
        try {
          window.localStorage.setItem(key, now);
        } catch (err) {}
        setTimeout(() => {
          window.location.reload(true);
        }, 360);
        return;
      }
      try {
        window.localStorage.setItem(key, now);
      } catch (err) {}
      setInterval(() => {
        this.getVersion();
      }, 6000);
      this.getVersion();
      this.getImageSaveType();
      this.getSystemNoticeConfig();
    } catch (err) {}
  },
  methods: {
    init() {
      this.$notice({
        title: '系统资源检查',
        dangerouslyUseHTMLString: true,
        message: '系统资源检查完成',
        duration: 1600,
        offset: 80,
      });
      let backend_network_url =
        window.localStorage.getItem('backend_network_url') ||
        this.$store.state.default_backend_network_url;
      if (backend_network_url) {
        this.$store.commit('updateObj', {
          backend_network_url: backend_network_url,
        });
      } else {
        try {
          window.localStorage.setItem(
            'backend_network_url',
            backend_network_url
          );
        } catch (err) {}
        this.$store.commit('updateObj', {
          backend_network_url: backend_network_url,
        });
      }
      setTimeout(() => {
        this.$notice({
          title: '当前环境',
          dangerouslyUseHTMLString: false,
          message:
            this.$store.state.backend_network_url ==
            this.$store.state.default_backend_network_url
              ? '官方环境'
              : '代理环境',
          duration: 3600,
          offset: 80,
        });
      }, 1000);
    },
    updateSeverError(server_error = false) {
      this.$store.commit('updateObj', { server_error: server_error });
    },
    async getVersion() {
      if (this.get_version_lock) {
        return;
      }
      this.get_version_lock = true;
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Version/getVersion',
        portType: {
          process: '8793',
        },
      }).catch(() => {
        ++err_times;
        if (err_times > max_err_times) {
          this.updateSeverError(true);
          this.get_version_lock = false;
          if (this.$route.path != '/maintenance') {
            this.$router.replace({
              path: '/maintenance',
              replace: true,
            });
          }
        }
        return;
      });
      this.get_version_lock = false;
      if (res?.code == 1) {
        err_times = 0;
        this.updateSeverError(false);
        if (this.$route.path === '/maintenance') {
          this.$router.replace({
            path: '/homelist',
            replace: true,
          });
          return;
        }
        // 版本过低
        if (compareVersion(this.version, res.version) < 0) {
          this.$notice({
            title: '发现新版本！',
            dangerouslyUseHTMLString: true,
            message: '系统自动更新中',
            duration: 1600,
            offset: 80,
          });
          setTimeout(() => {
            window.location.reload(true);
          }, 360);
        }
      } else if (++err_times > max_err_times) {
        this.updateSeverError(true);
        this.$route.path != '/maintenance' &&
          this.$router.replace({
            path: '/maintenance',
            replace: true,
          });
      }
    },
  },
};
</script>

<style lang="less">
/**
// h1颜色
--ltpp-h1-color: #fa278e;
// h2颜色
--ltpp-h2-color: #21e016;
// h3颜色
--ltpp-h3-color: #00f7ff;
// h4颜色
--ltpp-h4-color: #00bdff;
// h5颜色
--ltpp-h5-color: #ffbb00;
// h6颜色
--ltpp-h6-color: #e06c75;
// 主题背景色
--ltpp-main-bk-color: 117, 63, 178;
// 主题色
--ltpp-main-color: #e493d0;
// 主题浅色
--ltpp-light-color: 228, 147, 208;
// 代码背景色
--ltpp-code-bk-color: 40, 44, 52;
// 主题文字颜色
--ltpp-main-text-color: #f5f7fa;
// 顶部滚动条颜色
--ltpp-top-scroll-color: #409eff;
// blockquote颜色
--ltpp-blockquote-color: 0, 162, 60;
// blockquote背景透明度
--ltpp-blockquote-opacity: 0.18;
// 滚动条颜色
--ltpp-scroll-color: 117, 63, 178;
// 阴影宽度
--ltpp-shadow-border-width: 1px;
// 弹出框悬浮文字颜色
--ltpp-hover-text-color: rgba(245, 247, 250, 0.66);
// 组件内容盒子背景透明度
--ltpp-center-box-bk-opacity: 0.36;
// 代码背景透明度
--ltpp-code-bk-color-opacity: 1;
// 组件内容盒子颜色
--ltpp-box-text-color: #ffffffe6;
// 表格背景透明度
--ltpp-list-box-bk-opacity: 0.36;
// 阴影色
--ltpp-shadow-color: --ltpp-shadow-color: rgba(
  var(--ltpp-main-bk-color),
  var(--ltpp-center-box-bk-opacity)
);
// 弹出框背景悬浮颜色
--ltpp-hover-bk-color: rgba(var(--ltpp-light-color), 0.36);
// 对话框背景色
--ltpp-dialog-bk-color: 117, 63, 178;
// 对话框背景色透明度
--ltpp-dialog-bk-opacity: 0.86;
// 标题文字颜色
--ltpp-title-color: #ffdd00;
*/
:root {
  --ltpp-h1-color: #fa278e;
  --ltpp-h2-color: #21e016;
  --ltpp-h3-color: #00f7ff;
  --ltpp-h4-color: #00bdff;
  --ltpp-h5-color: #ffbb00;
  --ltpp-h6-color: #e06c75;
  --ltpp-main-bk-color: 117, 63, 178;
  --ltpp-main-color: #e493d0;
  --ltpp-light-color: 228, 147, 208;
  --ltpp-code-bk-color: 40, 44, 52;
  --ltpp-main-text-color: #f5f7fa;
  --ltpp-top-scroll-color: #409eff;
  --ltpp-blockquote-color: 0, 162, 60;
  --ltpp-blockquote-opacity: 0.18;
  --ltpp-scroll-color: 117, 63, 178;
  --ltpp-shadow-border-width: 1px;
  --ltpp-hover-text-color: rgba(245, 247, 250, 0.66);
  --ltpp-center-box-bk-opacity: 0.36;
  --ltpp-code-bk-color-opacity: 1;
  --ltpp-box-text-color: #ffffffe6;
  --ltpp-list-box-bk-opacity: 0.36;
  --ltpp-shadow-color: rgba(
    var(--ltpp-main-bk-color),
    var(--ltpp-center-box-bk-opacity)
  );
  --ltpp-hover-bk-color: rgba(var(--ltpp-light-color), 0.36);
  --ltpp-dialog-bk-color: 117, 63, 178;
  --ltpp-dialog-bk-opacity: 0.86;
  --ltpp-title-color: #ffdd00;
}

* {
  padding: 0;
  margin: 0;
  -webkit-touch-callout: none !important;
  -webkit-user-select: none !important;
  -khtml-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
  user-select: none !important;
}

@keyframes appear {
  from {
    opacity: 0;
    scale: 0.78;
  }

  to {
    opacity: 1;
    scale: 1;
  }
}

.appear {
  animation: appear linear;
  animation-timeline: view();
  animation-range: entry 0;
}

.ltpp-list-box {
  background-color: rgba(
    var(--ltpp-main-bk-color),
    var(--ltpp-list-box-bk-opacity)
  );
  border: 0px solid transparent;
  color: var(--ltpp-main-text-color);
  border-width: 0rem;
  height: auto;
  width: 100%;
}

.mtk1 {
  color: var(--mtk1) !important;
}

.mtk5 {
  color: var(--mtk5) !important;
  font-weight: bold !important;
}

.mtk6 {
  color: var(--mtk6) !important;
  font-weight: bold !important;
}

.mtk7 {
  color: var(--mtk7) !important;
}

.mtk8 {
  color: var(--mtk8) !important;
  font-weight: bold !important;
}

.mtk9 {
  color: var(--mtk9) !important;
  font-weight: bold !important;
}

.mtk14 {
  color: var(--mtk14) !important;
  font-weight: bold !important;
}

.mtk20 {
  color: var(--mtk20) !important;
  font-weight: bold !important;
}

.mtk22 {
  color: var(--mtk22) !important;
  font-weight: bold !important;
}

.mtk23 {
  color: var(--mtk23) !important;
  font-weight: bold !important;
}

.monaco-editor.showUnused .squiggly-inline-unnecessary {
  opacity: 0.68 !important;
}

blockquote {
  margin: 0.36rem 0rem !important;
  color: #ffffff !important;
  font-weight: 400;
  min-height: 2.909rem !important;
  padding: 0.66rem !important;
  background-color: rgba(
    var(--ltpp-blockquote-color),
    var(--ltpp-blockquote-opacity)
  ) !important;
  border-left: 0.58rem solid rgb(var(--ltpp-blockquote-color)) !important;
}

.main-center-box-content {
  color: var(--ltpp-box-text-color) !important;
  border-width: 0rem !important;
  width: 100% !important;
  margin-left: auto !important;
  margin-right: auto !important;
  background-color: rgba(
    var(--ltpp-main-bk-color),
    var(--ltpp-center-box-bk-opacity)
  ) !important;
  border-color: rgba(
    var(--ltpp-light-color),
    var(--ltpp-center-box-bk-opacity)
  ) !important;
}

.el-input-group--prepend,
.el-input__inner,
.el-input-group__append {
  border: 0px solid transparent !important;
  color: var(--ltpp-main-text-color) !important;
  border-top-left-radius: 0 !important;
  border-bottom-left-radius: 0 !important;
  border-top-right-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
}

.el-icon-full-screen {
  background-color: var(--ltpp-main-color) !important;
  border: 0px solid transparent;
  color: var(--ltpp-main-text-color) !important;
}

hr {
  border-top: 3px dotted rgb(171, 178, 191);
  border-bottom: none;
}

.v-note-show pre {
  min-height: 2.97201rem !important;
}

.el-notification__content pre {
  color: var(--ltpp-main-text-color);
}

pre {
  min-height: 1rem !important;
  overflow: auto;
  font-family: Consolas, Monaco, DejaVu Sans Mono, monospace !important;
  display: block !important;
  overflow-x: auto !important;
  font-size: 1.06rem !important;
  line-height: 1.56 !important;
  border-radius: 0rem !important;
  background-color: rgba(
    var(--ltpp-code-bk-color),
    var(--ltpp-code-bk-color-opacity)
  ) !important;
  border-left: 0.58rem solid rgb(var(--ltpp-main-bk-color)) !important;
  padding: 0.66rem !important;
  margin: 0.36rem 0rem !important;
}

.comment-pre {
  white-space: pre-wrap !important;
  overflow-wrap: break-word !important;
  margin-right: 1rem !important;
  background-color: transparent !important;
  color: var(--ltpp-box-text-color);
  padding: 0.36rem 1rem !important;
  border: 1px solid
    rgba(var(--ltpp-light-color), var(--ltpp-center-box-bk-opacity)) !important;
  &:extend(.can-select);
}

.relative {
  position: relative;
}

.copy-button-has-language {
  position: absolute;
  right: 0.78rem;
  top: 1.56rem;
  transform: translateY(-50%);
  border: 0px;
  border-radius: 0.26rem;
  padding: 0.16rem 0.36rem;
  cursor: pointer;
  font-size: 1rem;
  display: none;
  color: rgba(var(--ltpp-light-color), 1) !important;
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  &:extend(.no-select);
}

.copy-button-no-language {
  &:extend(.copy-button-has-language);
  top: 1.5rem;
}

.show-copy-button {
  display: inline-block;
}

.fade-in {
  animation: fadeIn 0.36s ease-in-out;
}

.fade-out {
  animation: fadeOut 0.36s ease-in-out;
}

@keyframes fadeIn {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

@keyframes fadeOut {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

code.hljs {
  padding: 3px 5px;
}

.hljs {
  background: #1e1e1e !important;
  color: #21e016 !important;
}

.hljs-comment {
  color: #f6ff00 !important;
}

.hljs-punctuation,
.hljs-tag {
  color: #ffbb00 !important;
}

.hljs-property {
  color: rgb(97, 175, 239) !important;
}

.hljs-attr,
.hljs-name {
  color: rgb(224, 108, 117) !important;
}

.hljs-attribute,
.hljs-doctag,
.hljs-keyword,
.hljs-meta.hljs-keyword,
.hljs-name,
.hljs-selector-tag {
  font-weight: bold !important;
}

.hljs-deletion,
.hljs-number,
.hljs-quote,
.hljs-selector-class,
.hljs-selector-id,
.hljs-string,
.hljs-template-tag,
.hljs-type {
  color: #ffbb00 !important;
  font-weight: bold !important;
}

.hljs-section,
.hljs-title {
  color: #ffbb00 !important;
  font-weight: bold !important;
}

.hljs-link,
.hljs-operator,
.hljs-regexp,
.hljs-selector-attr,
.hljs-selector-pseudo,
.hljs-symbol,
.hljs-template-variable,
.hljs-variable {
  color: #ff9070 !important;
}

.hljs-literal {
  color: #ffbb00 !important;
}

.hljs-addition,
.hljs-built_in,
.hljs-bullet,
.hljs-code {
  color: #3dc9b0 !important;
  font-weight: bold !important;
}

.hljs-meta {
  color: #ffbb00 !important;
  font-weight: bold !important;
}

.hljs-meta.hljs-string {
  color: #f6ff00 !important;
}

.hljs-emphasis {
  font-style: italic !important;
}

.hljs-strong {
  font-weight: bold !important;
}

.hljs-keyword {
  color: #fa278e !important;
}

.hljs-doctag {
  color: #f6ff00 !important;
}

.hljs-params {
  color: #ffbb00 !important;
}

span.hljs-function,
.hljs-subst {
  color: #ffbb00 !important;
}

span.hljs-string {
  font-weight: bold;
}

.el-menu {
  display: flex !important;
  flex-direction: column !important;
  align-content: center !important;
  padding: 0 !important;
}

.el-submenu__title,
.el-menu-item,
.el-tooltip {
  padding-left: 12.555px !important;
}

.el-submenu__title,
.el-menu-item {
  i {
    color: var(--ltpp-main-color) !important;
  }
  background-color: transparent !important;
}

.el-menu--collapse {
  width: 50px !important;
}

.can-select,
.can-select * {
  -webkit-user-select: text !important;
  -moz-user-select: text !important;
  -ms-user-select: text !important;
  user-select: text !important;
}

.shadow {
  box-shadow: 0px var(--ltpp-shadow-border-width)
      var(--ltpp-shadow-border-width) var(--ltpp-shadow-color),
    var(--ltpp-shadow-border-width) 0px var(--ltpp-shadow-border-width)
      var(--ltpp-shadow-color),
    calc(var(--ltpp-shadow-border-width) * -1) 0px
      var(--ltpp-shadow-border-width) var(--ltpp-shadow-color),
    0px calc(var(--ltpp-shadow-border-width) * -1)
      var(--ltpp-shadow-border-width) var(--ltpp-shadow-color);
}

video:focus {
  outline: none !important;
}

.el-progress-bar__inner {
  transition: width 0s ease !important;
}

.home-default-bk {
  position: fixed;
  background-size: cover;
  background-position: center center;
  background-repeat: no-repeat;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  height: 100%;
  width: 100%;
  background-color: transparent !important;
  z-index: -1000000;
}

.default-bk {
  position: fixed;
  background-position: center center;
  background-repeat: no-repeat;
  height: 100vh;
  width: 100vw;
  margin: 0;
  z-index: -1000000;
  background-color: var(--ltpp-main-color);
  background-image: radial-gradient(
      closest-side,
      rgba(235, 105, 78, 1),
      rgba(235, 105, 78, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(243, 11, 164, 0.36),
      rgba(243, 11, 164, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(254, 234, 131, 1),
      rgba(254, 234, 131, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(170, 142, 245, 1),
      rgba(170, 142, 245, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(248, 192, 147, 1),
      rgba(248, 192, 147, 0)
    );
  background-size: 130vmax 130vmax, 80vmax 80vmax, 90vmax 90vmax,
    110vmax 110vmax, 90vmax 90vmax;

  background-position: -80vmax -80vmax, 60vmax -30vmax, 10vmax 10vmax,
    -30vmax -10vmax, 50vmax 50vmax;

  animation: 4s defaultbkmove linear infinite;
}

@keyframes defaultbkmove {
  0%,
  100% {
    background-size: 130vmax 130vmax, 80vmax 80vmax, 90vmax 90vmax,
      110vmax 110vmax, 90vmax 90vmax;
    background-position: -80vmax -80vmax, 60vmax -30vmax, 10vmax 10vmax,
      -30vmax -10vmax, 50vmax 50vmax;
  }

  25% {
    background-size: 100vmax 100vmax, 90vmax 90vmax, 100vmax 100vmax,
      90vmax 90vmax, 60vmax 60vmax;
    background-position: -60vmax -60vmax, 50vmax -40vmax, 0vmax 10vmax,
      -40vmax -20vmax, 40vmax 40vmax;
  }
  50% {
    background-size: 90vmax 90vmax, 100vmax 100vmax, 80vmax 80vmax,
      90vmax 90vmax, 60vmax 60vmax;

    background-position: -70vmax -70vmax, 40vmax -40vmax, 0vmax 10vmax,
      -50vmax -30vmax, 30vmax 30vmax;
  }
  75% {
    background-size: 80vmax 80vmax, 70vmax 70vmax, 80vmax 80vmax, 70vmax 70vmax,
      50vmax 50vmax;

    background-position: -60vmax -60vmax, 60vmax -30vmax, 10vmax 10vmax,
      -40vmax -40vmax, 50vmax 50vmax;
  }
}

.video-bk {
  position: fixed;
  background-size: cover;
  background-position: center center;
  background-repeat: no-repeat;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  height: 100%;
  width: auto;
  background-color: transparent !important;
  z-index: -100000;
}

.el-progress-bar__outer {
  background-color: rgba(248, 249, 250, 0.1) !important;
}

.el-progress-bar__innerText {
  display: none !important;
}

.el-drawer,
.el-drawer__header {
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
}

.is-active {
  border: 0rem !important;
  font-size: 1.06rem !important;
  border-color: transparent !important;
  color: deepskyblue !important;
  background-color: transparent !important;
}

.no-select,
.el-menu--horizontal,
.el-menu-item {
  border: 0rem !important;
  border-color: transparent !important;
  font-size: 1rem !important;
  z-index: 1000006 !important;
}

.el-submenu__title:hover,
.el-menu-item:hover {
  i {
    color: var(--ltpp-hover-text-color) !important;
  }
  background-color: var(--ltpp-hover-bk-color) !important;
  z-index: 1000006 !important;
}

.el-tabs__item,
.is-top {
  border: 0rem !important;
  font-size: 1.06rem !important;
  border-color: transparent !important;
  background-color: rgba(53, 61, 68, 1) !important;
}

.el-tabs,
.el-tabs__nav-scroll,
.el-tabs__content {
  border-radius: 0;
  border: 0rem !important;
  border-color: transparent !important;
  background-color: rgba(53, 61, 68, 0.6) !important;
}

[v-cloak] {
  display: none;
}

.el-drawer__wrapper,
.el-tooltip__popper {
  z-index: 1000007 !important;
}

.el-picker-panel__body-wrapper,
.el-picker-panel__footer {
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
}

.el-upload__tip,
.el-upload__text,
.el-upload-dragger {
  background-color: transparent !important;
}

.el-input__count-inner {
  background-color: transparent !important;
}

.el-autocomplete-suggestion,
.el-popper,
.el-popper:hover li,
.el-autocomplete-suggestion:hover li {
  background-color: rgba(var(--ltpp-light-color), 0) !important;
  border-width: 0 !important;
}

.el-table {
  will-change: transform !important;
}

.footer {
  font-size: 1rem;
  color: #c0c4cc;
  font-weight: bold;
}

.bottomIcpLink {
  width: 100vw;
  position: absolute;
  bottom: 0.88rem;
  display: flex;
  justify-content: center;
}

.link {
  text-decoration: none;
  font-size: 1.06rem;
  color: #c0c4cc;
}

a:hover {
  color: deepskyblue;
}

a:active {
  color: deeppink;
}

.pulse-enter-active:hover {
  transform: scale(1.022);
  cursor: pointer;
  -webkit-transition: all 0.666s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.666s ease !important;
  /* Firefox */
  -o-transition: all 0.666s ease !important;
  /* Opera */
  -ms-transition: all 0.666s ease !important;
  /* IE 9 */
  transition: all 0.666s ease !important;
}

/**
动画
*/
.animate {
  -webkit-transition: all 0.666s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.666s ease !important;
  /* Firefox */
  -o-transition: all 0.666s ease !important;
  /* Opera */
  -ms-transition: all 0.666s ease !important;
  /* IE 9 */
  transition: all 0.666s ease !important;
}

.sure,
.el-time-panel__btn.confirm {
  background-color: deepskyblue !important;
  border-width: 0 !important;
  font-weight: bold;
}

.cancel,
.el-time-panel__btn.cancel {
  background-color: #67c23a !important;
  border-width: 0 !important;
  font-weight: bold;
}

.el-time-spinner__item.active:not(.disabled) {
  color: red !important;
}

.el-scrollbar__wrap,
.el-scrollbar__bar .is-horizontal,
.el-time-panel__footer {
  background-color: var(--ltpp-main-color) !important;
}

.next-month,
.prev-month {
  color: rgba(248, 249, 250, 0.36) !important;
}

.video-box {
  position: relative;
}

.video-box .dec {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 50%;
  left: 2%;
  transform: translateY(-50%);
}

.video-box .inc {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 50%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .love {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: deeppink;
  top: 30%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .no-love {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 30%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .fabulous {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: deeppink;
  top: 20%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .no-fabulous {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 20%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .download {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 40%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .comment {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 60%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .share {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 70%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .open {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 80%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box span:hover {
  color: deeppink;
  font-size: 2.36rem;
  animation-name: txt-to-big;
  animation-duration: 0.6s;
}

@keyframes txt-to-big {
  0% {
    font-size: 2rem;
  }
  100% {
    font-size: 2.36rem;
  }
}

.input,
.cancel,
.el-switch__label,
.el-dialog__headerbtn,
.el-dialog__close,
.el-icon-full-screen,
.el-time-panel__btn.cancel,
.sure,
.el-time-panel__btn.confirm,
.el-upload__tip,
.el-upload__text,
.el-upload-dragger,
.el-picker-panel__body-wrapper,
.el-picker-panel__footer,
.el-tabs,
.el-tabs__nav-scroll,
.el-tabs__content,
.el-drawer,
.el-drawer__header,
.el-tooltip__popper,
.el-select,
.el-input,
.el-input__inner,
.op-btn.sure,
.op-btn.cancel,
.el-form-item__label,
.el-carousel__arrow,
.el-carousel__arrow--right,
.btn-prev,
.btn-next,
.el-input__icon::before,
.add-image-link input::placeholder,
.el-textarea__inner::placeholder,
.el-input__inner::placeholder,
.el-range-input::placeholder,
.auto-textarea-input,
.auto-textarea-input::placeholder,
.el-descriptions__title,
.md,
.content-input-wrapper,
.auto-textarea-input,
.no-border,
.content-input,
.auto-textarea-wrapper,
.markdown-body,
.el-select-dropdown__item,
.el-select,
.el-select--mini,
.el-scrollbar__view,
.el-select-dropdown__list,
.el-select-dropdown,
.el-dialog__title,
.el-dialog__body,
.dropdown-item,
.el-picker-panel__link-btn,
.markdown-body h3,
.add-image-link input,
.add-image-link,
.fa-mavon-times,
.el-upload__tip,
.el-upload__text,
.el-upload-dragger,
.available,
.el-date-table th,
.el-autocomplete-suggestion__list li,
.el-scrollbar__view li,
.el-select-dropdown__list li,
.el-select-dropdown li,
.el-scrollbar__wrap,
.el-scrollbar__bar .is-horizontal,
.el-time-panel__footer,
.el-pagination__jump,
.el-pagination__total,
.el-time-spinner__item {
  color: var(--ltpp-main-text-color) !important;
}

.el-picker-panel__icon-btn {
  color: red !important;
}

.v-note-show,
.single-show,
.v-note-navigation-title,
.v-note-navigation-content {
  border-width: 0 !important;
  border-color: transparent !important;
  background-color: transparent !important;
}

.add-image-link,
.fa-mavon-times {
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  border-width: 0 !important;
}

.add-image-link input {
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  border-width: 0 !important;
  border: none !important;
}

.in-range {
  background-color: transparent !important;
  border-width: 0 !important;
  color: rgba(var(--ltpp-main-bk-color), 1) !important;
}

.confirm {
  background-color: rgba(var(--ltpp-light-color), 0) !important;
  border-width: 0 !important;
  color: deepskyblue !important;
  font-weight: bold !important;
}

.dropdown-item {
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
}

.dropdown-item :hover {
  color: red !important;
}

.el-popper {
  z-index: 1000002 !important;
}

.el-loading-mask,
.is-fullscreen {
  z-index: 1000010 !important;
}

.el-message,
.el-notification {
  z-index: 1000000000 !important;
}

.el-dialog__body {
  background-color: rgba(
    var(--ltpp-dialog-bk-color),
    var(--ltpp-dialog-bk-opacity)
  ) !important;
  border: 0 !important;
}

.el-dialog__header {
  background-color: rgba(
    var(--ltpp-dialog-bk-color),
    var(--ltpp-dialog-bk-opacity)
  ) !important;
  height: 0rem !important;
  border: 0 !important;
}

.el-dialog__footer {
  background-color: rgba(
    var(--ltpp-dialog-bk-color),
    var(--ltpp-dialog-bk-opacity)
  ) !important;
}

.el-scrollbar__view,
.el-select-dropdown__list,
.el-select-dropdown {
  background-color: var(--ltpp-main-color) !important;
  border: none !important;
}

.el-select,
.el-select--mini {
  background-color: Transparent !important;
  border: none !important;
}

.el-select-dropdown__item {
  background-color: var(--ltpp-main-color) !important;
  margin-bottom: 0.6rem !important;
}

.selected {
  color: red !important;
}

.el-select-dropdown__item.hover {
  background-color: transparent !important;
  color: rgb(var(--ltpp-main-bk-color)) !important;
  font-weight: bold;
}

.el-select-dropdown__wrap {
  max-height: 26rem !important;
  border: none !important;
}

p {
  line-height: 1.67;
}

.md,
.content-input-wrapper,
.auto-textarea-input,
.no-border,
.content-input,
.auto-textarea-wrapper,
.markdown-body {
  background-color: transparent !important;
  border-color: transparent !important;
  border-width: 0rem !important;
  p {
    margin: 0px !important;
  }
  .v-note-img-wrapper {
    background-color: transparent !important;
  }
}

.el-descriptions__title {
  font-size: 0.88rem;
}

.op-btn.cancel {
  font-weight: 400 !important;
  background-color: #67c23a !important;
}

.op-btn.sure {
  font-weight: 400 !important;
  background-color: deepskyblue !important;
}

.el-select,
.el-input,
.el-input__inner {
  background-color: var(--ltpp-main-color) !important;
  border-color: var(--ltpp-main-color) !important;
  resize: none !important;
}

/*按钮悬浮*/
.el-button:hover {
  text-shadow: 0 0 1.2rem #cacac6 !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.popper__arrow {
  border: transparent !important;
  border-width: 0px !important;
}

.el-tooltip__popper {
  max-width: 60% !important;
  background: var(--ltpp-main-color) !important;
  border: transparent !important;
  border-width: 0px !important;
  box-shadow: 0 2px 5px rgba(var(--ltpp-light-color), 0.16),
    2px 0 5px rgba(var(--ltpp-light-color), 0.16),
    -2px 0 5px rgba(var(--ltpp-light-color), 0.16),
    0 -2px 5px rgba(var(--ltpp-light-color), 0.16);
  font-size: 1rem !important;
  font-weight: bold !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.el-tooltip__popper[x-placement^='top'] .popper__arrow:after {
  border-top-color: var(--ltpp-main-color) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.el-tooltip__popper[x-placement^='right'] .popper__arrow:after {
  border-right-color: var(--ltpp-main-color) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.el-tooltip__popper[x-placement^='bottom'] .popper__arrow:after {
  border-bottom-color: var(--ltpp-main-color) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.el-tooltip__popper[x-placement^='left'] .popper__arrow:after {
  border-left-color: var(--ltpp-main-color) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
}

.no-select {
  -webkit-touch-callout: none !important;
  -webkit-user-select: none !important;
  -khtml-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
  user-select: none !important;
}

/*markdown */
.v-note-panel {
  border: none !important;
}

.v-show-content {
  background-color: transparent !important;
  border-width: 0rem !important;
  font-size: 1.06rem;
}

/*表格颜色*/
.el-table .warning-row {
  background: rgba(255, 146, 146, 0.651) !important;
}

.el-table .success-row {
  background: #b9ff96 !important;
}

.v-show-content h1,
.scroll-style h1,
.scroll-style-border-radius h1 {
  color: var(--ltpp-h1-color) !important;
}

.v-show-content h2,
.scroll-style h2,
.scroll-style-border-radius h2 {
  color: var(--ltpp-h2-color) !important;
}

.v-show-content h3,
.scroll-style h3,
.scroll-style-border-radius h3 {
  color: var(--ltpp-h3-color) !important;
}

.v-show-content h4,
.scroll-style h4,
.scroll-style-border-radius h4 {
  color: var(--ltpp-h4-color) !important;
}

.v-show-content h5,
.scroll-style h5,
.scroll-style-border-radius h5 {
  color: var(--ltpp-h5-color) !important;
}

.v-show-content h6,
.scroll-style h6,
.scroll-style-border-radius h6 {
  color: var(--ltpp-h5-color) !important;
}

.hljs {
  border-color: transparent !important;
  font-size: 1.16rem !important;
  background-color: transparent !important;
}

.hljs-params {
  color: #c678dd !important;
}

.v-show-content tr,
.scroll-style tr,
.scroll-style-border-radius tr,
.v-show-content th,
.scroll-style th,
.scroll-style-border-radius th {
  background-color: rgba(
    var(--ltpp-code-bk-color),
    var(--ltpp-code-bk-color-opacity)
  ) !important;
  padding: 0.66rem !important;
  margin: 0.36rem 0rem !important;
  white-space: pre-wrap !important;
  word-wrap: break-word !important;
}

.md a {
  color: deeppink !important;
  text-decoration: none !important;
}

.input {
  font-size: 1.06rem;
  background-color: rgba(var(--ltpp-main-bk-color), 0.6);
  border-width: 0rem;
  min-height: 2rem;
  width: auto;
  text-align: left;
}

.el-button {
  border: 0 !important;
}

.my-span {
  /* 不换行，溢出隐藏 */
  white-space: nowrap !important;
  overflow: hidden !important;
  overflow-wrap: break-word !important;
  text-overflow: ellipsis !important;
}

.my-span-parent {
  width: 100%;
  overflow: hidden !important;
  overflow-wrap: break-word !important;
  text-overflow: ellipsis;
}

.el-pagination.is-background .el-pager li {
  color: var(--ltpp-main-text-color) !important;
  background-color: rgba(var(--ltpp-light-color), 1) !important;
}

.el-icon-arrow-left:before,
.el-icon-arrow-right:before {
  color: var(--ltpp-main-text-color) !important;
  background-color: rgba(var(--ltpp-light-color), 1) !important;
}

.el-pagination.is-background .el-pager li:not(.disabled).active {
  color: var(--ltpp-main-text-color) !important;
  background-color: deepskyblue !important;
}

.el-switch__label {
  font-weight: bold !important;
}

.el-icon-edit-outline {
  color: deepskyblue !important;
}

.v-note-wrapper .v-note-op {
  border: 0px solid transparent !important;
  border-bottom: 2px solid rgba(var(--ltpp-light-color), 1) !important;
  border-radius: 0px !important;
}

.v-note-wrapper {
  z-index: 1 !important;
  min-width: auto !important;
}

.is-active {
  color: deepskyblue !important;
  font-weight: bold;
}

.el-carousel__arrow,
.el-carousel__arrow--right,
.btn-prev,
.btn-next {
  background-color: rgba(var(--ltpp-light-color), 1) !important;
}

.el-input,
.el-pagination__editor,
.is-in-pagination {
  padding: 0rem !important;
}

.el-pagination__jump,
.el-pagination__total {
  padding: 0rem 0.6rem !important;
}

.el-submenu__title:hover {
  color: deepskyblue !important;
  background-color: rgba(var(--ltpp-light-color), 0.166) !important;
}

.search {
  padding: 0.6rem 0.6rem !important;
}

.v-note-navigation-title,
.v-note-read-model,
.scroll-style.show,
.v-note-navigation-content,
.scroll-style,
.auto-textarea-input,
.no-border,
.no-resize,
.v-left-item,
.transition,
.v-right-item,
.transition {
  color: var(--ltpp-main-text-color) !important;
  background-color: transparent !important;
  border-width: 0rem !important;
  padding: 0rem 0rem;
}

.scroll-style.show {
  background-color: rgb(var(--ltpp-main-bk-color)) !important;
}

.fullscreen {
  z-index: 100000000 !important;
  background-color: rgb(var(--ltpp-main-bk-color)) !important;
}

.add-image-link {
  color: rgb(0, 0, 0) !important;
}

.v-note-op,
.content-input-wrapper,
.v-note-edit.diletea-wrapper.scroll-style.transition {
  color: var(--ltpp-main-text-color) !important;
  background-color: transparent !important;
}

.op-icon {
  color: var(--ltpp-main-text-color) !important;
  background-color: transparent !important;
}

.op-icon:hover {
  color: rgb(0, 0, 0) !important;
  background-color: var(--ltpp-main-text-color) !important;
}

.v-show-content,
.scroll-style,
.scroll-style-border-radius {
  background-color: transparent !important;
  padding: 0rem 0rem;
  border-width: 0rem !important;
}

.el-range-input,
.el-range-separator,
.el-data-editor,
.el-range-editor,
.el-data-editor--datetimerange {
  color: var(--ltpp-main-text-color) !important;
  background-color: transparent !important;
  border-width: 0rem !important;
}

.el-pager {
  color: var(--ltpp-main-text-color) !important;
  background-color: Transparent !important;
  border-width: 0rem !important;
}

.el-textarea__inner {
  color: var(--ltpp-main-text-color) !important;
  background-color: rgba(var(--ltpp-light-color), 1) !important;
  border-color: rgba(var(--ltpp-light-color), 1) !important;
}

.el-scrollbar__thumb {
  border-width: 0rem !important;
  width: 0rem !important;
  height: 0rem !important;
  background-color: transparent !important;
}

.el-icon-arrow-left:before,
.el-icon-arrow-right:before {
  background-color: transparent !important;
}

.el-picker-panel__link-btn,
.el-button.is-plain {
  border-width: 0 !important;
  font-weight: bold !important;
  color: deepskyblue !important;
  font-size: 0.96rem !important;
}

.el-button--default {
  padding: 0.6rem 1rem !important;
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  color: var(--ltpp-main-text-color) !important;
}

.el-descriptions__body {
  background-color: rgba(var(--ltpp-light-color), 1) !important;
  color: var(--ltpp-main-text-color) !important;
}

.el-descriptions-item__cell,
.el-descriptions-item__label,
.is-bordered-label {
  background-color: rgba(var(--ltpp-light-color), 1) !important;
  color: var(--ltpp-main-text-color) !important;
}

.el-scrollbar__bar,
.is-horizontal {
  background-color: transparent !important;
  color: var(--ltpp-main-text-color) !important;
  height: 0rem !important;
  border: none !important;
}

.markdown-body table td,
.markdown-body table th {
  border: 2px solid rgb(var(--ltpp-main-bk-color)) !important;
}

table {
  margin: 0.36rem 0rem !important;
}

::-webkit-scrollbar {
  z-index: 1000000;
  width: 0rem !important;
  height: 0.58rem !important;
  border-radius: 0rem;
  background-color: transparent;
}

/* 滚动条上的按钮 (上下箭头). */
::-webkit-scrollbar-button {
  background-color: transparent;
  border-radius: 0rem;
  height: 0rem;
  width: 0rem;
}

/* 滚动条上的滚动滑块. */
::-webkit-scrollbar-thumb {
  background-color: rgba(var(--ltpp-scroll-color), 1);
  border-radius: 0rem;
}

/*  滚动条轨道. */
::-webkit-scrollbar-track {
  border-radius: 0rem;
  background-color: var(--ltpp-main-color);
}

/* 滚动条没有滑块的轨道部分 */
::-webkit-scrollbar-track-piece {
  background-color: transparent;
}

/* 当同时有垂直滚动条和水平滚动条时交汇的部分. */
::-webkit-scrollbar-corner {
  background-color: transparent;
  border-radius: 0rem;
}

/* 某些元素的corner部分的部分样式(例:textarea的可拖动按钮). */
::-webkit-resizer {
  width: 0rem;
  height: 0rem;
  background-color: Transparent;
}
</style>
