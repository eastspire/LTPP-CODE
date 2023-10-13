<template>
  <div
    @contextmenu.prevent=""
    @keyup.27="toback()"
    style="margin-left: auto; margin-right: auto"
  >
    <div
      class="shadow"
      style="
        background-color: rgba(41, 50, 56, 0.68);
        color: rgb(248, 249, 250);
        border-width: 0rem;
        border-color: rgba(41, 50, 56, 0.68)
        min-height: auto;
        width: 100%;
      "
    >
      <div style="height: 2.6rem"></div>
      <!-- 标题 -->
      <div style="text-align: center">
        <div
          style="
            font-size: 1.66rem;
            font-weight: bold;
            color: deeppink;
            overflow: hidden;
          "
        >
          {{ tableData.problemName }}
        </div>
      </div>
      <div>
        <div style="float: left">
          <el-button
            type="text"
            @click="tooneprocode()"
            class="el-icon-s-claim pulse-enter-active"
            style="font-size: 1.06rem; margin: 0rem 2rem; color: deepskyblue"
            >测评记录</el-button
          >
        </div>
        <div style="float: right">
          <el-button
            type="text"
            @click="tooneproblemsolve()"
            class="el-icon-s-opportunity pulse-enter-active"
            style="font-size: 1.06rem; margin: 0rem 2rem; color: deepskyblue"
            >去看题解-></el-button
          >
        </div>
      </div>
      <div style="clear: both"></div>
      <div class="markdown-body">
        <mavon-editor
          class="md"
          :toolbars="toolbars"
          :value="tableData.problemContent"
          :subfield="prop.subfield"
          :defaultOpen="prop.defaultOpen"
          :toolbarsFlag="prop.toolbarsFlag"
          :editable="prop.editable"
          :scrollStyle="prop.scrollStyle"
          :codeStyle="prop.codeStyle"
          :boxShadow="prop.boxShadow"
          :tabSize="prop.tabSize"
          :fontSize="prop.fontSize"
          :externalLink="externalLink"
          :toolbarsBackground="prop.toolbarsBackground"
          :previewBackground="prop.previewBackground"
          :editorBackground="prop.editorBackground"
          :xssOptions="whiteList"
          style="
            color: rgb(248, 249, 250);
            min-height: 0rem;
            height: auto;
            border-width: 0rem;
          "
        >
        </mavon-editor>
      </div>
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 0.8rem"></div>
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
            style="font-size: 1.06rem; font-weight: bold; color: chartreuse"
            type="text"
            icon="el-icon-edit"
            class="pulse-enter-active"
            @click="updateTestin()"
            >填入样例</el-button
          >
        </p>
        <pre style="font-size: 1.06rem">{{ tableData.problemCinTest }}</pre>
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
        <pre style="font-size: 1.06rem">{{ tableData.problemCoutTest }}</pre>

        <!-- 时间内存限制 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          时间/内存限制
        </p>
        <div>
          <el-tag
            class="pulse-enter-active"
            effect="dark"
            size="small"
            type="success"
            style="
              margin: 0rem 2rem 0rem 0rem;
              font-size: 1rem;
              font-weight: bold;
            "
            >时间限制：{{ tableData.Time }}MS</el-tag
          >
          <el-tag
            class="pulse-enter-active"
            effect="dark"
            size="small"
            type="success"
            style="font-size: 1rem; font-weight: bold"
            >内存限制：{{ tableData.Memory }}M</el-tag
          >
        </div>
        <!-- 标签 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          标签
        </p>
        <div>
          <el-tag
            class="pulse-enter-active"
            effect="dark"
            size="small"
            type="warning"
            style="
              margin: 0rem 2rem 0rem 0rem;
              font-size: 1rem;
              font-weight: bold;
            "
            >{{ tableData.problemLabe }}
          </el-tag>
        </div>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          通过率
        </p>
        <div>
          <el-tag
            class="pulse-enter-active"
            effect="dark"
            size="small"
            type="success"
            style="
              margin: 0rem 2rem 0rem 0rem;
              font-size: 1rem;
              font-weight: bold;
            "
            >{{ (tableData.ACpoint * 100).toFixed(0) }}%
          </el-tag>
        </div>
        <div v-if="show_ide">
          <Myide
            :contest_id="contestid"
            :problem_data="tableData"
            :testin="testin"
          ></Myide>
        </div>
      </div>
      <div style="height: 2rem"></div>
    </div>
  </div>
</template>
<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../updateCompoents/mavon-editor/dist/markdown/github-markdown.min.css";
import Myide from "../../components/myide.vue";

export default {
  name: "oneproblem",
  components: {
    Myide,
  },
  activated() {
    if (
      !(
        this.$route &&
        this.$route.query &&
        this.$route.query.path &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.$router.go(-1);
      return;
    }
    this.id = urlencode.decode(this.$route.query.path, "gbk"); //问题id
    this.contestid = urlencode.decode(this.$route.query.contest, "gbk"); //竞赛id
    this.getproblem(this.id);
    this.show_ide = true;
    this.$nextTick(() => {
      this.totop();
    });
  },
  deactivated() {
    this.show_ide = false;
  },
  computed: {
    prop() {
      let data = {
        subfield: false, // 单双栏模式
        defaultOpen: "preview", //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: false,
        toolbarsFlag: false, //工具栏
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
  data() {
    return {
      testin: "",
      show_ide: true,
      whiteList: false,
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      type: "算法",
      contestid: -1,
      id: -1,
      tableData: {
        id: 0,
        problemName: "加载中",
        problemLabe: "加载中",
        problemContent: "加载中",
        problemCinTest: "加载中",
        problemCoutTest: "加载中",
        ACNum: "加载中",
        ALLSubmitNum: "加载中",
        Time: "加载中",
        Memory: "加载中",
        problemFrom: "加载中",
        ACpoint: "加载中",
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
        imagelink: true, // 图片链接

        code: true, // code
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: false, // 沉浸式阅读
        /* 1.3.5 */
        undo: true, // 上一步
        trash: true, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
      userlanguage: "C++",
      userthemelanguage: "cpp",
    };
  },
  methods: {
    toback() {
      this.$router.go(-1);
    },
    updateTestin() {
      this.testin = 0;
      setTimeout(() => {
        this.$nextTick(() => {
          this.testin = this.tableData.problemCinTest;
        });
      }, 0);
    },
    tooneproblemsolve() {
      this.id &&
        this.$router.push({
          path: "/oneproblemsolve",
          query: {
            path: urlencode(this.id, "gbk"),
            contestid: urlencode(this.contestid, "gbk"),
          },
        });
    },
    tooneprocode() {
      this.id &&
        this.$router.push({
          path: "/problemcode",
          query: {
            path: urlencode(this.id, "gbk"),
            contestid: urlencode(this.contestid, "gbk"),
          },
        });
    },

    //获取题目内容
    async getproblem(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/lookOneProblem",
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
      });
      if (res.code == 1) {
        this.tableData = res.data;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
        return;
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