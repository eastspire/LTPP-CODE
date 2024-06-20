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
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
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
      <div style="overflow: auto">
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
import { monaco } from '../plugins/monacoEditor';
let contentWidth = 0;
let contentHeight = 0;
let last_max_width = 0;
let timer = null;

export default {
  name: 'ShowCode',
  props: {
    language: {
      default: 'cpp',
    },
    code: {
      default: '加载中',
    },
  },
  created() {
    this.my_ide_id = this.randomString();
    this.changeCodeCSS(this.usertheme);
  },
  async mounted() {
    await this.waitDomLoad(this.my_ide_id, 100);
    // 创建 Monaco Editor
    try {
      this.editor = monaco.editor.create(this.$refs[this.my_ide_id], {
        value: this.code,
        language: this.language,
        theme: this.usertheme,
        fontSize: 18,
        tabSize: 4,
        accessibilityHelpUrl: '',
        smoothScrolling: true,
        links: true,
        folding: true,
        mouseWheelZoom: true,
        cursorSmoothCaretAnimation: true,
        contextmenu: false,
        cursorBlinking: 'smooth',
        cursorWidth: 2,
        automaticLayout: false,
        readOnly: true,
        scrollbar: {
          verticalScrollbarSize: 0,
          vertical: 'hidden', // 垂直滚动条根据内容溢出自动显示
          horizontalSliderSize: 8,
          horizontal: 'auto', // 水平滚动条根据内容溢出自动显示
          alwaysConsumeMouseWheel: false, // 滚动
        },
        scrollBeyondLastLine: false, // 最后一行多出一个屏幕高度
        wordWrap: 'off', // 溢出换行
        wrappingStrategy: 'advanced',
        minimap: {
          enabled: false, // 关闭预览栏
        },
        overviewRulerLanes: 0,
      });
      this.editor.onDidContentSizeChange(this.updateHeight);
      this.updateHeight();
      timer = setInterval(() => {
        if (last_max_width == this.$store.state.max_width) {
          // 宽度无改变，返回
          return;
        }
        this.updateHeight();
      }, 360);
    } catch (err) {}
    if (this.editor) {
      this.onExit();
    }
  },
  destroyed() {
    try {
      clearInterval(timer);
      timer = null;
      this.editor && this.editor.dispose();
    } catch (err) {}
  },
  data() {
    return {
      my_ide_id: '',
      editor: null,
      usertheme: window.localStorage.getItem('theme') ?? 'vs-dark',
    };
  },
  methods: {
    updateHeight() {
      if (!this.editor) {
        return;
      }
      try {
        if (
          document.fullscreenElement ||
          document.mozFullScreenElement ||
          document.webkitFullscreenElement ||
          document.msFullscreenElement
        ) {
          // 全屏更新高度宽度会有BUG，直接重新布局
          this.editor.layout();
          return;
        }
        contentWidth = last_max_width = this.$store.state.max_width;
        // 题目界面需要减去边距
        const rootFontSize = parseFloat(
          getComputedStyle(document?.documentElement)?.fontSize
        );
        contentWidth -= rootFontSize * 2.476;
        contentHeight = Math.max(
          window.innerHeight * 0.78,
          this.editor.getContentHeight()
        );
        this.$refs[this.my_ide_id].style.width = `${contentWidth}px`;
        this.$refs[this.my_ide_id].style.height = `${contentHeight}px`;
        this.editor.layout({ width: contentWidth, height: contentHeight });
      } catch (err) {}
    },
    onExit() {
      let element_dom = document.getElementById(this.my_ide_id);
      if (!element_dom) {
        return;
      }
      let fn = () => {
        setTimeout(() => {
          this.$nextTick(() => {
            this.updateHeight();
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
          document.addEventListener('fullscreenchange', fn);
        } else if (element_dom.mozRequestFullScreen) {
          document.addEventListener('mozfullscreenchange', fn);
        } else if (element_dom.webkitRequestFullscreen) {
          document.addEventListener('webkitfullscreenchange', fn);
        } else if (element_dom.msRequestFullscreen) {
          document.addEventListener('msfullscreenchange', fn);
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
