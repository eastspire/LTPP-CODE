<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div class="shadow main-center-box-content">
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 0.8rem"></div>
        <!-- 标题 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 1rem 0.5rem 0rem;
          "
        >
          标题
        </p>
        <div>
          <el-input
            v-model.lazy="tableData.problemName"
            style="font-size: 1.06rem"
            placeholder="请输入标题"
          ></el-input>
        </div>

        <div style="height: 1rem"></div>
        <div class="markdown-body">
          <mavon-editor
            ref="md"
            class="md"
            @imgAdd="$imgAdd"
            :toolbars="toolbars"
            v-model.lazy="tableData.problemContent"
            :subfield="prop.subfield"
            :defaultOpen="prop.defaultOpen"
            :codeStyle="prop.codeStyle"
            :toolbarsFlag="prop.toolbarsFlag"
            :editable="prop.editable"
            :scrollStyle="prop.scrollStyle"
            :boxShadow="prop.boxShadow"
            :tabSize="prop.tabSize"
            :toolbarsBackground="prop.toolbarsBackground"
            :previewBackground="prop.previewBackground"
            :editorBackground="prop.editorBackground"
            :fontSize="prop.fontSize"
            :externalLink="externalLink"
            :xssOptions="xss_options"
            :stripIgnoreTagBody="stripIgnoreTagBody"
            style="min-height: 16rem; height: auto; border-width: 0rem"
          >
            <!-- 引用视频链接的自定义按钮 -->
            <template v-slot:left-toolbar-after>
              <!--点击按钮触发的事件是打开表单对话框-->
              <el-button
                type="text"
                @click="
                  form.region = 'url';
                  dialogFormVisible = true;
                "
                aria-hidden="true"
                class="op-icon fa"
                title="插入视频资源"
              >
                <i class="el-icon-video-camera-solid" />
              </el-button>
              <el-button
                type="text"
                @click="changeImageSaveType"
                aria-hidden="true"
                class="op-icon fa"
                title="切换图片保存方式"
              >
                <i
                  v-if="$store.state.image_use_remote"
                  class="el-icon-upload"
                />
                <i v-else class="el-icon-picture" />
              </el-button>
            </template>
          </mavon-editor>
        </div>
        <!-- 样例 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          测试样例（输入）&nbsp;&nbsp;&nbsp;&nbsp;
          <el-button
            class="pulse-enter-active"
            style="font-size: 1.06rem; font-weight: bold; color: chartreuse"
            type="text"
            icon="el-icon-edit"
            @click="updateTestin()"
            >填入样例</el-button
          >
        </p>
        <el-input
          type="textarea"
          :autosize="{ minRows: 1, maxRows: 20 }"
          placeholder="请输入输入样例"
          style="font-size: 1rem; overflow-x: hidden"
          v-model.lazy="tableData.problemCinTest"
        >
        </el-input>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          测试样例（输出）
        </p>
        <el-input
          type="textarea"
          :autosize="{ minRows: 1, maxRows: 20 }"
          placeholder="请输入输入样例"
          style="font-size: 1rem; overflow-x: hidden"
          v-model.lazy="tableData.problemCoutTest"
        >
        </el-input>

        <!-- 来源 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          来源
        </p>
        <el-input
          placeholder="请输入题目来源"
          style="font-size: 1.06rem; overflow-x: hidden"
          v-model.lazy="tableData.problemFrom"
        ></el-input>

        <!-- 类别 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          类别
        </p>
        <el-input
          placeholder="请输入题目类别"
          style="font-size: 1.06rem"
          v-model.lazy="tableData.problemLabe"
        ></el-input>
        <!-- 时间设置 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          时间限制（MS）
        </p>

        <el-input
          placeholder="请输入时间限制"
          style="font-size: 1.06rem"
          v-model.lazy="tableData.Time"
        >
        </el-input>
        <!-- 内存设置 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          内存限制（M）
        </p>

        <el-input
          placeholder="请输入内存限制"
          style="font-size: 1.06rem"
          v-model.lazy="tableData.Memory"
        ></el-input>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          解题思路
        </p>
        <el-input
          type="textarea"
          :autosize="{ minRows: 1, maxRows: 6666 }"
          placeholder="请输入解题思路"
          style="font-size: 1rem; overflow-x: hidden"
          v-model.lazy="tableData.think"
        ></el-input>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          AC代码（C++）
        </p>
        <el-input
          type="textarea"
          :autosize="{ minRows: 1, maxRows: 6666 }"
          placeholder="请输入AC代码（C++）"
          style="font-size: 1rem; overflow-x: hidden"
          v-model.lazy="tableData.code"
        >
        </el-input>
        <!-- 是否公开 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          是否公开
        </p>
        <el-switch
          style="margin: 1rem 1rem 0.5rem 1rem"
          v-model.lazy="tableData.public"
          :active-value="1"
          :inactive-value="0"
          active-text="公开"
          inactive-text="私密"
          active-color="#13ce66"
          inactive-color="#ff4949"
        >
        </el-switch>
        <div style="margin: 20px 0"></div>
        <!-- 测试样例 -->
        <div v-if="tableData.id">
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 0.5rem 0rem;
            "
          >
            测试点
          </p>
          <el-upload
            class="upload-demo"
            style="width: 100%; text-align: center"
            :headers="head"
            :auto-upload="true"
            drag
            ref="upload"
            :data="passparam"
            :action="linuxbkurl"
            :on-success="getupres"
            multiple
          >
            <i class="el-icon-upload"></i>
            <div class="el-upload__text">
              将文件拖到此处，或<em>点击上传</em>
            </div>
            <div class="el-upload__tip" slot="tip">
              只能上传zip格式文件，且不超过1个
            </div>
          </el-upload>
        </div>
        <div style="height: 4rem"></div>
        <div style="text-align: right">
          <el-button
            v-show="!is_download && tableData.id"
            @click="downloadTest()"
            style="margin: 0rem 4rem 0rem 0rem; font-size: 1.06rem; color: aqua"
            class="el-icon-s-claim pulse-enter-active"
            type="text"
            >下载样例
          </el-button>
          <el-button
            v-if="issendfinish"
            @click="tableData.id ? updateid() : addid()"
            style="
              margin: 0rem 4rem 0rem 0rem;
              font-size: 1.06rem;
              color: deeppink;
            "
            class="el-icon-s-promotion pulse-enter-active"
            type="text"
            >{{ tableData.id ? "更新题目" : "添加题目" }}
          </el-button>
          <el-button
            style="margin: 0rem 2rem 0rem 0rem; font-size: 1.06rem; color: aqua"
            class="el-icon-s-unfold pulse-enter-active"
            @click="toback()"
            type="text"
            >返回
          </el-button>
        </div>
        <div v-if="tableData.id">
          <el-divider></el-divider>
          <div style="height: 2rem"></div>
          <div v-if="show_ide && tableData.id">
            <Myide
              :contest_id="contestid"
              :problem_data="tableData"
              :testin="testin"
            ></Myide>
          </div>
        </div>

        <!-- 插入视频链接的dialog提示框，表单对话框 -->
        <el-dialog
          :close-on-click-modal="false"
          title="插入视频资源"
          :append-to-body="true"
          :visible.sync="dialogFormVisible"
          :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        >
          <el-form :model="form">
            <el-form-item label="视频链接" :label-width="formLabelWidth">
              <el-input v-model.lazy="form.link" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="链接类型" :label-width="formLabelWidth">
              <el-select
                v-model.lazy="form.region"
                placeholder="请选择链接类型"
              >
                <el-option label="iframe标签" value="iframe"></el-option>
                <el-option label="url链接" value="url"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
            <el-button type="primary" @click="videoLink">确 定</el-button>
            <el-button
              style="margin-left: 2rem"
              type="warning"
              @click="dialogFormVisible = false"
              >取 消</el-button
            >
          </div>
        </el-dialog>

        <!-- 错误提示框 -->
        <el-dialog
          :close-on-click-modal="false"
          title="提示"
          :visible.sync="dialogVisible"
          :append-to-body="true"
          width="30%"
          id="link-error"
        >
          <span class="my-span">视频链接格式错误，请重新确认后再输入！</span>
          <span slot="footer" class="dialog-footer my-span">
            <el-button type="primary" @click="dialogVisible = false"
              >确 定</el-button
            >
          </span>
        </el-dialog>
      </div>
      <div style="height: 3.6rem"></div>
    </div>
  </div>
</template>
<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
import Myide from "../../components/myide.vue";

export default {
  name: "oneproblemmanage",
  components: {
    Myide,
  },
  activated() {
    this.code = "";
    if (
      !(
        this.$route &&
        this.$route.query &&
        (this.$route.query.path || this.$route.query.path == "") &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.$router.go(-1);
      return;
    }
    this.show_ide = true;
    this.tableData = {
      id: "",
      code: "",
      think: "",
      problemName: "",
      problemContent: "",
      problemCinTest: "",
      problemCoutTest: "",
      Time: 1000,
      Memory: 128,
      problemFrom: "LTPP",
      problemLabe: "算法",
      public: 1,
    };

    this.head = {
      Authorization: "Bearer " + window.localStorage.getItem("authorization"),
      Key: window.localStorage.getItem("key"),
      Requestid: this.Base64Encode(new Date().getTime()),
    };
    this.requestid_timer = setInterval(() => {
      this.head.Requestid = this.Base64Encode(new Date().getTime());
    }, 1000);
    const tem_linuxbkurl = window.sessionStorage.getItem("linuxurl");
    if (!tem_linuxbkurl) {
      this.getlinuxbkurl();
    } else {
      this.linuxbkurl = tem_linuxbkurl + "/Testupload/savetest";
    }

    this.tableData.id = urlencode.decode(this.$route.query.path, "gbk"); //问题id
    this.contestid = urlencode.decode(this.$route.query.contestid, "gbk"); //竞赛id

    this.passparam = {
      id: this.tableData.id,
    };
    this.code = "";

    if (this.tableData.id && this.tableData.id != "") {
      //更新题目
      this.getproblem(this.tableData.id);
    } else {
      //添加题目
      this.tableData.problemContent = `<h4>题目内容</h4>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n\n<h4>输入说明</h4>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n\n<h4>输出说明</h4>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n\n<h5>输入样例1</h5>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n\n<h5>输出样例1</h5>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n\n<h4>提示</h4>\n<pre style="background-color:rgba(0,0,0,0);font-size:1.06rem;color:deeppink;padding:0rem;margin:0rem;white-space:pre-wrap;">\n\n</pre>\n`;
      this.tableData.problemName = "";
      this.tableData.problemCinTest = "";
      this.tableData.problemCoutTest = "";
      this.tableData.problemFrom = "LTPP";
      this.tableData.Time = "1000";
      this.tableData.Memory = "128";
      this.tableData.public = "0";
    }
    this.form.region = "url";
    this.$nextTick(() => {
      this.totop();
    });
  },
  computed: {
    prop() {
      let data = {
        subfield: false, // 单双栏模式
        defaultOpen: "edit", //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: true,
        toolbarsFlag: true, //工具栏
        scrollStyle: true,
        codeStyle: "atom-one-dark",
        boxShadow: false,
        ishljs: true,
        tabSize: 4,
        toolbarsBackground: "rgba(0,0,0,0)",
        editorBackground: "rgba(0,0,0,0)",
        previewBackground: "rgba(0,0,0,0)",
        fontSize: "1.06rem",
        navigation: false,
      };
      return data;
    },
  },
  deactivated() {
    this.show_ide = false;
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
  },
  data() {
    return {
      requestid_timer: null,
      issendfinish: true,
      show_ide: true,
      testin: "",
      is_download: false,
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      linuxbkurl: window?.location?.href,
      head: {},
      passparam: {
        id: "",
      },
      type: "",
      contestid: "",
      code: "",
      tableData: {
        id: "",
        problemName: "",
        problemContent: "",
        problemCinTest: "",
        problemCoutTest: "",
        Time: 1000,
        Memory: 128,
        problemFrom: "LTPP",
        problemLabe: "算法",
        public: 1,
      },
      /* context:  '',//输入的数据 */
      toolbars: {
        bold: true, // 粗体
        italic: true, // 斜体
        header: true, // 标题
        underline: true, // 下划线
        mark: true, // 标记
        superscript: true, // 上角标
        quote: true, // 引用
        ol: true, // 有序列表
        link: true, // 链接
        imagelink: false, // 图片链接

        code: true, // code
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: true, // 沉浸式阅读
        htmlcode: false, // 展示html源码
        /* 1.3.5 */
        undo: true, // 上一步
        trash: false, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
    };
  },
  methods: {
    updateTestin() {
      this.testin = 0;
      setTimeout(() => {
        this.$nextTick(() => {
          this.testin = this.tableData.problemCinTest;
        });
      }, 0);
    },
    async downloadTest() {
      if (this.is_download) {
        return;
      }
      this.$msg({
        type: "success",
        message: "开始下载",
        duration: 1600,
        offset: 80,
      });
      this.is_download = true;
      await this.$ajax({
        method: "post",
        url: "/Oj/downloadTest",
        responseType: "blob",
        headers: {
          "Content-Type": "application/json; application/octet-stream;",
        },
        data: {
          problem_id: this.tableData.id,
        },
      })
        .then((res) => {
          let reslastname = ".zip";
          let Name = this.tableData.problemName + reslastname;
          if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            const blob = new Blob([res?.data], {
              type: "application/zip",
            });
            window.navigator.msSaveOrOpenBlob(blob, Name);
          } else {
            /* 火狐谷歌的文件下载方式 */
            const blob = new Blob([res?.data], {
              type: "application/zip",
            });
            let url = window.URL.createObjectURL(blob);
            const link = document.createElement("a"); // 创建a标签
            link.href = url;
            link.download = Name; // 重命名文件
            link.click();
            URL.revokeObjectURL(url); // 释放内存
          }
          setTimeout(() => {
            this.$msg({
              type: "success",
              message: "下载完成",
              duration: 1600,
              offset: 80,
            });
            this.is_download = false;
          }, 66);
        })
        .catch((t) => {
          setTimeout(() => {
            this.$msg({
              type: "error",
              message: t,
              duration: 1600,
              offset: 80,
            });
            this.is_download = false;
          }, 66);
        });
    },
    ChangeCacheTheme() {
      window.localStorage.setItem("Theme", this.usertheme);
    },
    ChangeCacheLanguage() {
      window.localStorage.setItem("Language", this.userlanguage);
    },
    videoLink() {
      // 准备链接模板
      let linkFrame = "";
      if (this.form.region == "") {
        this.form.region = "url";
      }
      // 创建一个div盒子，为提取src做准备
      let box = document.createElement("div");
      // 将原始链接插入到盒子中

      box.innerHTML = this.form.link;
      // 判断不同的视频原链接类型
      if (this.form.region == "url") {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><video style="height:46vh; width: 100%" controls controlslist="nodownload"><source src="`;
        let linkFrameEnd = `" type="video/mp4" /></video></div>`;

        linkFrame = linkFrameStart + this.form.link + linkFrameEnd;
      } else if (
        this.form.region == "iframe" &&
        box.getElementsByTagName("iframe").length > 0
      ) {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><iframe height="${
          (window.innerHeight - 240) / 2 + "px"
        }" width="80%" src="`;
        let linkFrameEnd = `" allowfullscreen="true" scrolling="no" border="0" frameborder="no" framespacing="0" style="border-width: 0rem; min-height: 31.2rem;"></iframe></div>`;

        // 从iframe标签中提取src属性
        linkFrame =
          linkFrameStart +
          box.getElementsByTagName("iframe")[0].getAttribute("src") +
          linkFrameEnd;
      } else {
        // 原始链接格式错误时弹出错误提示
        this.dialogFormVisible = false;
        this.dialogVisible = true;
      }
      // 复原表单文本框内容
      this.form.link = "";

      // 获取文本域中当前光标起始位置、结束位置以及滚动条位置（滚动条位置我认为没有必要，如有需要可以自己取消注释）
      let textarea = document.getElementsByClassName("auto-textarea-input")[0];
      let posStart = textarea.selectionStart;
      let posEnd = textarea.selectionEnd;
      // let posScroll = document.getElementsByClassName("v-note-edit")[0].scrollTop;
      // 获取文本域中未选中的的前半部分和后半部分，以被选中内容起始和结束位置做分割点
      let subStart = this.$refs.md.d_value.substring(0, posStart);
      let subEnd = this.$refs.md.d_value.substring(
        posEnd,
        this.$refs.md.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.md.d_value = subStart + "\n" + linkFrame + "\n" + subEnd;
      // document.getElementsByClassName("v-note-edit")[0].scrollTop = posScroll;

      // 关闭对话框
      this.dialogFormVisible = false;
    },

    toback() {
      this.$router.go(-1);
    },

    //添加
    async addid() {
      if (!this.issendfinish) {
        return;
      }
      this.issendfinish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/addProblem",
        portType: {
          process: "8794",
        },
        data: {
          data: this.tableData,
        },
      }).catch((t) => {
        this.issendfinish = true;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.issendfinish = true;
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: "请添加测试样例",
          duration: 1600,
          offset: 80,
        });
        this.tableData = res?.data;
        this.passparam = {
          id: this.tableData.id,
        };
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 更新
    async updateid() {
      if (!this.issendfinish) {
        return;
      }
      this.issendfinish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/updateProblem",
        portType: {
          process: "8794",
        },
        data: {
          data: this.tableData,
        },
      }).catch((t) => {
        this.issendfinish = true;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.issendfinish = true;
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 样例上传回调
    getupres(response) {
      if (response && response.code && response.code != 1) {
        this.$msg({
          type: "error",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "success",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async getlinuxbkurl() {
      const res = await this.getBackurl();
      this.linuxbkurl = res + "/Testupload/saveTest";
    },
    // 绑定@imgAdd event
    $imgAdd(pos, $file) {
      this.imgAddMiddleware(pos, $file, "md");
    },
    //获取题目内容
    async getproblem(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/backLookOneProblem",
        portType: {
          process: "8794",
        },
        data: {
          problem_id: id,
          contest_id: this.contestid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      });
      if (res?.code == 0 || res?.code == 1) {
        this.tableData = res?.data;
      } else {
        this.$msg({
          type: "error",
          message: "该题目不存在，即将返回！",
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      }
    },
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
/* pre保持格式的同时实现自动换行 */
pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  font-size: 1.06rem;
  font-family: "Microsoft YaHei", 微软雅黑, "MicrosoftJhengHei", 华文细黑,
    STHeiti, MingLiu;
}
</style>