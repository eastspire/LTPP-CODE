<template>
  <div>
    <div
      @contextmenu.prevent=""
      class="no-select"
      style="margin-left: auto; margin-right: auto"
    >
      <div>
        <el-carousel
          type="card"
          v-if="
            photoList &&
            photoList != undefined &&
            photoList != null &&
            photoList.length > 0
          "
        >
          <el-carousel-item
            v-for="tem in photoList"
            :key="tem.index"
            style="overflow: hidden"
          >
            <img
              class="animate shadow"
              style="display: block; width: 100%; object-fit: cover"
              :src="linuxurl + tem"
            />
          </el-carousel-item>
        </el-carousel>
        <div
          style="height: 2.6rem"
          v-show="
            photoList &&
            photoList != undefined &&
            photoList != null &&
            photoList.length > 0
          "
        ></div>

        <!-- 短句 -->
        <div
          class="shadow ltpp-list-box"
          v-show="
            shortsentenceList &&
            shortsentenceList != undefined &&
            shortsentenceList != null &&
            shortsentenceList.length > 0
          "
          style="color: azure; width: 100%"
        >
          <div>
            <div style="text-align: center">
              <div style="height: 1rem"></div>
              <p
                style="
                  font-size: 1.4rem;
                  color: deeppink;
                  text-align: center;
                  margin-top: 1rem;
                "
                class="el-icon-s-opportunity"
              >
                一言
              </p>
            </div>
            <div style="text-align: right">
              <el-button
                type="text"
                style="
                  cursor: pointer;
                  font-size: 1rem;
                  color: #409eff;
                  margin: 0rem 1rem;
                "
                class="el-icon-refresh pulse-enter-active"
                @click="refreshshortsentencelist()"
              >
                换一批
              </el-button>
            </div>
          </div>

          <div style="height: 1rem"></div>
          <div
            style="overflow: auto"
            v-for="tem in shortsentenceList"
            :key="tem.index"
          >
            <div
              style="
                font-family: Consolas, Monaco, DejaVu Sans Mono, monospace;
                padding: 0.6rem 2.6rem;
                font-size: 1.06rem;
                color: var(--ltpp-box-text-color);
              "
            >
              {{ tem.hitokoto }}
            </div>
            <div
              style="
                font-family: Consolas, Monaco, DejaVu Sans Mono, monospace;
                text-align: right;
                font-size: 1.06rem;
                padding: 0.6rem 2.6rem;
                color: var(--ltpp-box-text-color);
              "
            >
              {{ tem.from }}
            </div>
          </div>
          <div style="height: 1rem"></div>
        </div>
        <div
          v-show="
            shortsentenceList &&
            shortsentenceList != undefined &&
            shortsentenceList != null &&
            shortsentenceList.length > 0
          "
          style="height: 2.6rem"
        ></div>
        <!-- 公告 -->
        <div
          class="shadow ltpp-list-box"
          v-if="notice && notice != undefined && notice != null"
          style="text-align: center"
        >
          <div style="height: 1rem"></div>
          <p
            class="el-icon-s-order"
            style="
              font-size: 1.4rem;
              color: deeppink;
              text-align: center;
              background-color: rgba(var(--ltpp-main-bk-color), 0);
              margin-top: 1rem;
            "
          >
            公告
          </p>
          <div class="markdown-body" style="padding: 1rem 1rem">
            <mavon-editor
              class="md"
              :codeStyle="prop.codeStyle"
              :toolbars="toolbars"
              :value="notice.content"
              :subfield="prop.subfield"
              :defaultOpen="prop.defaultOpen"
              :toolbarsFlag="prop.toolbarsFlag"
              :ishljs="true"
              :editable="prop.editable"
              :scrollStyle="prop.scrollStyle"
              :boxShadow="prop.boxShadow"
              :tabSize="prop.tabSize"
              :fontSize="prop.fontSize"
              :externalLink="externalLink"
              :xssOptions="whiteList"
              style="
                color: #ebeef5;
                min-height: 0rem;
                height: auto;
                border-width: 0rem;
              "
            >
            </mavon-editor>
          </div>
          <div
            style="
              font-family: Consolas, Monaco, DejaVu Sans Mono, monospace;
              padding: 0.6rem 2.6rem;
              text-align: right;
              font-size: 1rem;
              color: var(--ltpp-box-text-color);
              background-color: rgba(var(--ltpp-main-bk-color), 0);
            "
          >
            {{ notice.time }}
          </div>
          <div style="clear: both"></div>
          <el-pagination
            v-show="allnum"
            background
            style="text-align: center"
            @current-change="handleCurrentChange"
            :current-page="page"
            :page-size="1"
            layout="total, prev, pager, next, jumper"
            :total="allnum"
          ></el-pagination>
          <div style="height: 3.6rem"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/css/index.css";

export default {
  name: "homelist",
  async created() {
    this.page = 1;
    this.allnum = 0;
    this.notice = {};
    this.shortsentenceList = this.$SqsGlobal.short_sentence_list;
    this.linuxurl = window.sessionStorage.getItem("linuxurl");
    if (!this.linuxurl) {
      await this.getlinuxurl();
    }
    this.getphotolist();
    this.getshortsentencelist();
    this.getNotice();
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
      whiteList: false,
      page: 1,
      allnum: 0,
      linuxurl: window?.location?.href,
      photoList: [],
      notice: {},
      shortsentenceList: [],
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      /* context:  */
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
    handleCurrentChange(val) {
      this.page = val;
      this.getNotice();
    },
    //获取短句列表
    async getshortsentencelist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Shortsentence/loadShortSentence",
        data: {
          page: 1,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.shortsentenceList = res?.data;
    },
    //刷新短句列表
    async refreshshortsentencelist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Shortsentence/refreshShortSentence",
        data: {
          page: 1,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.shortsentenceList = res?.data;
    },
    async getlinuxurl() {
      this.linuxurl = await this.getBackurl();
    },
    //获取图片列表
    async getphotolist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Photo/loadPhoto",
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.photoList = res?.data;
    },
    async getNotice() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/loadNotice",
        data: {
          page: this.page,
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
        this.notice = res?.data[0];
        this.allnum = res.allnum;
      }
    },
  },
};
</script>

<style  lang="less" scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
</style>