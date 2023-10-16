<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 22:11:28
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-10-16 15:04:04
 * @FilePath: \LTPP-CODE\Frontend\src\components\myide.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
-->
<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div
      style="
        background-color: rgba(0, 0, 0, 0);
        border-width: 0rem;
        color: azure;
        border-color: rgba(0, 0, 0, 0);
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

        <div
          :ref="ide_base_id"
          :id="ide_base_id"
          style="
            height: 0px !important;
            width: 0px !important;
            display: none !important;
          "
        ></div>

        <!-- IDE -->
        <div style="height: 66vh; overflow: hidden">
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
            v-model.lazy="testin"
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
              usetime +
              " MS ，内存消耗：" +
              usememory +
              " KB ）"
            }}
          </p>
          <div v-show="isshow">
            <div v-show="iswrong" @dblclick="copy(wrong)">
              <div
                v-show="!istestres"
                style="
                  border-radius: 1rem;
                  padding: 1rem;
                  background-color: rgb(242, 222, 222);
                  border-color: rgb(242, 222, 222);
                  color: rgb(169, 68, 66);
                  min-height: 1rem;
                "
              >
                <pre>{{ wrong }}</pre>
              </div>
              <div
                v-show="istestres"
                style="
                  border-radius: 1rem;
                  padding: 1rem;
                  background-color: rgba(50, 112, 0, 0.88);
                  border-color: rgba(50, 112, 0, 0.88);
                  color: rgb(254, 255, 255);
                  min-height: 1rem;
                "
              >
                <pre>{{ wrong }}</pre>
              </div>
            </div>
            <div v-show="isac">
              <div
                style="
                  text-align: center;
                  border-radius: 1rem;
                  padding: 1rem;
                  background-color: #67c23a;
                  border-color: rgb(235, 204, 209);
                  color: #ffffff;
                "
              >
                <pre style="font-size: 1.6rem">{{ ac }}</pre>
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
  mounted() {
    let theme = window.localStorage.getItem("theme") ?? "vs-dark";
    if (theme) {
      this.usertheme = theme;
    }
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
    setTimeout(() => {
      this.$nextTick(() => {
        try {
          // 创建 Monaco Editor
          this.editor = monaco.editor.create(this.$refs[this.my_ide_id], {
            value: this.my_code,
            language: this.my_language,
            theme: this.usertheme,
            accessibilityHelpUrl: "",
            fontSize: 17,
            tabSize: 4,
            scrollBeyondLastLine: true,
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
            },
          });
        } catch (err) {}
        if (this.editor) {
          let layout_timer = setInterval(() => {
            try {
              this.editor.layout();
            } catch (err) {}
          }, 100);
          setTimeout(() => {
            try {
              clearInterval(layout_timer);
              layout_timer = null;
              this.editor.layout();
            } catch (err) {}
          }, 1000);
          try {
            let timer = setInterval(() => {
              if (this.problem_data?.id) {
                this.editor && this.initcode();
                clearInterval(timer);
                timer = null;
              }
            }, 360);
          } catch (err) {}
          this.onExit();
          this.editor.onDidChangeModelContent(this.save);
        }
        this.refreshBase();
      });
    }, 0);
  },
  destroyed() {
    try {
      this.save(true);
      this.ide_base && this.ide_base.dispose();
      this.editor && this.editor.dispose();
    } catch (err) {}
    try {
      clearInterval(this.up_timer);
      this.up_timer = null;
    } catch (err) {}
  },
  data() {
    return {
      code_id: "",
      up_timer: null,
      my_language: "cpp",
      ide_base_id: "my_ide_base",
      ide_base: null,
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
      usertheme: "vs-dark",
      my_code: "",
      wrong: "",
      ac: "",
      res: "",
      timer_id: null,
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
    save(is_now = false) {
      if (this.iscloudfile) {
        try {
          this.$emit("upCodeFile", this.editor.getValue());
        } catch (err) {}
        return;
      }
      let problem_name = this.problem_data?.problemName ?? "";
      if (!this.editor) {
        return;
      }
      if (this.timer_id) {
        clearTimeout(this.timer_id);
        this.timer_id = null;
      }
      if (is_now) {
        try {
          this.my_code = this.editor.getValue();
          window.localStorage.setItem(
            "idecode" + problem_name + this.my_language,
            this.my_code
          );
        } catch (err) {}
        return;
      }
      this.timer_id = setTimeout(() => {
        try {
          this.my_code = this.editor.getValue();
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
      !this.iscloudfile &&
        window.localStorage.setItem("language", this.my_language);
      this.initcode();
      this.refreshBase();
    },
    refreshBase() {
      try {
        this.ide_base && this.ide_base.dispose();
        this.ide_base = null;
        this.$nextTick(() => {
          setTimeout(() => {
            if (!this.$refs[this.ide_base_id]) {
              return;
            }
            this.ide_base = monaco.editor.create(this.$refs[this.ide_base_id], {
              value: this.$SqsGlobal.language_tips[this.my_language],
              language: this.my_language,
              contextmenu: false,
              automaticLayout: false,
            });
          }, 0);
        });
      } catch (err) {}
    },
    ChangeCacheTheme() {
      this.editor &&
        this.editor.updateOptions({
          theme: this.usertheme,
        });
      window.localStorage.setItem("theme", this.usertheme);
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
      if (!this.editor || !code_id) {
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
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (!res.code) {
        // code为0
        // 等待中
        return;
      }
      try {
        clearInterval(this.up_timer);
        this.up_timer = null;
      } catch (err) {}
      this.deleteCode(code_id);
      this.isup = false;
      this.istest = false;
      this.usetime = res.usetime;
      this.usememory = res.usememory;
      this.iswrong = true;
      this.wrong = res.result;
      if (res.code == 1) {
        this.istestres = true;
      } else {
        this.istestres = false;
      }
      this.isshow = true;
      this.istest = false;
    },
    async testone() {
      if (!this.editor) {
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
          testin: this.testin,
          userlanguage: this.$SqsGlobal.language_map[this.my_language],
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
      if (res.code == 1) {
        this.code_id = res.code_id;
        this.up_timer = setInterval(() => {
          this.testQueryOne(this.code_id);
        }, 1000);
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async deleteCode(code_id) {
      this.$ajax({
        method: "post",
        url: "/Webcode/deleteCode",
        portType: {
          process: "8791",
        },
        data: {
          code_id: code_id,
        },
      }).catch(() => {});
    },
    async submitQueryOne(code_id) {
      if (!this.editor || !code_id) {
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
      });
      if (!res.code) {
        // code为0
        // 等待中
        return;
      }
      try {
        clearInterval(this.up_timer);
        this.up_timer = null;
      } catch (err) {}
      this.deleteCode(code_id);
      this.isup = false;
      this.istest = false;
      this.usetime = res.usetime;
      this.usememory = res.usememory;
      if (res.code == 1) {
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
    },
    async submit() {
      if (!this.editor) {
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
      if (res.code == 1) {
        this.code_id = res.code_id;
        this.up_timer = setInterval(() => {
          this.submitQueryOne(this.code_id);
        }, 1000);
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
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
          if (!this.editor) {
            return;
          }
          this.istestres = false;
          this.isshow = false;
          this.istest = false;
          this.usetime = 0;
          this.usememory = 0;
          this.testin = "";
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
        this.my_code = window.localStorage.getItem(
          "idecode" + problem_name + this.my_language
        );
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
      if (res.code == 1 && res.data != undefined && res.data != "") {
        this.$alert("是否使用该题最近的AC代码？", "提示", {
          confirmButtonText: "确定",
          type: "warning",
        })
          .then(() => {
            this.my_code = res.data;
            this.editor.setValue(res.data);
            this.save();
          })
          .catch(() => {});
      } else {
        let problem_name = this.problem_data?.problemName ?? "";
        let cache_code = window.localStorage.getItem(
          "idecode" + problem_name + this.my_language
        );
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

<style scoped>
/* pre保持格式的同时实现自动换行 */
/* pre保持格式的同时实现自动换行 */
pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  font-size: 1.06rem;
  font-family: "Microsoft YaHei", 微软雅黑, "MicrosoftJhengHei", 华文细黑,
    STHeiti, MingLiu;
}
</style>
