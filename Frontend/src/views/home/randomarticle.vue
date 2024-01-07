/* 
随机文章
 */

<template>
  <div
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
    v-loading.lock="!loadfinish"
    element-loading-text="拼命加载中"
    element-loading-spinner="el-icon-loading"
    element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"
  >
    <div style="height: 1rem"></div>
    <div v-show="loadfinish" class="shadow main-center-box-content">
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="border-width: 0rem">
          <div>
            <div style="height: 2rem"></div>
            <div
              style="
                border-width: 0rem;
                min-height: 3rem;
                text-align: left;
                margin-left: 1.38rem;
                font-weight: bold;
                font-size: 1.66rem;
                overflow: hidden;
              "
            >
              {{ tableData.name }}
            </div>
            <div style="height: 1rem"></div>
            <div>
              <div style="overflow: hidden; margin: 0rem 1.38rem">
                <img
                  class="animate"
                  :src="tableData.image"
                  style="
                    display: block;
                    width: 100%;
                    height: 26rem;
                    object-fit: cover;
                    text-align: center;
                  "
                />
              </div>
            </div>
            <div style="height: 1rem"></div>
            <div class="markdown-body">
              <mavon-editor
                class="md"
                :value="tableData.article"
                :codeStyle="prop.codeStyle"
                :toolbars="toolbars"
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
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/css/index.css";
export default {
  name: "randomarticle",
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
  activated() {
    this.loadfinish = false;
    this.getlist();
  },
  methods: {
    async getlist() {
      //获取文章
      const { data: res } = await this.$ajax
        .post("/Article/randomOneArticle")
        .catch((t) => {
          this.loadfinish = true;
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
          return;
        });
      this.loadfinish = true;
      this.tableData = res.data;
    },
  },
  data() {
    return {
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
      loadfinish: false,
      context: " ", //输入的数据
      image: "",
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
      tableData: {
        articlename: "",
        article: "",
        image: "",
      },
    };
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
/**
鼠标放上，图片变大
*/
.animate {
  -webkit-transition: all 1s ease; /* Safari and Chrome */
  -moz-transition: all 1s ease; /* Firefox */
  -o-transition: all 1s ease; /* Opera */
  -ms-transition: all 1s ease; /* IE 9 */
  transition: all 1s ease;
}
img:hover {
  transform-origin: center center;
  transform: scale(1.1, 1.1);
  -webkit-transform-origin: center center;
  -webkit-transform: scale(1.1, 1.1);
  -moz-transform-origin: center center;
  -moz-transform: scale(1.1, 1.1);
  -o-transform-origin: center center;
  -o-transform: scale(1.1, 1.1);
  -ms-transform-origin: center center;
  -ms-transform: scale(1.1, 1.1);
}
</style>