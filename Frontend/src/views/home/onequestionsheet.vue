<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div class="shadow main-center-box-content">
        <div>
          <div style="height: 0.8rem"></div>
          <div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              题单名称
            </p>
            <div
              style="
                border-radius: 1.6rem;
                font-size: 1.66rem;
                color: var(--ltpp-title-color);
                font-weight: bold;
                text-align: center;
                overflow: hidden;
              "
            >
              <span
                :class="`${
                  question_sheet_data.password
                    ? 'el-icon-lock'
                    : 'el-icon-trophy'
                }`"
              ></span>
              {{ question_sheet_data.name }}
            </div>
          </div>

          <div style="height: 1rem"></div>
          <p
            v-show="question_sheet_data.content"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 1.16rem;
            "
          >
            题单简介
          </p>
          <div style="margin: 1rem 0rem"></div>

          <div class="markdown-body">
            <mavon-editor
              class="md"
              :toolbars="toolbars"
              :value="question_sheet_data.content || '<br>'"
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
              :xssOptions="xss_options"
              :stripIgnoreTagBody="stripIgnoreTagBody"
              style="
                color: var(--ltpp-box-text-color);
                min-height: 0rem;
                height: auto;
                border-width: 0rem;
              "
            >
            </mavon-editor>
          </div>
          <div v-show="problemList && problemList.length">
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              题目列表
            </p>
            <div>
              <div
                v-for="(tem, index) in problemList"
                :key="index"
                style="padding: 0.6rem 0rem 0.6rem 2rem; will-change: transform"
              >
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'P' + (index + 1) + '：' + tem.problemName"
                  placement="right"
                >
                  <el-tag
                    class="pulse-enter-active"
                    effect="dark"
                    @click="toonepro(tem.id)"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      cursor: pointer;
                    "
                    >P{{ index + 1 + "：" + tem.problemName }}</el-tag
                  >
                </el-tooltip>
              </div>
            </div>
          </div>
          <div style="height: 1.6rem"></div>
          <div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              创建者:
              <el-tag
                class="pulse-enter-active"
                @click="touserpage(question_sheet_data.creator_id)"
                effect="dark"
                style="font-size: 1.06rem; cursor: pointer"
                size="medium"
              >
                {{ question_sheet_data.creator_name }}
              </el-tag>
            </p>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 1rem 0.5rem 1.16rem;
              "
            >
              题单创建时间：
              <el-tag
                effect="dark"
                type="success"
                style="font-size: 1.06rem"
                size="medium"
              >
                {{ question_sheet_data.time }}
              </el-tag>
            </p>
          </div>

          <div style="height: 2rem"></div>

          <div style="margin-left: 1.6rem; margin-right: 1.6rem">
            <div>
              <span
                style="font-size: 1.06rem; color: #f56c6c; cursor: auto"
                class="el-icon-user-solid"
              >
                题单人数：{{ question_sheet_data.people_num }}</span
              >
              <div style="height: 1rem"></div>
            </div>

            <div style="display: flex; justify-content: space-around">
              <el-button
                v-if="!isjoin && canclick"
                round
                @click="middlewareJoinQuestionSheet()"
                width="auto"
                class="el-icon-user-solid pulse-enter-active shadow"
              >
                加入题单</el-button
              >
              <el-button
                @click="
                  isSeeComment = false;
                  toback();
                "
                width="auto"
                round
                class="el-icon-s-unfold pulse-enter-active shadow"
              >
                【返回】</el-button
              >
            </div>
          </div>
          <div style="height: 3.6rem"></div>
        </div>
      </div>
      <el-dialog
        @contextmenu.prevent.native="isseepassword = false"
        :close-on-click-modal="false"
        width="30%"
        :append-to-body="true"
        title="题单密码"
        :visible.sync="isseepassword"
      >
        <el-input
          placeholder="请输入题单密码"
          style="font-size: 1.06rem"
          v-model.lazy="password"
          @keyup.enter.native="joinOneQuestionSheet()"
        >
          <el-button
            slot="append"
            icon="el-icon-success"
            @click="joinOneQuestionSheet()"
          >
            确定</el-button
          >
        </el-input>
      </el-dialog>
      <div style="height: 2rem"></div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/markdown/github-markdown.min.css";

export default {
  name: "onequestionsheet",
  async activated() {
    this.isseetip = true;
    this.showone_join_msg = false;
    this.isseepassword = false;
    this.password = "";
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
    this.isseetip = true;
    this.question_sheet_id = urlencode.decode(this.$route.query.path, "gbk");
    this.getdata();
    this.judgeisjoin();
  },
  destroyed() {
    this.isseetip = false;
  },
  deactivated() {
    this.can_show_time = false;
    this.problemList = [];
    this.isseetip = false;
  },
  data() {
    return {
      password: "",
      isseepassword: false,
      showone_join_msg: false,
      isHidePagenum: true,
      isseetip: true,
      canclick: true,
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      isjoin: false,
      problemList: [],
      question_sheet_id: "",
      question_sheet_data: {
        id: "",
        name: this.$SqsGlobal.loading_tips,
        time: this.$SqsGlobal.loading_tips,
        people_num: this.$SqsGlobal.loading_tips,
        creator_name: this.$SqsGlobal.loading_tips,
        password: true,
        creator_id: "",
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
    touserpage(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    async judgeisjoin() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/QuestionSheet/judgeIsJoinOneQuestionSheet",
        portType: {
          process: "8796",
        },
        data: {
          question_sheet_id: this.question_sheet_id,
        },
      }).catch((t) => {
        this.isjoin = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        this.isjoin = true;
      } else {
        this.isjoin = false;
      }
    },
    middlewareJoinQuestionSheet() {
      if (this.question_sheet_data?.password) {
        this.isseepassword = true;
      } else {
        this.joinOneQuestionSheet();
      }
    },
    toback() {
      this.$router.go(-1);
    },
    toonepro(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/oneproblem",
          query: {
            path: urlencode(id, "gbk"),
            contest: urlencode("", "gbk"),
          },
        });
    },
    async getproblemlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/QuestionSheet/lookOneQuestionSheetProblemList",
        portType: {
          process: "8796",
        },
        data: {
          question_sheet_id: this.question_sheet_id,
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
        this.problemList = res?.data;
      } else {
        !this.showone_join_msg &&
          (this.showone_join_msg = true) &&
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
      }
    },
    async getdata() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/QuestionSheet/lookOneQuestionSheetData",
        portType: {
          process: "8796",
        },
        data: {
          question_sheet_id: this.question_sheet_id,
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
        this.question_sheet_data = res?.data;
        this.getproblemlist();
        this.$nextTick(() => {
          this.totop();
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      }
    },
    async joinOneQuestionSheet() {
      this.canclick = false;
      this.isseepassword = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/QuestionSheet/joinOneQuestionSheet",
        portType: {
          process: "8796",
        },
        data: {
          question_sheet_id: this.question_sheet_id,
          password: this.password,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.canclick = true;
      this.password = "";
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1000,
          offset: 80,
        });
        this.isjoin = true;
        this.isseetip = true;
        await this.getdata();
        await this.judgeisjoin();
      } else {
        this.isjoin = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1000,
          offset: 80,
        });
      }
    },
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
};
</script>

<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";

.el-button {
  padding: 0.6rem 1rem !important;
  background-color: rgba(var(--ltpp-main-bk-color), 1) !important;
  color: rgba(var(--ltpp-light-color), 1) !important;
  font-size: 1.06rem;
}
</style>