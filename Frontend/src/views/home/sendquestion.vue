<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div class="shadow ltpp-list-box" style="border-width: 0rem">
      <div
        style="
          background-color: rgba(var(--ltpp-main-bk-color), 0);
          color: var(--ltpp-box-text-color);
          border-width: 0rem;
          border-color: rgba(var(--ltpp-main-bk-color), 0);
          height: auto;
          width: 100%;
        "
      >
        <div class="markdown-body">
          <mavon-editor
            ref="md"
            @imgAdd="$imgAdd"
            class="md"
            v-model.lazy="question_name"
            :toolbars="toolbars"
            :subfield="prop.subfield"
            :defaultOpen="prop.defaultOpen"
            :toolbarsFlag="prop.toolbarsFlag"
            :editable="prop.editable"
            :scrollStyle="prop.scrollStyle"
            :codeStyle="prop.codeStyle"
            :toolbarsBackground="prop.toolbarsBackground"
            :previewBackground="prop.previewBackground"
            :editorBackground="prop.editorBackground"
            :boxShadow="prop.boxShadow"
            :tabSize="prop.tabSize"
            :fontSize="prop.fontSize"
            :externalLink="externalLink"
            :xssOptions="whiteList"
            :style="`
                border-radius: 0rem;
                min-height:${$store.state.no_scroll_height - 188}vh;
                height: auto;
                border-width: 0rem;
              `"
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
            </template>
            <!-- 发布 -->
            <template v-slot:right-toolbar-after>
              <el-button
                v-if="issendfinish"
                type="text"
                aria-hidden="true"
                class="op-icon fa"
                style="color: deepskyblue !important"
                title="发布"
                @click="upQuestion()"
              >
                <i class="el-icon-s-promotion" />
              </el-button>
            </template>
          </mavon-editor>
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
        <div style="height: 4rem"></div>
      </div>
      <div style="height: 1rem"></div>
    </div>
  </div>
</template>

<script>
import "../../../public/md/markdown/github-markdown.min.css";

export default {
  name: "write",
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

  activated() {
    this.issendfinish = true;
    let question_name = window.localStorage.getItem("question_name");
    if (question_name != undefined && question_name && question_name != null) {
      this.question_name = question_name;
    }
    this.form.region = "url";
    this.question_name = window.localStorage.getItem("my_send_question") ?? "";
  },
  updated() {
    window.localStorage.setItem("my_send_question", this.question_name);
  },
  mounted() {
    // 切换页面时滚动条自动滚动到顶部
    window.scrollTo(0, 0);
  },
  updated() {
    window.localStorage.setItem("my_send_question", this.question_name);
  },
  data() {
    return {
      issendfinish: true,
      question_name: "",
      whiteList: false,
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
        imagelink: true, // 图片链接

        code: true, // code
        htmlcode: false, // 展示html源码
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: true, // 沉浸式阅读
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

    // 绑定@imgAdd event
    async $imgAdd(pos, $file) {
      // 第一步.将图片上传到服务器.
      let formdata = new FormData();
      formdata.append("file", $file);
      await this.$ajax({
        url: "/File/saveImage",
        method: "post",
        data: formdata,
        headers: { "Content-Type": "multipart/form-data" },
      })
        .then((res) => {
          // 第二步.将返回的url替换到文本原位置![...](0) -> ![...](url)
          // $vm.$img2Url 详情见本页末尾
          this.$refs.md.$img2Url(pos, res?.data.url);
        })
        .catch((t) => {
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
    },
    async upQuestion() {
      if (!this.issendfinish) {
        return;
      }
      let t1 = this.question_name;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "提交不能为空",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      this.issendfinish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Question/writeOneQuestion",
        portType: {
          process: "8792",
        },
        data: {
          question_name: this.question_name,
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
        window.localStorage.removeItem("my_send_question");
        this.question_name = "";
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
</style>