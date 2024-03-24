<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-08 10:01:48
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-26 00:03:40
 * @FilePath: \LTPP-CODE\Frontend\src\components\showcode.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
-->
<template>
  <div
    v-if="loadfinish"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div
      @dblclick="full()"
      style="
        background-color: rgba(var(--ltpp-main-bk-color), 0);
        color: azure;
        border-width: 0rem;
        border-color: rgba(var(--ltpp-main-bk-color), 0);
        height: auto;
        width: 100%;
      "
    >
      <!-- IDE -->
      <div style="height: 66vh">
        <div
          :ref="my_ide_id"
          :id="my_ide_id"
          style="height: 100%; width: 100%; will-change: transform"
        ></div>
      </div>
    </div>
  </div>
</template>
<script>
import { monaco } from "../plugins/monacoEditor";

export default {
  name: "ShowCode",
  props: {
    language: {
      default: "cpp",
    },
    code: {
      default: "加载中",
    },
  },
  created() {
    this.loadfinish = false;
    this.my_ide_id = this.randomString();
  },
  mounted() {
    // 创建 Monaco Editor
    this.loadfinish = true;
    setTimeout(() => {
      this.$nextTick(() => {
        try {
          this.editor = monaco.editor.create(this.$refs[this.my_ide_id], {
            value: this.code,
            language: this.language,
            theme: this.usertheme,
            fontSize: 17,
            tabSize: 4,
            scrollBeyondLastLine: true,
            accessibilityHelpUrl: "",
            smoothScrolling: true,
            links: true,
            folding: true,
            mouseWheelZoom: true,
            cursorSmoothCaretAnimation: true,
            contextmenu: false,
            cursorBlinking: "smooth",
            cursorWidth: 2,
            automaticLayout: false,
            readOnly: true,
            scrollbar: {
              verticalScrollbarSize: 0,
              vertical: "hidden", // 垂直滚动条根据内容溢出自动显示
              horizontalSliderSize: 8,
              horizontal: "auto", // 水平滚动条根据内容溢出自动显示
            },
          });
        } catch (err) {}
        this.changeCodeCSS(this.usertheme);
        if (this.editor) {
          this.onExit();
        }
      });
    }, 0);
  },
  destroyed() {
    this.editor && this.editor.dispose();
  },
  data() {
    return {
      loadfinish: false,
      my_ide_id: "",
      editor: null,
      usertheme: window.localStorage.getItem("theme") ?? "vs-dark",
    };
  },
  methods: {
    onExit() {
      let element_dom = document.getElementById(this.my_ide_id);
      if (!element_dom) {
        return;
      }
      let fn = () => {
        setTimeout(() => {
          this.$nextTick(() => {
            this.editor && this.editor.layout();
          });
        }, 0);
      };
      if (
        !document.fullscreenElement &&
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement &&
        !document.msFullscreenElement
      ) {
        if (element_dom.requestFullscreen) {
          document.addEventListener("fullscreenchange", fn);
        } else if (element_dom.mozRequestFullScreen) {
          document.addEventListener("mozfullscreenchange", fn);
        } else if (element_dom.webkitRequestFullscreen) {
          document.addEventListener("webkitfullscreenchange", fn);
        } else if (element_dom.msRequestFullscreen) {
          document.addEventListener("msfullscreenchange", fn);
        }
      }
    },
    full() {
      this.fullscreen(this.my_ide_id, () => {
        setTimeout(() => {
          this.$nextTick(() => {
            this.editor && this.editor.layout();
          });
        }, 0);
      });
    },
  },
};
</script>