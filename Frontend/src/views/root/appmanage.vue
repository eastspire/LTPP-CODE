<!-- 我的APP -->
<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div class="ltpp-list-box">
        <div class="search shadow">
          <el-input
            style="font-size: 1.06rem"
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
      <el-row :gutter="12">
        <el-col :span="24">
          <div v-for="temtable in tableData" :key="temtable.index">
            <div
              @click="
                lookOneApp(temtable.id);
                updateclick = true;
              "
              class="pulse-enter-active shadow ltpp-list-box"
              style="
                border-width: 0rem;
                height: 8rem;
                overflow: hidden;
                width: 100%;
              "
            >
              <div>
                <div
                  style="
                    float: left;
                    height: 8rem;
                    overflow: hidden;
                    white-space: nowrap;
                  "
                >
                  <div class="tagdiv">
                    <el-tag effect="dark" type="danger" class="tag"
                      >应用名称</el-tag
                    >
                    <p
                      style="
                        float: left;
                        margin: 0.26rem 0.6rem 0rem 0.6rem;
                        width: 14rem;
                        height: 2rem;
                        color: deeppink;
                        font-weight: bold;
                        overflow: hidden;
                      "
                    >
                      {{ temtable.name }}
                    </p>
                  </div>
                  <div class="tagdiv">
                    <el-tag effect="dark" type="success" class="tag"
                      >发布者</el-tag
                    >
                    <p
                      style="
                        float: left;
                        margin: 0.26rem 0.6rem 0rem 0.6rem;
                        width: 10rem;
                        height: 2rem;
                        color: #67c23a;
                        font-weight: bold;
                      "
                    >
                      {{ temtable.user_name.substr(0, 10) }}
                    </p>
                  </div>

                  <div class="tagdiv">
                    <el-tag effect="dark" type="danger" class="tag"
                      >发布时间</el-tag
                    >
                    <p
                      style="
                        float: left;
                        margin: 0.26rem 0.6rem 0rem 0.6rem;
                        width: 12rem;
                        height: 2rem;
                        color: deeppink;
                        font-weight: bold;
                      "
                    >
                      {{ temtable.time }}
                    </p>
                  </div>

                  <div class="tagdiv">
                    <el-tag effect="dark" type="warning" class="tag"
                      >访问数</el-tag
                    >
                    <p
                      style="
                        float: left;
                        margin: 0.26rem 0.6rem 0rem 0.6rem;
                        width: 4rem;
                        height: 2rem;
                        color: #e6a23c;
                        font-weight: bold;
                      "
                    >
                      {{ temtable.opentimes }}
                    </p>
                  </div>
                </div>
              </div>
              <!-- 清除浮动 -->
              <div class="clear"></div>
            </div>
            <div style="height: 2rem"></div>
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
        </el-col>
      </el-row>
    </div>
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      title="更新应用"
      :visible.sync="updateclick"
    >
      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
        "
      >
        <i class="el-icon-picture"></i>
        APP应用图标URL地址
      </p>
      <el-input
        v-model.lazy="onedata.image"
        style="font-size: 1.06rem"
      ></el-input>

      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
        "
      >
        <i class="el-icon-s-custom"></i>
        应用名称
      </p>
      <el-input
        v-model.lazy="onedata.name"
        style="font-size: 1.06rem"
      ></el-input>

      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
        "
      >
        <i class="el-icon-warning"></i>
        应用页面URL
      </p>
      <el-input
        v-model.lazy="onedata.url"
        style="font-size: 1.06rem"
      ></el-input>
      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
        "
      >
        <i class="el-icon-warning"></i>
        应用介绍
      </p>
      <div style="background-color: var(--ltpp-main-color) !important">
        <mavon-editor
          ref="update_md"
          @imgAdd="$updateImgAdd"
          class="md"
          v-model.lazy="onedata.content"
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
      <div style="height: 1.6rem"></div>
      <div style="text-align: right">
        <el-button
          type="danger"
          @click="deleteApp()"
          style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
          width="auto"
          class="el-icon-delete"
        >
          删除</el-button
        >
        <el-button
          type="success"
          @click="update()"
          style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
          width="auto"
          class="el-icon-upload2"
        >
          更新</el-button
        >
        <el-button
          type="success"
          @click="toOneApp()"
          style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
          width="auto"
          class="el-icon-monitor"
        >
          打开</el-button
        >
        <el-button
          type="primary"
          @click="updateclick = false"
          width="auto"
          style="font-size: 1.06rem; font-weight: bold"
          class="el-icon-s-unfold"
        >
          关闭</el-button
        >
      </div>
    </el-dialog>

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
          <el-select v-model.lazy="form.region" placeholder="请选择链接类型">
            <el-option label="iframe标签" value="iframe"></el-option>
            <el-option label="url链接" value="url"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
        <el-button type="primary" @click="addVideoLink">确 定</el-button>
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
</template>

<script>
export default {
  name: "myappmamage",
  activated() {
    this.updateclick = false;
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
    this.page = 1;
    this.limit = 50;
    this.showone = false;
    this.issearch = false;
  },
  methods: {
    addVideoLink() {
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
      let subStart = this.$refs.add_md.d_value.substring(0, posStart);
      let subEnd = this.$refs.add_md.d_value.substring(
        posEnd,
        this.$refs.add_md.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.add_md.d_value = subStart + "\n" + linkFrame + "\n" + subEnd;
      // document.getElementsByClassName("v-note-edit")[0].scrollTop = posScroll;

      // 关闭对话框
      this.dialogFormVisible = false;
    },
    // 绑定@addImgAdd event
    async $addImgAdd(pos, $file) {
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
          this.$refs.add_md.$img2Url(pos, res?.data.url);
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
    updateVideoLink() {
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
      let subStart = this.$refs.update_md.d_value.substring(0, posStart);
      let subEnd = this.$refs.update_md.d_value.substring(
        posEnd,
        this.$refs.update_md.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.update_md.d_value =
        subStart + "\n" + linkFrame + "\n" + subEnd;
      // document.getElementsByClassName("v-note-edit")[0].scrollTop = posScroll;

      // 关闭对话框
      this.dialogFormVisible = false;
    },
    // 绑定@updateImgAdd event
    async $updateImgAdd(pos, $file) {
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
          this.$refs.update_md.$img2Url(pos, res?.data.url);
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
    initData() {
      this.tableData = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.app_list_data);
      }
      this.tableData = tem_list;
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
    async deleteApp(id) {
      this.$confirm("确定删除该应用吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/App/delete",
            portType: {
              process: "8794",
            },
            data: {
              delete_id: this.onedata.id,
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.$msg({
                  type: "success",
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
                this.updateclick = false;
              } else {
                this.$msg({
                  type: "error",
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              }
              this.search();
            })
            .catch((t) => {
              this.$msg({
                type: "error",
                message: t,
                duration: 1600,
                offset: 80,
              });
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
    async update() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/update",
        portType: {
          process: "8792",
        },
        data: {
          data: this.onedata,
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
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        this.updateclick = false;
        this.search();
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //获取应用应用列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/backLoadAllAppList",
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
      this.total = res.allnum;
      this.tableData = res?.data;
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
    //查找
    async keysearch() {
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/App/backAllAppKeySearch",
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
      this.total = res.allnum;
      if (!this.showone) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.showone = true;
      }
    },
    search() {
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = true;
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.showone = false;
      this.issearch = true;
      this.keysearch();
    },
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
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
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
      updateclick: false,
      onedata: {},
      lastkey: "",
      isseetip: true,
      name: "",
      issearch: false,
      showone: false,
      total: 0,
      limit: 50,
      page: 1,
      key: "",
      //应用数据
      tableData: [],
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
};
</script>


<style scoped>
.up {
  border-radius: 1rem;
  height: 100%;
  width: 100%;
  background-color: #cecfd1;
  box-shadow: 0 0 6px rgba(var(--ltpp-main-bk-color), 0.12);
  text-align: center;
  line-height: 40px;
  color: #008bb6;
}
.CardHeader {
  text-align: center;
  background-color: rgb(188, 199, 199);
}
.el-table .warning-row {
  background: oldlace;
}

.el-table .success-row {
  background: #f0f9eb;
}

.dialog-footer {
  text-align: center;
}

.tag {
  float: left;
  font-size: 1.06rem;
  font-weight: bold;
  overflow: hidden;
}
.tagdiv {
  float: left;
  margin-top: 3rem;
  margin-left: 2rem;
  margin-right: auto;
  overflow: hidden;
}
</style>
