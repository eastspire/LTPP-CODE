<!-- APP列表页面 -->
<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div class="ltpp-list-box">
        <div class="search shadow">
          <el-input
            style="font-size: 1rem"
            placeholder="请输入需要搜索的应用名称"
            v-model.lazy="key"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div style="height: 1rem"></div>
      <div>
        <div
          style="
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            will-change: transform;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
          "
        >
          <div v-for="temtable in tableData" :key="temtable.index">
            <div
              v-loading.lock="!temtable || !temtable.id"
              element-loading-text="拼命加载中"
              element-loading-spinner="el-icon-loading"
              element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity)) !important"
              @click="
                lookOneApp(temtable.id);
                onedata = temtable;
                show_dialog = true;
              "
              style="
                cursor: pointer;
                margin: 1rem;
                width: 6rem;
                height: 6rem;
                background-color: rgba(
                  var(--ltpp-main-bk-color),
                  var(--ltpp-list-box-bk-opacity)
                ) !important;
              "
            >
              <div style="padding: 0rem !important">
                <div>
                  <img
                    onerror="/LTPPlogo.png"
                    v-if="temtable.image && reg.test(temtable.image)"
                    class="animate"
                    style="
                      width: 6rem;
                      height: 6rem;
                      object-fit: cover;
                      overflow: hidden;
                    "
                    :title="temtable.name"
                    alt=""
                    :src="temtable.image"
                  />
                </div>
              </div>
            </div>
            <div style="height: 1%"></div>
          </div>
        </div>
      </div>
      <!-- 清除浮动 -->
      <div class="clear"></div>
      <el-dialog
        :close-on-click-modal="true"
        :append-to-body="true"
        :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        :title="'【' + onedata.name + '】应用介绍' || ''"
        :visible.sync="show_dialog"
      >
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 0.5rem 1rem 0.5rem 0rem;
          "
        >
          发布者
        </p>
        <el-tag
          @click="
            show_dialog = false;
            touserpage(onedata.user_id);
          "
          size="small"
          effect="dark"
          type="success"
          style="font-size: 1rem; font-weight: bold; cursor: pointer"
          >{{ onedata.user_name }}
        </el-tag>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 0.5rem 1rem 0.5rem 0rem;
          "
        >
          发布时间
        </p>
        <el-tag
          size="small"
          effect="dark"
          type="primary"
          style="font-size: 1rem; font-weight: bold"
          >{{ onedata.time }}
        </el-tag>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 0.5rem 1rem 0.5rem 0rem;
          "
        >
          访问数
        </p>
        <el-tag
          size="small"
          effect="dark"
          type="warning"
          style="font-size: 1rem; font-weight: bold"
          >{{ onedata.opentimes }} 次
        </el-tag>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 0.5rem 1rem 0.5rem 0rem;
          "
        >
          应用介绍
        </p>
        <div
          style="background-color: rgb(var(--ltpp-main-bk-color)) !important"
        >
          <mavon-editor
            class="md"
            :codeStyle="prop.codeStyle"
            :toolbars="toolbars"
            :value="onedata.content || '<br>'"
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
            :xssOptions="xss_options"
            :stripIgnoreTagBody="stripIgnoreTagBody"
            style="color: #ebeef5; min-height: 0rem; height: auto"
          >
          </mavon-editor>
        </div>
        <br />
        <div style="text-align: right">
          <el-button
            class="el-icon-monitor"
            style="font-size: 1.06rem"
            @click="
              show_dialog = false;
              toOneApp();
            "
          >
            打开应用
          </el-button>
        </div>
      </el-dialog>
    </div>
    <div style="height: 3.4rem"></div>
    <el-pagination
      background
      v-show="total"
      style="text-align: center"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
      :current-page="page"
      :page-sizes="[1, 2, 6, 10, 20, 50]"
      :page-size="limit"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
    ></el-pagination>
    <div style="height: 3.4rem"></div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "app",
  async activated() {
    this.show_dialog = false;
    this.onedata = this.$SqsGlobal.app_list_data;
    this.isseetip = true;
    this.search();
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  async created() {
    this.isseetip = true;
    this.limit = 50;
    this.islock = false;
    this.issearch = false;
    this.initData();
    await this.getlist();
  },
  mounted() {
    this.$nextTick(() => {
      this.totop();
    });
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
    async lookOneApp(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/lookOneApp",
        portType: {
          process: "8792",
        },
        data: {
          id: id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res.code == 1) {
        this.onedata = res.data;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.search();
      } else {
        this.getlist();
      }
    },
    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (this.issearch) {
        this.search();
      } else {
        this.getlist();
      }
    },
    initData() {
      this.tableData = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.app_list_data);
      }
      this.tableData = tem_list;
    },
    tableRowClassName({ row, rowIndex }) {
      if (rowIndex === 1) {
        return "warning-row";
      } else if (rowIndex === 3) {
        return "success-row";
      }
      return "";
    },
    async keysearch() {
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/allAppKeySearch",
        portType: {
          process: "8792",
        },
        data: {
          key: this.key,
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.tableData = res?.data;
      this.total = res?.allnum;
    },
    search() {
      this.initData();
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        this.getlist();
        return;
      }
      this.issearch = true;
      this.keysearch();
    },

    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/loadAllAppList",
        portType: {
          process: "8792",
        },
        data: {
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.tableData = res?.data;
      this.total = res?.allnum;
    },
    //加载应用
    toOneApp() {
      try {
        this.$ajax({
          method: "post",
          url: "/App/addOpenTimes",
          portType: {
            process: "8794",
          },
          data: {
            id: this.onedata.id,
          },
        })
          .then((res) => {
            if (res?.data?.code == 1) {
              this.search();
            }
          })
          .catch((err) => {
            this.$msg({
              type: "error",
              message: t,
              duration: 1600,
              offset: 80,
            });
          });
      } catch (err) {}
      try {
        this.onedata.url &&
          this.onedata.url != this.$SqsGlobal.loading_tips &&
          window.open(this.onedata.url);
      } catch (err) {}
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
  data() {
    return {
      total: 0,
      page: 1,
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
      show_dialog: false,
      onedata: {},
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      limit: 50,
      lastkey: "",
      isseetip: true,
      issearch: false,
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
      },
      key: "",
      tableData: [],
    };
  },
};
</script>

<style scoped>
.text {
  font-size: 1rem;
  color: deepskyblue;
}

.el-carousel__item h3 {
  color: #475669;
  font-size: 14px;
  opacity: 0.75;
  line-height: 150px;
  margin: 0;
}

.el-carousel__item:nth-child(2n) {
  background-color: #99a9bf;
}

.el-carousel__item:nth-child(2n + 1) {
  background-color: #d3dce6;
}

/**
鼠标放上，图片变大
*/
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