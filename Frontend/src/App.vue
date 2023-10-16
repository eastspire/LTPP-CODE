<template>
  <div id="app" @contextmenu.prevent="" class="no-select;">
    <div class="default-bk"></div>
    <router-view @contextmenu.prevent=""></router-view>
  </div>
</template>

<script>
window.onload = () => {
  document.addEventListener("touchstart", function (event) {
    if (event.touches.length > 1) {
      event.preventDefault();
    }
  });
  document.addEventListener("gesturestart", function (event) {
    event.preventDefault();
  });
};
export default {
  name: "app",
  data() {
    return {
      version: "1.4.1",
    };
  },
  beforeCreate() {
    let authorization = window.localStorage.getItem("authorization");
    let key = window.localStorage.getItem("key");
    if (!authorization || !key) {
      this.logoutRemove();
      return;
    }
  },
  async mounted() {
    try {
      this.init();
      let is_electron = false;
      is_electron =
        typeof navigator !== "undefined" &&
        navigator.userAgent.toLowerCase().indexOf("electron") !== -1;

      let key = "time";
      const max_time = 86400;
      let last = window.localStorage.getItem(key);
      let now = new Date().getTime();
      if (!last || (parseInt(now) - parseInt(last)) / 1000 > max_time) {
        this.$notice({
          title: "系统资源检查",
          dangerouslyUseHTMLString: true,
          message: "系统资源更新中",
          duration: 3600,
          offset: 80,
        });
        window.localStorage.setItem(key, now);
        if (!is_electron) {
          setTimeout(() => {
            location.reload(true);
          }, 1000);
          return;
        }
      }
      this.$notice({
        title: "系统资源检查",
        dangerouslyUseHTMLString: true,
        message: "系统资源检查完成",
        duration: 3600,
        offset: 80,
      });
      window.localStorage.setItem(key, now);
      setInterval(() => {
        this.getVersion(is_electron);
      }, 60000);
      await this.getVersion(is_electron);
    } catch (err) {}
  },
  methods: {
    init() {
      let is_public_network = window.localStorage.getItem("is_public_network");
      if (is_public_network == 1) {
        this.$store.commit("updateObj", { is_public_network: 1 });
      } else if (is_public_network == 0) {
        this.$store.commit("updateObj", { is_public_network: 0 });
      } else {
        window.localStorage.setItem("is_public_network", 1);
        this.$store.commit("updateObj", { is_public_network: 1 });
      }
    },
    async getVersion(is_electron = false) {
      while (true) {
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Version/getVersion",
          portType: {
            process: "8793",
          },
        }).catch(async (e) => {
          this.$route.path != "/maintenance" &&
            this.$router.replace({
              path: "/maintenance",
              replace: true,
            });
        });
        if (res.code == 1) {
          if (this.version < res.version) {
            if (!is_electron) {
              this.$notice({
                title: "发现新版本！",
                dangerouslyUseHTMLString: true,
                message: "系统自动更新中",
                duration: 3600,
                offset: 80,
              });
              setTimeout(() => {
                location.reload(true);
              }, 1000);
            } else {
              this.$notice({
                title: "发现新版本！",
                dangerouslyUseHTMLString: true,
                message: "请手动安装新版本",
                duration: 0,
                offset: 80,
              });
              window.open(res.url, "_blank");
            }
          } else {
            if (this.$route.path === "/maintenance") {
              this.$router.replace({
                path: "/homelist",
                replace: true,
              });
            }
          }
          return;
        } else {
          this.$route.path != "/maintenance" &&
            this.$router.replace({
              path: "/maintenance",
              replace: true,
            });
        }
      }
    },
  },
};
</script>

<style>
* {
  padding: 0;
  /*清除元素的内边距*/
  margin: 0;
  /*清除元素的外边距*/
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
}

blockquote {
  background: rgb(214 215 215/36%);
  border-left: 8px solid rgb(224 224 224/66%);
  padding: 0.66rem !important;
  margin: 0;
  color: #ffffff !important;
  font-weight: 400;
}

pre code.hljs {
  font-family: Consolas, Monaco, DejaVu Sans Mono, monospace;
  line-height: 1.46;
  padding: 2px 4px;
  display: block;
  overflow-x: auto;
  padding: 1em;
  border-radius: 0rem !important;
  font-size: 1.06rem;
}

hr {
  border-top: 3px dotted rgb(171, 178, 191);
  border-bottom: none;
}

code.hljs {
  padding: 3px 5px;
}

.hljs {
  background: rgb(40, 44, 52);
  color: rgb(171, 178, 191);
}

.hljs-comment {
  color: rgb(92, 99, 112);
}

.hljs-punctuation,
.hljs-tag {
  color: rgb(171, 178, 191);
}

.hljs-property {
  color: rgb(97, 175, 239);
}

.hljs-attr,
.hljs-name {
  color: rgb(224, 108, 117);
}

.hljs-attribute,
.hljs-doctag,
.hljs-keyword,
.hljs-meta.hljs-keyword,
.hljs-name,
.hljs-selector-tag {
  font-weight: 700;
}

.hljs-deletion,
.hljs-number,
.hljs-quote,
.hljs-selector-class,
.hljs-selector-id,
.hljs-string,
.hljs-template-tag,
.hljs-type {
  color: #c678dd;
}

.hljs-section,
.hljs-title {
  color: rgb(97, 175, 239);
  font-weight: 700;
}

.hljs-link,
.hljs-operator,
.hljs-regexp,
.hljs-selector-attr,
.hljs-selector-pseudo,
.hljs-symbol,
.hljs-template-variable,
.hljs-variable {
  color: rgb(209, 154, 102);
}

.hljs-literal {
  color: rgb(198, 120, 221);
}

.hljs-addition,
.hljs-built_in,
.hljs-bullet,
.hljs-code {
  color: #61aeee;
}

.hljs-meta {
  color: #c678dd;
  font-weight: bold;
}

.hljs-meta.hljs-string {
  color: #61aeee;
}

.hljs-emphasis {
  font-style: italic;
}

.hljs-strong {
  font-weight: 700;
}

.hljs-keyword {
  color: rgb(198, 120, 221);
}

pre {
  line-height: 1.56;
  white-space: pre-wrap;
  white-space: -moz-pre-wrap;
  white-space: -webkit-pre-wrap;
  white-space: -o-pre-wrap;
  word-wrap: break-word;
}

.hljs-doctag {
  color: gray;
}

.hljs-params {
  color: #c678dd;
}

span.hljs-function,
.hljs-subst {
  color: rgb(171, 178, 191);
}

span.hljs-string {
  font-weight: bold;
}

.monaco-editor .view-lines {
  font-weight: bold !important;
}

.el-menu {
  display: flex !important;
  flex-direction: column !important;
  align-content: center !important;
  padding: 0 !important;
}

.el-submenu__title,
.el-menu-item .el-tooltip {
  padding-left: 12.555px !important;
}

.el-menu--collapse {
  width: 50px !important;
}

can-select {
  -webkit-user-select: auto !important;
  -moz-user-select: auto !important;
  -ms-user-select: auto !important;
  user-select: auto !important;
}

.shadow {
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3), 2px 0 5px rgba(0, 0, 0, 0.3),
    -2px 0 5px rgba(0, 0, 0, 0.3), 0 -2px 5px rgba(0, 0, 0, 0.3);
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
  background-color: rgba(0, 0, 0, 0) !important;
  z-index: -1000000;
  will-change: transform;
}

.default-bk {
  position: fixed;
  background-position: center center;
  background-repeat: no-repeat;
  height: 100vh;
  width: 100vw;
  margin: 0;
  z-index: -1000000;
  will-change: transform;
  background-color: #e493d0;
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

.default-bk::after {
  content: "";
  display: block;
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
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
  background-color: #282c34 !important;
  z-index: -100000;
  will-change: transform;
}

.el-progress-bar__outer {
  background-color: rgba(255, 255, 255, 0.1) !important;
}

.el-progress-bar__innerText {
  display: none !important;
}

.el-drawer {
  background-color: rgba(41, 50, 56, 1) !important;
}

.is-active {
  border: 0rem !important;
  font-size: 1.06rem !important;
  border-color: rgba(0, 0, 0, 0) !important;
  color: deepskyblue !important;
  background-color: rgba(0, 0, 0, 0) !important;
}

.no-select,
.el-menu--horizontal,
.el-menu-item {
  border: 0rem !important;
  border-color: rgba(0, 0, 0, 0) !important;
  font-size: 1rem !important;
  z-index: 1000006 !important;
}

.el-menu-item:hover {
  color: deepskyblue !important;
  background-color: rgba(0, 0, 0, 0.46) !important;
  z-index: 1000006 !important;
}

.el-tabs__item,
.is-top {
  border: 0rem !important;
  font-size: 1.06rem !important;
  border-color: rgba(0, 0, 0, 0) !important;
  background-color: rgba(53, 61, 68, 1) !important;
}

.el-tabs,
.el-tabs__nav-scroll,
.el-tabs__content {
  border-radius: 0;
  border: 0rem !important;
  border-color: rgba(0, 0, 0, 0) !important;
  color: rgba(255, 255, 255, 1) !important;
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
  background-color: rgba(30, 30, 30, 1) !important;
}

.el-upload-dragger {
  background-color: rgba(41, 50, 56, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
  border-color: #c0c4cc !important;
}

.el-input__count-inner {
  background-color: rgba(0, 0, 0, 0) !important;
}

.el-autocomplete-suggestion,
.el-popper,
.el-popper:hover li,
.el-autocomplete-suggestion:hover li {
  background-color: rgba(41, 50, 56, 0) !important;
  border-width: 0 !important;
}

.el-table {
  will-change: transform;
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
  transform: scale(1.06);
  cursor: pointer;
  -webkit-transition: all 1s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 1s ease !important;
  /* Firefox */
  -o-transition: all 1s ease !important;
  /* Opera */
  -ms-transition: all 1s ease !important;
  /* IE 9 */
  transition: all 1s ease !important;
}

/**
动画
*/
.animate {
  -webkit-transition: all 1s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 1s ease !important;
  /* Firefox */
  -o-transition: all 1s ease !important;
  /* Opera */
  -ms-transition: all 1s ease !important;
  /* IE 9 */
  transition: all 1s ease !important;
}

.sure {
  background-color: deepskyblue !important;
  border-width: 0 !important;
  color: rgba(255, 255, 255, 1) !important;
}

.cancel {
  background-color: transparent !important;
  border-width: 0 !important;
  color: deepskyblue !important;
  font-weight: bold;
}

.el-time-spinner__item.active:not(.disabled) {
  color: red !important;
}

.el-scrollbar__wrap,
.el-scrollbar__bar .is-horizontal,
.el-time-panel__footer {
  background-color: #1e1e1e !important;
}

.next-month,
.prev-month {
  color: rgba(255, 255, 255, 0.36) !important;
}

.available {
  color: rgb(255, 255, 255) !important;
}

.el-time-spinner__item {
  color: rgb(255, 255, 255) !important;
}

.v-note-show,
.single-show,
.v-note-navigation-title,
.v-note-navigation-content {
  border-width: 0 !important;
  border-color: rgba(0, 0, 0, 0) !important;
  background-color: rgba(0, 0, 0, 0) !important;
}

.add-image-link,
.fa-mavon-times {
  background-color: rgba(41, 50, 56, 1) !important;
  border-width: 0 !important;
  color: rgba(255, 255, 255, 1) !important;
}

.add-image-link input {
  background-color: rgba(41, 50, 56, 1) !important;
  border-width: 0 !important;
  color: rgba(255, 255, 255, 1) !important;
  border: none !important;
}

.markdown-body h3 {
  color: rgba(255, 255, 255, 1) !important;
}

.el-picker-panel__link-btn {
  background-color: rgba(0, 0, 0, 0) !important;
  border-width: 0 !important;
  font-weight: bold !important;
  color: rgba(0, 162, 60, 1) !important;
  font-size: 1rem !important;
}

.in-range {
  background-color: rgba(0, 0, 0, 0) !important;
  border-width: 0 !important;
  color: rgba(41, 50, 56, 1) !important;
}

.confirm {
  background-color: rgba(41, 50, 56, 0) !important;
  border-width: 0 !important;
  color: deepskyblue !important;
  font-weight: bold !important;
}

.dropdown-item {
  background-color: rgba(30, 30, 30, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
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
  background-color: rgba(41, 50, 56, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
  border: 0 !important;
}

.el-dialog__header {
  background-color: rgba(41, 50, 56, 1) !important;
  height: 0rem !important;
  border: 0 !important;
}

.el-dialog__title {
  color: rgb(255, 255, 255) !important;
}

.el-dialog__footer {
  background-color: rgba(41, 50, 56, 1) !important;
}

.el-scrollbar__view,
.el-select-dropdown__list,
.el-select-dropdown {
  background-color: rgba(30, 30, 30, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
  border: none !important;
}

.el-select,
.el-select--mini {
  background-color: Transparent !important;
  color: rgba(255, 255, 255, 1) !important;
  border: none !important;
}

.el-select-dropdown__item {
  background-color: rgba(30, 30, 30, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
  margin-bottom: 0.6rem !important;
}

.selected {
  color: red !important;
}

.el-select-dropdown__item.hover {
  background-color: rgba(255, 255, 255, 0.06) !important;
  color: deepskyblue !important;
  font-weight: bold;
}

.el-select-dropdown__wrap {
  max-height: 26rem !important;
  border: none !important;
}

.md,
.content-input-wrapper,
.auto-textarea-input,
.no-border,
.content-input,
.auto-textarea-wrapper,
.markdown-body {
  color: rgba(255, 255, 255, 1) !important;
  background-color: rgba(0, 0, 0, 0) !important;
  border-color: rgba(0, 0, 0, 0) !important;
  border-width: 0rem !important;
}

.el-select,
.el-input,
.el-input__inner {
  color: rgba(255, 255, 255, 1) !important;
  background-color: rgba(30, 30, 30, 1) !important;
  border-color: rgba(30, 30, 30, 1) !important;
  resize: none !important;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.el-loading-spinner,
.el-loading-mask,
.is-fullscreen {
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
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

.el-tooltip__popper {
  max-width: 60% !important;
  background: rgb(41, 50, 56) !important;
  border: rgb(41, 50, 56) !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3), 2px 0 5px rgba(0, 0, 0, 0.3),
    -2px 0 5px rgba(0, 0, 0, 0.3), 0 -2px 5px rgba(0, 0, 0, 0.3);
  font-size: 1rem !important;
  color: rgb(255, 255, 255) !important;
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
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.el-tooltip__popper[x-placement^="top"] .popper__arrow:after {
  border-top-color: rgb(41, 50, 56) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.el-tooltip__popper[x-placement^="right"] .popper__arrow:after {
  border-right-color: rgb(41, 50, 56) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.el-tooltip__popper[x-placement^="bottom"] .popper__arrow:after {
  border-bottom-color: rgb(41, 50, 56) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.el-tooltip__popper[x-placement^="left"] .popper__arrow:after {
  border-left-color: rgb(41, 50, 56) !important;
  -webkit-transition: all 0.3s ease !important;
  /* Safari and Chrome */
  -moz-transition: all 0.3s ease !important;
  /* Firefox */
  -o-transition: all 0.3s ease !important;
  /* Opera */
  -ms-transition: all 0.3s ease !important;
  /* IE 9 */
  transition: all 0.3s ease !important;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

.no-select {
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

/*markdown */
.v-note-panel {
  border: none !important;
}

.v-show-content {
  background-color: rgba(0, 0, 0, 0) !important;
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
  color: red !important;
}

.v-show-content h2,
.scroll-style h2,
.scroll-style-border-radius h2 {
  color: rgb(255, 66, 66) !important;
}

.v-show-content h3,
.scroll-style h3,
.scroll-style-border-radius h3 {
  color: deepskyblue !important;
}

.v-show-content h4,
.scroll-style h4,
.scroll-style-border-radius h4 {
  color: rgb(66, 210, 255) !important;
}

.v-show-content h5,
.scroll-style h5,
.scroll-style-border-radius h5 {
  color: deeppink !important;
}

.v-show-content h6,
.scroll-style h6,
.scroll-style-border-radius h6 {
  color: rgb(255, 66, 167) !important;
}

.hljs {
  border-color: rgba(0, 0, 0, 0) !important;
  font-size: 1.16rem !important;
  background-color: rgba(0, 0, 0, 0) !important;
}

.hljs-params {
  color: #c678dd !important;
}

pre,
.v-show-content tr,
.scroll-style tr,
.scroll-style-border-radius tr,
.v-show-content th,
.scroll-style th,
.scroll-style-border-radius th {
  border: none !important;
  background-color: rgba(0, 0, 0, 0) !important;
  /* pre保持格式的同时实现自动换行 */
  white-space: pre-wrap !important;
  word-wrap: break-word !important;
}

.md a {
  color: deeppink !important;
  text-decoration: none !important;
}

.input {
  font-size: 1.06rem;
  background-color: rgba(0, 0, 0, 0.6);
  color: rgb(255, 255, 25);
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
}

.my-span-parent {
  width: 100%;
  overflow: hidden !important;
}

::-webkit-scrollbar {
  z-index: 1000000 !important;
  width: 0rem;
  height: 0.36rem !important;
  border-radius: 3rem;
  background-color: rgba(41, 50, 56, 0.36);
}

/* 滚动条上的按钮 (上下箭头). */
::-webkit-scrollbar-button {
  background-color: Transparent;
  border-radius: 0rem;
  height: 0rem;
  width: 0rem;
}

/* 滚动条上的滚动滑块. */
::-webkit-scrollbar-thumb {
  background-color: rgb(183, 185, 186);
  border-radius: 3rem;
}

/*  滚动条轨道. */
::-webkit-scrollbar-track {
  border-radius: 3rem;
  background-color: rgba(41, 50, 56, 0.36);
}

/* 滚动条没有滑块的轨道部分 */
::-webkit-scrollbar-track-piece {
  background-color: rgba(41, 50, 56, 0.36);
}

/* 当同时有垂直滚动条和水平滚动条时交汇的部分. */
::-webkit-scrollbar-corner {
  background-color: rgba(41, 50, 56, 0.36);
  border-radius: 3rem;
}

/* 某些元素的corner部分的部分样式(例:textarea的可拖动按钮). */
::-webkit-resizer {
  width: 0rem;
  height: 0rem;
  background-color: Transparent;
}
</style>