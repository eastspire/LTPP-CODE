<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 22:11:28
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2024-01-07 16:23:11
 * @FilePath: \LTPP-CODE\Frontend\src\components\myide.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
-->
<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div
      style="
        background-color: rgba(var(--ltpp-main-bk-color), 0);
        border-width: 0rem;
        color: azure;
        border-color: rgba(var(--ltpp-main-bk-color), 0);
        height: auto;
        width: 100%;
      "
    >
      <div>
        <div>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0.88rem 0rem 0.5rem 0rem;
              float: left;
              color: var(--ltpp-main-text-color);
            "
          >
            代码编辑区
          </p>
          <div style="text-align: left; float: left">
            <el-select
              @change="ChangeCacheTheme()"
              :popper-append-to-body="true"
              v-model="usertheme"
              placeholder="请选择主题"
              style="margin: 0.6rem 0rem 0.8rem 2rem"
            >
              <el-option
                v-for="item in $SqsGlobal.themelist"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
          <div style="text-align: right">
            <el-button
              class="el-icon-full-screen"
              @click="full"
              type="primary"
              style="margin-right: 1.6rem; background-color: #242424"
            ></el-button>
            <el-select
              :popper-append-to-body="true"
              v-model="my_language"
              placeholder="请选择语言"
              @change="ChangeCacheLanguage()"
              style="margin: 0.6rem 0rem 0.8rem 0rem; text-align: left"
            >
              <el-option
                v-for="item in $SqsGlobal.options"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
        </div>

        <!-- IDE -->
        <div style="overflow: auto">
          <div
            :ref="my_ide_id"
            :id="my_ide_id"
            style="height: 100%; width: 100%; will-change: transform"
          ></div>
        </div>

        <div>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 0rem;
            "
          >
            输入的数据
          </p>
          <el-input
            type="textarea"
            :autosize="{ minRows: 1, maxRows: 20 }"
            placeholder="请输入测试样例"
            v-model.lazy="local_testin"
            style="font-size: 1rem; font-weight: bold; overflow-x: hidden"
          >
          </el-input>

          <div style="height: 0.36rem"></div>
          <div style="text-align: right">
            <el-button
              v-if="!contest_id && isac"
              size="small"
              type="danger"
              style="
                border-radius: 2rem;
                margin: 1rem 0rem 0rem 1.88rem;
                font-size: 1.06rem;
              "
              @click="towrite()"
              class="el-icon-upload pulse-enter-active"
              width="auto"
              >发布笔记</el-button
            >
            <el-button
              type="info"
              size="small"
              style="
                border-radius: 2rem;
                margin: 1rem 0rem 0rem 1.88rem;
                font-size: 1.06rem;
              "
              @click="reset()"
              class="el-icon-delete-solid pulse-enter-active"
              width="auto"
              >还原模板</el-button
            >
            <el-button
              v-if="is_has_ac_code"
              type="warning"
              size="small"
              style="
                border-radius: 2rem;
                margin: 1rem 0rem 0rem 1.88rem;
                font-size: 1.06rem;
              "
              @click="useAcCode()"
              class="el-icon-s-claim pulse-enter-active"
              width="auto"
              >使用AC代码</el-button
            >
            <el-button
              :loading="istest"
              :disabled="istest || isup"
              type="success"
              size="small"
              style="
                border-radius: 2rem;
                margin: 1rem 0rem 0rem 1.88rem;
                font-size: 1.06rem;
              "
              @click="testone()"
              class="el-icon-upload pulse-enter-active"
              width="auto"
              >在线测试</el-button
            >
            <el-button
              v-if="problem_data.id"
              :loading="isup"
              :disabled="istest || isup"
              type="primary"
              size="small"
              style="
                border-radius: 2rem;
                margin: 1rem 0rem 0rem 1.88rem;
                font-size: 1.06rem;
              "
              @click="submit()"
              class="el-icon-upload pulse-enter-active"
              width="auto"
              >提交代码</el-button
            >
          </div>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 0rem;
            "
            v-show="isshow"
          >
            输出/运行结果{{
              "（ 时间消耗：" +
              (usetime ? usetime : 0) +
              " MS ，内存消耗：" +
              (usememory ? usememory : 0) +
              " KB ）"
            }}
          </p>
          <div v-show="isshow">
            <div v-show="iswrong" @dblclick="copy(wrong)">
              <div v-show="!istestres" style="color: #fa278e">
                <pre>{{ wrong ? wrong : "\n" }}</pre>
              </div>
              <div v-show="istestres">
                <pre>{{ wrong ? wrong : "\n" }}</pre>
              </div>
            </div>
            <div v-show="isac">
              <div style="text-align: center; color: #21e016">
                <pre style="font-size: 1.6rem !important">{{
                  ac ? ac : "\n"
                }}</pre>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../updateCompoents/urlencode";
import { monaco } from "../plugins/monacoEditor";
let contentWidth = 0;
let contentHeight = 0;
let last_max_width = 0;
let timer = null;

export default {
  name: "Myide",
  props: {
    language: {
      default: null,
    },
    code: {
      default: "",
    },
    testin: {
      default: "",
    },
    contest_id: {
      default: "",
    },
    iscloudfile: {
      default: false,
    },
    problem_data: {
      default: function () {
        return {};
      },
    },
  },
  created() {
    this.changeCodeCSS(this.usertheme);
    this.my_ide_id = this.randomString();
    this.istestres = false;
    this.isshow = false;
    this.ac = "";
    this.wrong = "";
    this.isac = false;
    this.iswrong = false;
    this.isup = false;
    this.usetime = 0;
    this.usememory = 0;
  },
  async mounted() {
    this.listenKeydown();
    this.local_testin = this.testin;
    if (!this.iscloudfile) {
      let language = window.localStorage.getItem("language") ?? "cpp";
      if (language) {
        this.my_language = language;
      }
      let problem_name = this.problem_data?.problemName ?? "";
      this.my_code =
        window.localStorage.getItem(
          "idecode" + problem_name + this.my_language
        ) ?? "";
    } else {
      this.my_language = this.language;
      this.my_code = this.code;
    }

    try {
      await this.waitDomLoad(this.my_ide_id, 100);
      // 创建 Monaco Editor
      this.editor = monaco.editor.create(this.$refs[this.my_ide_id], {
        value: this.my_code,
        language: this.my_language,
        theme: this.usertheme,
        accessibilityHelpUrl: "",
        fontSize: 18,
        tabSize: 4,
        smoothScrolling: true,
        links: true,
        cursorSmoothCaretAnimation: true,
        readOnly: false,
        folding: true,
        contextmenu: false,
        suggestOnTriggerCharacters: true,
        cursorBlinking: "smooth",
        cursorWidth: 2,
        automaticLayout: false,
        mouseWheelZoom: true, // 缩放字体
        scrollbar: {
          verticalScrollbarSize: 0,
          vertical: "hidden", // 垂直滚动条根据内容溢出自动显示
          horizontalSliderSize: 8,
          horizontal: "auto", // 水平滚动条根据内容溢出自动显示
          alwaysConsumeMouseWheel: false, // 滚动
        },
        scrollBeyondLastLine: false, // 最后一行多出一个屏幕高度
        wordWrap: "off", // 溢出换行
        wrappingStrategy: "advanced",
        minimap: {
          enabled: false, // 关闭预览栏
        },
        overviewRulerLanes: 0,
      });
    } catch (err) {}

    if (this.editor) {
      try {
        if (this.problem_data?.id) {
          this.initcode();
        }
        this.onExit();
        this.editor.onDidChangeModelContent(this.save);
        this.editor.onDidContentSizeChange(this.updateHeight);
        this.updateHeight();
        this.loadCodeTips(this.my_language);
        timer = setInterval(() => {
          if (last_max_width == this.$store.state.max_width) {
            // 宽度无改变，返回
            return;
          }
          this.updateHeight();
        }, 100);
      } catch (err) {}
    }
  },
  destroyed() {
    try {
      this.removeListenKeydown();
      this.test_query_one_can_next = true;
      this.save(true);
      this.editor && this.editor.dispose();
    } catch (err) {}
    try {
      clearInterval(timer);
      timer = null;
      clearInterval(this.up_timer);
      this.up_timer = null;
    } catch (err) {}
  },
  watch: {
    usertheme: {
      handler(new_value, old_value) {
        this.changeCodeCSS(new_value);
      },
      immediate: true,
      deep: true,
    },
    testin: {
      handler(new_value, old_value) {
        this.local_testin = new_value;
      },
      immediate: true,
      deep: true,
    },
  },
  data() {
    return {
      test_query_one_can_next: true,
      has_ac_code: "",
      is_has_ac_code: false,
      local_testin: "",
      code_id: "",
      up_timer: null,
      my_language: "cpp",
      my_ide_id: "",
      isac: false,
      iswrong: false,
      istestres: false,
      istest: false,
      isshow: false,
      isup: false,
      editor: null,
      usememory: 0,
      usetime: 0,
      usertheme: window.localStorage.getItem("theme") ?? "vs-dark",
      my_code: "",
      wrong: "",
      ac: "",
      res: "",
      timer_id: null,
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
        if (this.problem_data?.id) {
          // 题目界面需要减去边距
          const rootFontSize = parseFloat(
            getComputedStyle(document?.documentElement)?.fontSize
          );
          contentWidth -= rootFontSize * 3.207;
        }
        if (this.iscloudfile) {
          // 云盘界面需要减去边距
          contentWidth -= 40;
        }
        contentHeight = Math.max(
          window.innerHeight * 0.78,
          this.editor.getContentHeight()
        );
        this.$refs[this.my_ide_id].style.width = `${contentWidth}px`;
        this.$refs[this.my_ide_id].style.height = `${contentHeight}px`;
        this.editor.layout({ width: contentWidth, height: contentHeight });
      } catch (err) {}
    },
    keydownTestCode(e) {
      if (e.keyCode == 116) {
        e.preventDefault();
        this.testone();
      }
    },
    listenKeydown() {
      try {
        window.addEventListener("keydown", this.keydownTestCode);
      } catch (err) {}
    },
    removeListenKeydown() {
      try {
        window.removeEventListener("keydown", this.keydownTestCode);
      } catch (err) {}
    },
    scrollDown() {
      this.$nextTick(() => {
        try {
          window.scrollBy({
            top: 95.55,
            left: 0,
            behavior: "smooth",
          });
        } catch (err) {}
      });
    },
    loadCodeTips(now_language) {
      try {
        monaco.languages.registerCompletionItemProvider(now_language, {
          provideCompletionItems: (model, position) => {
            const suggestions_list = [];
            if (this.$SqsGlobal.language_tips[now_language]) {
              this.$SqsGlobal.language_tips[now_language].forEach((tem) => {
                suggestions_list.push({
                  label: tem,
                  kind: monaco.languages.CompletionItemKind.Text,
                  insertText: tem,
                  range: {
                    startLineNumber: position.lineNumber,
                    endLineNumber: position.lineNumber,
                    startColumn: position.column,
                    endColumn: position.column,
                  },
                });
              });
            }
            const inputText = model.getValueInRange({
              startLineNumber: position.lineNumber,
              endLineNumber: position.lineNumber,
              startColumn: 1,
              endColumn: position.column,
            });
            const filtered_suggestions = this.$SqsGlobal.language_tips[
              now_language
            ]
              .filter((tip) => {
                return tip.startsWith(inputText);
              })
              .map((tip) => {
                return {
                  label: tip,
                  kind: monaco.languages.CompletionItemKind.Text,
                  insertText: tip,
                  range: {
                    startLineNumber: position.lineNumber,
                    endLineNumber: position.lineNumber,
                    startColumn: 1,
                    endColumn: position.column,
                  },
                };
              });
            return {
              suggestions: filtered_suggestions,
            };
          },
        });
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
    save(is_now = false) {
      if (!this.editor) {
        return;
      }
      if (this.iscloudfile) {
        try {
          this.$emit("upCodeFile", this.editor.getValue());
        } catch (err) {}
        return;
      }
      let problem_name = this.problem_data?.problemName ?? "";
      if (this.timer_id) {
        clearTimeout(this.timer_id);
        this.timer_id = null;
      }
      if (is_now) {
        this.my_code = this.editor.getValue();
        try {
          window.localStorage.setItem(
            "idecode" + problem_name + this.my_language,
            this.my_code
          );
        } catch (err) {}
        return;
      }
      this.timer_id = setTimeout(() => {
        this.my_code = this.editor.getValue();
        try {
          window.localStorage.setItem(
            "idecode" + problem_name + this.my_language,
            this.my_code
          );
        } catch (err) {}
      }, 1000);
    },
    ChangeCacheLanguage() {
      this.editor &&
        this.editor.updateOptions({
          language: this.my_language,
        });
      this.editor &&
        monaco.editor.setModelLanguage(
          this.editor.getModel(),
          this.my_language
        );
      try {
        !this.iscloudfile &&
          window.localStorage.setItem("language", this.my_language);
      } catch (err) {}
      this.initcode();
      this.loadCodeTips(this.my_language);
    },
    ChangeCacheTheme() {
      this.editor &&
        this.editor.updateOptions({
          theme: this.usertheme,
        });
      try {
        window.localStorage.setItem("theme", this.usertheme);
      } catch (err) {}
    },
    useAcCode() {
      this.my_code = this.has_ac_code;
      this.editor.setValue(this.my_code);
      this.save();
    },
    towrite() {
      this.$router.push({
        path: "/write",
        query: {
          problemName: urlencode(
            this.problem_data?.problemName +
              "(" +
              this.$SqsGlobal.language_map[this.my_language] +
              ")",
            "gbk"
          ),
          problemId: urlencode(this.problem_data?.id, "gbk"),
          problemContent: urlencode(this.problem_data?.problemContent, "gbk"),
          code: urlencode(this.my_code, "gbk"),
          language: urlencode(this.my_language, "gbk"),
        },
      });
    },
    async testQueryOne(code_id) {
      if (!this.editor || !this.test_query_one_can_next || !code_id) {
        return;
      }
      this.test_query_one_can_next = false;
      this.save();
      this.isshow = false;
      this.istest = true;
      this.isac = false;
      this.iswrong = false;
      this.my_code = this.editor.getValue();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Webcode/queryCode",
        portType: {
          process: "8791",
        },
        data: {
          code_id: code_id,
        },
      }).catch((t) => {
        this.isup = false;
        this.istest = false;
        this.test_query_one_can_next = true;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.test_query_one_can_next = true;
      if (res?.code == 0) {
        // code为0
        // 等待中
        return;
      }
      try {
        clearInterval(this.up_timer);
        this.up_timer = null;
      } catch (err) {}
      this.isup = false;
      this.istest = false;
      this.usetime = res.usetime;
      this.usememory = res.usememory;
      this.iswrong = true;
      this.wrong = res.result;
      if (res?.code == 1) {
        this.istestres = true;
      } else {
        this.istestres = false;
      }
      this.isshow = true;
      this.istest = false;
      this.scrollDown();
    },
    async testone() {
      if (!this.editor || this.isup || this.istest) {
        return;
      }
      this.save();
      this.isshow = false;
      this.istest = true;
      this.isac = false;
      this.iswrong = false;
      this.my_code = this.editor.getValue();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Webcode/runCode",
        portType: {
          process: "8791",
        },
        data: {
          code: this.my_code,
          testin: this.local_testin,
          userlanguage: this.$SqsGlobal.language_map[this.my_language],
        },
      }).catch((t) => {
        try {
          clearInterval(this.up_timer);
          this.up_timer = null;
        } catch (err) {}
        this.isup = false;
        this.istest = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        try {
          clearInterval(this.up_timer);
          this.up_timer = null;
        } catch (err) {}
        this.code_id = res?.code_id;
        this.up_timer = setInterval(() => {
          this.testQueryOne(this.code_id);
        }, 1000);
      } else {
        this.isup = false;
        this.istest = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async submitQueryOne(code_id) {
      if (!this.editor || !code_id) {
        return;
      }
      this.isshow = false;
      this.istest = true;
      this.isac = false;
      this.iswrong = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Ojjudge/queryCode",
        portType: {
          process: "8791",
        },
        data: {
          code_id: code_id,
        },
      }).catch((t) => {
        this.isup = false;
        this.istest = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 0) {
        // code为0
        // 等待中
        return;
      }
      try {
        clearInterval(this.up_timer);
        this.up_timer = null;
      } catch (err) {}
      this.isup = false;
      this.istest = false;
      this.usetime = res.usetime;
      this.usememory = res.usememory;
      if (res?.code == 1) {
        this.ac = res.result;
        this.isac = true;
        this.iswrong = false;
        this.wrong = "";
      } else {
        this.isac = false;
        this.iswrong = true;
        this.ac = "";
        this.wrong = res.result;
      }
      this.isshow = true;
      this.isup = false;
      this.scrollDown();
    },
    async submit() {
      if (!this.editor || this.isup || this.istest) {
        return;
      }
      this.save();
      this.isshow = false;
      this.isup = true;
      this.isac = false;
      this.iswrong = false;
      this.istestres = false;
      this.my_code = this.editor.getValue();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Ojjudge/runCode",
        portType: {
          process: "8790",
        },
        data: {
          problem_id: this.problem_data?.id,
          code: this.my_code,
          userlanguage: this.$SqsGlobal.language_map[this.my_language],
          contest_id: this.contest_id,
        },
      }).catch((t) => {
        try {
          clearInterval(this.up_timer);
          this.up_timer = null;
        } catch (err) {}
        this.isup = false;
        this.istest = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        this.code_id = res?.code_id;
        try {
          clearInterval(this.up_timer);
          this.up_timer = null;
        } catch (err) {}
        this.up_timer = setInterval(() => {
          this.submitQueryOne(this.code_id);
        }, 1000);
      } else {
        this.isup = false;
        this.istest = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    reset() {
      this.$alert("还原模板将覆盖当前代码！请谨慎操作！", "提示", {
        confirmButtonText: "确定",
        type: "warning",
      })
        .then(() => {
          try {
            clearInterval(this.up_timer);
            this.up_timer = null;
          } catch (err) {}
          if (!this.editor) {
            return;
          }
          this.istestres = false;
          this.isshow = false;
          this.istest = false;
          this.usetime = 0;
          this.usememory = 0;
          this.local_testin = "";
          this.ac = "";
          this.wrong = "";
          this.isac = false;
          this.isup = false;
          if (this.my_language == "c") {
            this.my_code = this.$SqsGlobal.c;
            this.editor.setValue(this.$SqsGlobal.c);
          } else if (this.my_language == "cpp") {
            this.my_code = this.$SqsGlobal.cpp;
            this.editor.setValue(this.$SqsGlobal.cpp);
          } else if (this.my_language == "java") {
            this.my_code = this.$SqsGlobal.java;
            this.editor.setValue(this.$SqsGlobal.java);
          } else if (this.my_language == "python") {
            this.my_code = this.$SqsGlobal.python;
            this.editor.setValue(this.$SqsGlobal.python);
          } else if (this.my_language == "go") {
            this.my_code = this.$SqsGlobal.go;
            this.editor.setValue(this.$SqsGlobal.go);
          } else if (this.my_language == "php") {
            this.my_code = this.$SqsGlobal.php;
            this.editor.setValue(this.$SqsGlobal.php);
          } else if (this.my_language == "javascript") {
            this.my_code = this.$SqsGlobal.javascript;
            this.editor.setValue(this.$SqsGlobal.javascript);
          } else if (this.my_language == "rust") {
            this.my_code = this.$SqsGlobal.rust;
            this.editor.setValue(this.$SqsGlobal.rust);
          } else if (this.my_language == "csharp") {
            this.my_code = this.$SqsGlobal.csharp;
            this.editor.setValue(this.$SqsGlobal.csharp);
          } else if (this.my_language == "typescript") {
            this.my_code = this.$SqsGlobal.typescript;
            this.editor.setValue(this.$SqsGlobal.typescript);
          } else if (this.my_language == "ruby") {
            this.my_code = this.$SqsGlobal.ruby;
            this.editor.setValue(this.$SqsGlobal.ruby);
          } else {
            this.my_language = "cpp";
            this.my_code = this.$SqsGlobal.cpp;
            this.editor.setValue(this.$SqsGlobal.cpp);
          }
          this.save();
        })
        .catch(() => {});
    },
    async initcode() {
      if (!this.editor) {
        return;
      }
      if (!this.problem_data?.id) {
        let problem_name = this.problem_data?.problemName ?? "";
        try {
          this.my_code = window.localStorage.getItem(
            "idecode" + problem_name + this.my_language
          );
        } catch (err) {}
        if (!this.my_code) {
          return;
        }
        this.editor.setValue(this.my_code);
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/lookProblemMySolveCode",
        portType: {
          process: "8795",
        },
        data: {
          problem_id: this.problem_data?.id,
          contest_id: this.contest_id,
          language: this.$SqsGlobal.language_map[this.my_language],
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1 && res?.data != undefined && res?.data != "") {
        this.is_has_ac_code = true;
        this.has_ac_code = res?.data;
      } else {
        this.is_has_ac_code = false;
        this.has_ac_code = "";
        let cache_code = "";
        let problem_name = this.problem_data?.problemName ?? "";
        try {
          cache_code = window.localStorage.getItem(
            "idecode" + problem_name + this.my_language
          );
        } catch (err) {}
        if (!cache_code) {
          return;
        }
        this.my_code = cache_code;
        this.editor.setValue(this.my_code);
        this.save();
      }
    },
  },
};
</script>
