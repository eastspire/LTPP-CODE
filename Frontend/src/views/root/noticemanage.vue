<!-- 公告管理 -->
<template>
  <div
    class="no-select shadow ltpp-list-box"
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div class="ltpp-list-box">
      <div class="search shadow">
        <el-input
          style="font-size: 1.06rem"
          placeholder="请输入关键字"
          v-model.lazy="key"
          @keyup.enter.native="search()"
          ><el-button slot="append" icon="el-icon-search" @click="search()"
            >搜索</el-button
          ></el-input
        >
      </div>
    </div>
    <div style="height: 1.6rem"></div>
    <div style="color: azure; border-width: 0rem; height: auto; width: 100%">
      <div style="text-align: left; margin-left: 1rem">
        <el-button
          type="text"
          @click="
            content = '';
            isadd = true;
            isupdate = false;
          "
          class="el-icon-plus pulse-enter-active"
          style="font-size: 1.06rem; font-weight: bold; color: chartreuse"
          >添加公告</el-button
        >
      </div>
      <div :style="`min-height:${$store.state.no_scroll_height * 0.76}vh;`">
        <el-table
          :cell-style="cellStyle"
          :header-cell-style="{
            color: '#FFFFFF',
            'font-size': '1.06rem',
          }"
          :data="noticeList"
          style="width: 100%"
        >
          <el-table-column label="公告" width="666">
            <template slot-scope="scope">
              <el-tooltip
                class="item;"
                effect="dark"
                :content="'公告内容：' + scope.row.content"
                placement="right"
              >
                <span
                  class="my-span"
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.content.substr(0, 36) }}
                </span>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column label="公告发布时间" width="auto" align="center">
            <template slot-scope="scope">
              <span
                class="my-span"
                style="font-weight: bold; font-size: 1.06rem; color: #409eff"
              >
                {{ scope.row.time }}
              </span>
            </template>
          </el-table-column>

          <el-table-column label="操作" width="246" align="center">
            <template slot-scope="scope">
              <el-button
                class="pulse-enter-active"
                @click="
                  id = scope.row.id;
                  deleteid();
                "
                style="
                  margin: 0rem 4rem 0rem 0rem;
                  font-size: 1.06rem;
                  font-weight: bold;
                  color: red;
                "
                type="text"
                >删除
              </el-button>
              <el-button
                class="pulse-enter-active"
                @click="
                  id = scope.row.id;
                  isadd = false;
                  lookOneNotice();
                "
                style="
                  margin: 0rem 2rem 0rem 0rem;
                  font-size: 1.06rem;
                  font-weight: bold;
                  color: deeppink;
                "
                type="text"
                >更新
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>

    <!-- 更新对话框 -->
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      style="text-align: center; font-size: 2rem; font-weight: bold"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      @contextmenu.prevent.native="
        updateid();
        isadd = false;
        isupdate = false;
      "
      title=""
      :visible.sync="isupdate"
    >
      <div>
        <div style="text-align: left">
          <span
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            公告
          </span>
          <div class="markdown-body">
            <mavon-editor
              ref="md"
              @imgAdd="$imgAdd"
              class="md"
              v-model.lazy="content"
              :toolbars="toolbars"
              :subfield="prop.subfield"
              :defaultOpen="prop.defaultOpen"
              :toolbarsFlag="prop.toolbarsFlag"
              :editable="prop.editable && load_txt_finish"
              :scrollStyle="prop.scrollStyle"
              :codeStyle="prop.codeStyle"
              :toolbarsBackground="prop.toolbarsBackground"
              :previewBackground="prop.previewBackground"
              :editorBackground="prop.editorBackground"
              :boxShadow="prop.boxShadow"
              :tabSize="prop.tabSize"
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
              </template>
            </mavon-editor>
          </div>
          <div style="height: 1.06rem"></div>
        </div>
        <div style="float: left; text-align: left" v-show="isupdate">
          <el-button
            width="auto"
            size="small"
            style="font-size: 1.06rem; margin-right: 2rem"
            type="danger"
            class="pulse-enter-active"
            @click="
              updateid();
              isadd = false;
              isupdate = false;
            "
            >更新</el-button
          >
        </div>

        <div style="text-align: right">
          <el-button
            size="small"
            type="success"
            width="auto"
            style="margin-right: 2rem; font-size: 1.06rem"
            class="pulse-enter-active"
            @click="
              isadd = false;
              isupdate = false;
            "
            >取消</el-button
          >
        </div>
      </div>
    </el-dialog>

    <!-- 添加对话框 -->
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      style="text-align: center; font-size: 2rem; font-weight: bold"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      @contextmenu.prevent.native="
        addid();
        isupdate = false;
        isadd = false;
      "
      title=""
      :visible.sync="isadd"
    >
      <div>
        <div style="text-align: left">
          <span
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            公告
          </span>
          <div class="markdown-body">
            <mavon-editor
              ref="md"
              @imgAdd="$imgAdd"
              class="md"
              v-model.lazy="content"
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
              </template>
            </mavon-editor>
          </div>
          <div style="height: 1.06rem"></div>
        </div>
        <div style="float: left; text-align: left" v-show="isadd">
          <el-button
            width="auto"
            size="small"
            style="font-size: 1.06rem; margin-left: 2rem"
            class="pulse-enter-active"
            type="danger"
            @click="
              addid();
              isupdate = false;
              isadd = false;
            "
            >添加</el-button
          >
        </div>
        <div style="text-align: right">
          <el-button
            size="small"
            type="success"
            width="auto"
            style="margin-right: 2rem; font-size: 1.06rem"
            class="pulse-enter-active"
            @click="
              isadd = false;
              isupdate = false;
            "
            >取消</el-button
          >
        </div>
      </div>
    </el-dialog>
    <!-- 插入视频链接的dialog提示框，表单对话框 -->
    <el-dialog
      :close-on-click-modal="false"
      title="插入视频资源"
      :append-to-body="true"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      :visible.sync="dialogFormVisible"
    >
      <el-form :model="form">
        <el-form-item label="视频链接" :label-width="formLabelWidth">
          <el-input v-model.lazy="form.link" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item label="链接类型" :label-width="formLabelWidth">
          <el-select v-model.lazy="form.region" placeholder="请选择链接类型">
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
      class="no-select"
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
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/css/index.css";
export default {
  name: "noticemanage",
  async activated() {
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
    this.load_txt_finish = false;
    this.search();
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
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
  data() {
    return {
      load_txt_finish: false,
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      lastkey: "",
      isseetip: true,
      isupdate: false,
      isadd: false,
      noticeList: [],
      total: 0,
      page: 1,
      limit: 50,
      key: "",
      id: 0,
      content: "",
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
    initData() {
      this.noticeList = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.notice_list_data);
      }
      this.noticeList = tem_list;
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
          this.$refs.md.$img2Url(pos, res?.data?.url);
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
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      return styleRes;
    },
    handleCurrentChange(val) {
      this.page = val;
      this.search();
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      this.search();
    },
    //获取公告列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/backLoadNotice",
        portType: {
          process: "8797",
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
      this.total = res.allnum;
      this.noticeList = res?.data;

      if (res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //查看公告
    async lookOneNotice() {
      if (!this.id) {
        return;
      }
      this.isupdate = true;
      this.content = "资源加载中！请耐心等待！";
      this.load_txt_finish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/backlookOneNotice",
        portType: {
          process: "8797",
        },
        data: {
          notice_id: this.id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.load_txt_finish = true;
        this.content = "资源加载失败！请稍后重试！";
      });
      this.load_txt_finish = true;
      if (res?.code == 1) {
        this.content = res?.data;
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //删除
    async deleteid() {
      if (!this.id) {
        return;
      }
      this.$confirm("确定删除该公告吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Notice/deleteOneNotice",
            portType: {
              process: "8797",
            },
            data: {
              delete_id: this.id,
            },
          }).then((res) => {
            if (res?.data.code == 1) {
              this.$msg({
                type: "success",
                message: res?.data.msg,
                duration: 1600,
                offset: 80,
              });
              this.search();
            } else {
              this.$msg({
                type: "error",
                message: res?.data.msg,
                duration: 1600,
                offset: 80,
              });
            }
            this.search();
          });
        })
        .catch(() => {
          this.$msg({
            type: "info",
            duration: 1600,
            offset: 80,
            message: "取消删除",
          });
        });
    },
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/backFindNotice",
        portType: {
          process: "8797",
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
      this.noticeList = res?.data;
      this.total = res.allnum;

      if (res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //搜索预处理
    search() {
      if (!this.key) {
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.keysearch();
    },

    //更新
    async updateid() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/updateOneNotice",
        portType: {
          process: "8797",
        },
        data: {
          tabledata: {
            id: this.id,
            content: this.content,
          },
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
      this.id = 0;
      this.content = "";
      this.search();
    },
    //添加
    async addid() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Notice/addOneNotice",
        portType: {
          process: "8797",
        },
        data: {
          tabledata: {
            content: this.content,
          },
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
      this.id = 0;
      this.content = "";
      this.search();
    },
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
::v-deep .el-table,
::v-deep .el-table__expanded-cell {
  background-color: transparent !important;
}
/* 表格内背景颜色 */
::v-deep .el-table th,
::v-deep .el-table tr,
::v-deep .el-table td {
  background-color: transparent !important;
}
::v-deep .el-table__row > td {
  border: none !important;
}
::v-deep .el-table::before {
  height: 0px !important;
}
::v-deep .el-table__cell,
.is-leaf {
  border: none !important;
}
</style>