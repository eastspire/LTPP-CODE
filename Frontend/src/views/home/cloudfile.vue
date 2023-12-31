<template>
  <div v-show="isseetip">
    <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
      <div
        class="shadow"
        :style="`
          background-color: rgba(41, 50, 56, 0.06);
          color: azure;
          border-width: 0rem;
          border-color: rgba(41, 50, 56, 0.06)
          height: auto;
          width: 100%;
          min-height:${$store.state.no_scroll_height}vh
        `"
      >
        <div>
          <div style="height: 1rem"></div>
          <p
            style="
              color: #dcdfe6;
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 1.6rem;
              float: left;
            "
          >
            我的云盘
          </p>
          <div>
            <div style="float: left">
              <el-button
                type="text"
                class="pulse-enter-active"
                icon="el-icon-caret-bottom"
                size="mini"
                style="
                  font-size: 1.06rem;
                  text-align: left;
                  padding: 1.25rem 1.25rem;
                  float: left;
                  color: chartreuse;
                "
                @click="downloadFile(filepath[filepath.length - 1])"
                >下载本页面文件</el-button
              >
              <el-button
                type="text"
                class="pulse-enter-active"
                icon="el-icon-circle-plus"
                size="mini"
                style="
                  font-size: 1.06rem;
                  text-align: left;
                  padding: 1.25rem 1.25rem;
                  float: left;
                  color: chartreuse;
                "
                @click="Isnew = true"
                >新建文件（夹）</el-button
              >
            </div>
            <div style="float: right">
              <el-button
                style="
                  font-size: 1.06rem;
                  font-weight: bold;
                  padding: 1.25rem 1.25rem;
                  color: chartreuse;
                "
                width="auto"
                type="text"
                class="el-icon-upload pulse-enter-active"
                @click="IsShowUp = true"
                >上传文件</el-button
              >
              <el-button
                v-show="
                  filepath.length > 0 && filepath[filepath.length - 1] != ''
                "
                style="
                  font-size: 1.06rem;
                  font-weight: bold;
                  padding: 1.25rem 2rem;
                  color: aqua;
                "
                width="auto"
                type="text"
                class="el-icon-s-unfold pulse-enter-active"
                @click="backfile()"
                >返回</el-button
              >
              <div style="clear: both"></div>
            </div>
          </div>
        </div>
        <div style="clear: both"></div>
        <div style="height: 0.8rem"></div>
        <div
          style="color: #dcdfe6; margin-left: 1.6rem; font-weight: bold"
          v-show="filepath.length > 0"
        >
          当前路径：{{
            filepath.length > 0 && filepath[filepath.length - 1] == ""
              ? "/"
              : get_dir_name(filepath[filepath.length - 1])
          }}
          <div style="height: 0.8rem"></div>
        </div>
        <p style="color: #dcdfe6; margin-left: 1.6rem; font-weight: bold">
          容量使用情况
        </p>
        <el-progress
          style="margin: 0.4rem 1.6rem"
          :text-inside="true"
          :percentage="file_percentage"
          status="success"
        ></el-progress>
        <div>
          <div
            style="
              margin-left: 1.6rem;
              margin-right: 1.6rem;
              will-change: transform;
            "
          >
            <div style="height: 0.8rem"></div>
            <div
              v-for="(tem, index) in list"
              class="shadow"
              :key="index"
              :style="`cursor: pointer; background-color:${
                index % 2 != 0 ? deepcolor : notdeepcolor
              };margin-top:0.46rem;`"
              @contextmenu.prevent="
                showdelete(
                  filepath[filepath.length - 1] + '/' + tem[0],
                  base64_decode(tem[0])
                )
              "
              @dblclick="
                tem[1] === 1
                  ? tolookfolder(filepath[filepath.length - 1] + '/' + tem[0])
                  : tolookcode(filepath[filepath.length - 1] + '/' + tem[0])
              "
            >
              <div>
                <div>
                  <div
                    :class="gitclass[tem[1] - 1]"
                    style="margin-left: 1rem"
                  ></div>
                  <div>
                    <el-tooltip
                      class="item;"
                      effect="dark"
                      :content="base64_decode(tem[0])"
                      placement="top"
                    >
                      <p
                        style="
                          float: left;
                          margin: 2rem 1rem 1rem 2rem;
                          font-size: 1.06rem;
                          color: deepskyblue;
                        "
                      >
                        {{ base64_decode(tem[0]).substr(0, 16) }}
                      </p>
                    </el-tooltip>
                  </div>

                  <div style="float: right">
                    <p
                      style="
                        float: left;
                        margin: 2rem 1rem 1rem 2rem;
                        font-size: 1.06rem;
                        color: deepskyblue;
                      "
                    >
                      修改时间：{{ tem[3] }}
                    </p>
                  </div>
                  <div style="float: right">
                    <p
                      style="
                        float: left;
                        margin: 2rem 1rem 1rem 2rem;
                        font-size: 1.06rem;
                        color: deeppink;
                      "
                    >
                      大小：{{ tem[2] }}
                    </p>
                  </div>
                </div>
              </div>
              <!-- 清除浮动 -->
              <div class="clear"></div>
            </div>

            <!-- 新建文件、文件夹 -->
            <div>
              <el-dialog
                width="30%"
                :append-to-body="true"
                :visible.sync="Isnew"
              >
                <p
                  style="
                    font-size: 1.06rem;
                    text-align: left;
                    font-weight: bold;
                    margin: 0rem 1rem 0.5rem 0rem;
                  "
                >
                  名称
                </p>
                <el-input
                  style="font-size: 1.06rem"
                  placeholder="请输入名称"
                  v-model.lazy="newname"
                  @keyup.enter.native="
                    newfile();
                    Isnew = false;
                    newname = '';
                  "
                >
                  <el-button
                    class="pulse-enter-active"
                    slot="append"
                    icon="el-icon-success"
                    @click="
                      newfile();
                      Isnew = false;
                      newname = '';
                    "
                    >确定</el-button
                  >
                </el-input>
                <div style="height: 2rem"></div>
              </el-dialog>
            </div>

            <!-- 文件预览 -->
            <div v-if="!iscloseFile">
              <el-dialog
                :width="
                  ($store.state.max_width / $store.state.now_width) * 100 + '%'
                "
                @contextmenu.prevent.native="closeFile"
                @closed="iscloseFile = true"
                :visible.sync="IsShowStaticFile"
                title="文件预览"
                :append-to-body="true"
              >
                <div>
                  <iframe
                    :style="`height:${
                      $store.state.no_scroll_height * 0.68
                    }vh; width: 100%`"
                    :src="ShowStaticFileUrl"
                    scrolling="no"
                    border="0"
                    frameborder="no"
                    framespacing="0"
                    allowfullscreen="true"
                  ></iframe>

                  <div style="text-align: left; margin-top: 0.6rem">
                    <el-button
                      class="pulse-enter-active"
                      type="text"
                      icon="el-icon-star-on"
                      size="mini"
                      width="auto"
                      style="
                        font-size: 1.06rem;
                        text-align: left;
                        color: #67c23a;
                        margin: 0rem 0rem;
                        float: left;
                      "
                      @click="downloadonefile(filename)"
                      >点击下载</el-button
                    >
                    <el-button
                      style="
                        color: #67c23a;
                        margin: 0rem 0rem 0rem 2.6rem;
                        font-size: 1.06rem;
                        float: right;
                      "
                      size="mini"
                      width="auto"
                      type="text"
                      class="el-icon-share pulse-enter-active"
                      @click="
                        copy(
                          linuxurl +
                            '/static/cloudfile/' +
                            $store.state.my_id +
                            filename
                        )
                      "
                      >分享</el-button
                    >
                    <el-button
                      style="
                        color: #67c23a;
                        margin: 0rem 0rem;
                        font-size: 1.06rem;
                        float: right;
                      "
                      size="mini"
                      width="auto"
                      type="text"
                      class="el-icon-s-opportunity pulse-enter-active"
                      @click="
                        iscloseFile = true;
                        lookfile();
                      "
                      >新页面打开</el-button
                    >
                  </div>
                  <div style="clear: both"></div>
                </div>
              </el-dialog>
            </div>

            <div>
              <el-dialog
                @contextmenu.prevent.native="savacode"
                :visible.sync="IsShowCode"
                title="文件内容"
                :width="
                  ($store.state.max_width / $store.state.now_width) * 100 + '%'
                "
                :append-to-body="true"
              >
                <div
                  style="`
                    border-radius: 0rem;
                    height: auto;
                    border-width: 0rem;
                  `"
                >
                  <div>
                    <div v-if="IsShowCode && !is_code_file">
                      <div class="markdown-body">
                        <mavon-editor
                          ref="md"
                          @imgAdd="$imgAdd"
                          @imgDel="$imgDel"
                          class="md"
                          v-model.lazy="code"
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
                        <!-- 插入视频链接的dialog提示框，表单对话框 -->
                        <el-dialog
                          title="插入视频资源"
                          :append-to-body="true"
                          :visible.sync="dialogFormVisible"
                          :width="
                            ($store.state.max_width / $store.state.now_width) *
                              100 +
                            '%'
                          "
                        >
                          <el-form :model="form">
                            <el-form-item
                              label="视频链接"
                              :label-width="formLabelWidth"
                            >
                              <el-input
                                v-model.lazy="form.link"
                                autocomplete="off"
                              ></el-input>
                            </el-form-item>
                            <el-form-item
                              label="链接类型"
                              :label-width="formLabelWidth"
                            >
                              <el-select
                                v-model.lazy="form.region"
                                placeholder="请选择链接类型"
                              >
                                <el-option
                                  label="iframe标签"
                                  value="iframe"
                                ></el-option>
                                <el-option
                                  label="url链接"
                                  value="url"
                                ></el-option>
                              </el-select>
                            </el-form-item>
                          </el-form>
                          <div slot="footer" class="dialog-footer">
                            <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
                            <el-button type="primary" @click="videoLink"
                              >确 定</el-button
                            >
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
                          title="提示"
                          :append-to-body="true"
                          :visible.sync="dialogVisible"
                          width="30%"
                          id="link-error"
                        >
                          <span class="my-span"
                            >视频链接格式错误，请重新确认后再输入！</span
                          >
                          <span slot="footer" class="dialog-footer my-span">
                            <el-button
                              type="primary"
                              @click="dialogVisible = false"
                              >确 定</el-button
                            >
                          </span>
                        </el-dialog>
                      </div>
                    </div>
                    <div v-if="IsShowCode && is_code_file">
                      <myide
                        :code="code"
                        :language="language"
                        @upCodeFile="updateIdeCode"
                        :iscloudfile="true"
                      ></myide>
                    </div>
                  </div>
                  <div style="height: 1rem"></div>
                  <div slot="footer" class="dialog-footer">
                    <div style="text-align: left; float: left">
                      <el-button
                        type="text"
                        class="pulse-enter-active"
                        icon="el-icon-star-on"
                        size="mini"
                        style="
                          font-size: 1.06rem;
                          text-align: left;
                          color: #67c23a;
                          margin: 0rem 0rem;
                          float: left;
                        "
                        @click="downloadonefile(filename)"
                        >点击下载</el-button
                      >
                    </div>

                    <div style="text-align: right">
                      <el-button
                        style="
                          color: #67c23a;
                          margin: 0rem 2.6rem;
                          font-size: 1.06rem;
                        "
                        width="auto"
                        type="text"
                        class="el-icon-s-opportunity pulse-enter-active"
                        @click="lookView()"
                        >预览</el-button
                      >
                      <el-button
                        style="
                          color: #67c23a;
                          margin: 0rem 2.6rem 0rem 0rem;
                          font-size: 1.06rem;
                        "
                        size="mini"
                        width="auto"
                        type="text"
                        class="el-icon-share pulse-enter-active"
                        @click="
                          copy(
                            linuxurl +
                              '/static/cloudfile/' +
                              $store.state.my_id +
                              filename
                          )
                        "
                        >分享</el-button
                      >
                      <el-button
                        style="
                          margin: 0rem 0rem;
                          font-size: 1.06rem;
                          color: red;
                        "
                        width="auto"
                        type="text"
                        class="el-icon-upload pulse-enter-active"
                        @click="savacode()"
                        >保存</el-button
                      >
                    </div>
                  </div>
                </div>
              </el-dialog>
            </div>
          </div>
          <div style="height: 2.46rem"></div>
        </div>
      </div>
      <div>
        <el-dialog
          @contextmenu.prevent.native="IsShowUp = false"
          :visible.sync="IsShowUp"
          width="30%"
          :append-to-body="true"
        >
          <div
            style="width: 100%;margin-left=auto;margin-right:auto;text-align:center;height:auto;"
          >
            <el-upload
              class="upload-demo"
              style="width: 100%;margin-left=auto;margin-right:auto;text-align:center"
              :headers="head"
              drag
              ref="upload"
              :auto-upload="true"
              :before-upload="passpath"
              :data="passparam"
              :action="cloudfileurl"
              :on-success="reloadList"
              multiple
            >
              <i class="el-icon-upload"></i>
              <div class="el-upload__text" style="font-size: 1.06rem">
                将文件拖到此处，或<em>点击上传</em>
              </div>
              <div class="el-upload__tip" slot="tip" style="font-size: 1.06rem">
                （支持多文件上传，不支持文件夹上传)
              </div>
            </el-upload>
          </div>
        </el-dialog>
      </div>
    </div>
  </div>
</template>


<script>
import "../../../updateCompoents/mavon-editor/dist/markdown/github-markdown.min.css";
import urlencode from "../../../updateCompoents/urlencode";
import myide from "../../components/myide.vue";

export default {
  name: "cloudfile",
  components: {
    myide,
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
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  activated() {
    this.isseetip = true;
  },
  async created() {
    this.isseetip = true;
    this.linuxurl = window.sessionStorage.getItem("linuxurl");
    if (!this.linuxurl) {
      await this.getlinuxurl();
    } else {
      this.cloudfileurl = this.linuxurl + "/Cloudfile/upFile";
    }
    this.IsShowCode = false;
    this.IsShowStaticFile = false;
    this.iscloseFile = false;
    this.IsShowUp = false;
    this.Isnew = false;
    this.height = window.innerHeight - 198 + "px";
    this.head = {
      authorization: "Bearer " + window.localStorage.getItem("authorization"),
      key: window.localStorage.getItem("key"),
    };
    this.filepath = []; //一定要清空文件夹路径
    this.filepath.push("");
    await this.loadCharset();
    this.getlist();
    this.getPercentage();
  },
  data() {
    return {
      ide_code: "",
      is_code_file: false,
      language: "cpp",
      char_set: [],
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      whiteList: true,
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
        readmodel: false, // 沉浸式阅读
        /* 1.3.5 */
        undo: true, // 上一步
        trash: true, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
      isseetip: true,
      iscloseFile: false,
      height: window.innerHeight + "px",
      deepcolor: "rgba(41, 50, 56, 0.16)",
      notdeepcolor: "rgba(26, 26, 26, 0.06)",
      file_percentage: 0,
      gitclass: [
        "folder",
        "music",
        "video",
        "code",
        "pdf",
        "compressed",
        "photo",
        "exe",
        "txt",
      ],
      IsShowStaticFile: false,
      ShowStaticFileUrl: "",
      linuxurl: "",
      cloudfileurl: "",
      IsShowCode: false,
      IsShowUp: false,
      code: "",
      list: [],
      filename: "", //文本路径加文本名称
      onetheme: "monokai",
      filepath: [],
      passparam: {
        path: "",
      },
      head: {
        authorization: "Bearer " + window.localStorage.getItem("authorization"),
        key: window.localStorage.getItem("key"),
      },
      Isnew: false,
      newname: "",
      isfile: false,
    };
  },
  methods: {
    updateIdeCode(code) {
      this.ide_code = code;
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
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><video style="height: ${
          window.innerHeight / 2 + "px"
        }; width: 100%" controls controlslist="nodownload"><source src="`;
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
      formdata.append("image", $file);
      await this.$ajax({
        url: "/CloudfileImage/saveImage",
        method: "post",
        data: formdata,
        headers: { "Content-Type": "multipart/form-data" },
      })
        .then((res) => {
          // 第二步.将返回的url替换到文本原位置![...](0) -> ![...](url)
          // $vm.$img2Url 详情见本页末尾
          this.$refs.md.$img2Url(pos, res.data.url);
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
    async $imgDel(pos) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/CloudfileImage/deleteImage",
        data: {
          path: pos[0],
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
        this.commenttext = "";
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 文件解码
    get_name(str) {
      if (!this.char_set.length) {
        return;
      }
      let point_loc = str.length;
      let first_base64_name = "";
      let name = "";
      let first_name = "";
      let last_name = "";
      for (let i = 0; i < str.length; ++i) {
        if (str[i] == ".") {
          point_loc = i;
          break;
        }
        first_base64_name += str[i];
      }
      for (let i = point_loc; i < str.length; ++i) {
        last_name += str[i];
      }
      first_name = this.Base64Decode(first_base64_name, this.char_set);
      name = first_name + last_name;
      return name;
    },
    // 目录解码
    get_dir_name(str) {
      let name = "";
      let len = str.length;
      let one_name = "";
      for (let i = 0; i < len; ++i) {
        if (str[i] == ".") {
          return "错误：不是文件夹！";
        }
        if (str[i] == "/") {
          name = name + this.Base64Decode(one_name, this.char_set) + "/";
          one_name = "";
        } else {
          one_name += str[i];
          if (i == len - 1) {
            name = name + this.Base64Decode(one_name, this.char_set) + "/";
            one_name = "";
          }
        }
      }
      return name;
    },
    // 获取字符集
    async loadCharset() {
      while (!this.char_set.length) {
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Cloudfile/loadCharset",
          portType: {
            process: "8797",
          },
        }).catch((t) => {
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
        if (res && res.code && res.code == 1) {
          this.char_set = res.data;
          return;
        }
      }
    },
    base64_decode(str) {
      return this.get_name(str);
    },
    base_encode(str) {
      return this.Base64Encode(str, this.char_set);
    },
    closeFile() {
      this.iscloseFile = true;
    },
    passpath() {
      this.passparam.path = this.filepath[this.filepath.length - 1];
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.linuxurl = res;
      this.cloudfileurl = res + "/Cloudfile/upFile";
    },

    async getPercentage() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/getcloudfilePercentage",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.file_percentage = res.data;
    },
    async newfile() {
      if (this.newname == "") {
        this.$msg({
          type: "error",
          message: "名称不能为空！",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/newFile",
        data: {
          path: this.filepath[this.filepath.length - 1],
          name: this.newname,
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
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.tolookfolder(this.filepath[this.filepath.length - 1]);
    },

    //首次加载自动获取文件和文件夹
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
        data: {
          path: this.filepath[0],
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.list = res.data;
    },
    //上传文件自动刷新
    async reloadList(response, file, file_list) {
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
      this.deleteOneFileHistoryFromUpList(file, file_list);
      this.getPercentage();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
        data: {
          path: this.filepath[this.filepath.length - 1],
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.list = res.data;
    },
    async tolookfolder(path) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
        data: {
          path: path,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.filepath.push(path); //获取到文件列表后入栈
      this.list = res.data;
    },
    async refreshlist(path) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
        data: {
          path: path,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.list = res.data;
    },

    async tolookcode(path) {
      this.filename = path;
      this.IsShowCode = false;
      this.IsShowStaticFile = false;
      this.is_code_file = false;
      let is_code = false;
      for (const key in this.$SqsGlobal.map_language_file) {
        if (
          Object.hasOwnProperty.call(this.$SqsGlobal.map_language_file, key) &&
          path.endsWith(key)
        ) {
          is_code = true;
          this.language = this.$SqsGlobal.map_language_file[key];
        }
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/lookCode",
        data: {
          path: path,
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
        this.code = res.data;
        if (is_code) {
          this.is_code_file = true;
        }
        this.IsShowCode = true;
      } else {
        this.IsShowCode = false;
        this.iscloseFile = false;
        this.IsShowStaticFile = true;
        this.ShowStaticDialog();
      }
    },

    async savacode() {
      if (this.is_code_file) {
        this.code = this.ide_code;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/updataCode",
        data: {
          path: this.filename,
          code: this.code,
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
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async ShowStaticDialog() {
      if (
        this.$store.state.my_id &&
        this.$store.state.my_id != null &&
        this.$store.state.my_id != undefined &&
        this.$store.state.my_id != ""
      ) {
        this.ShowStaticFileUrl =
          this.linuxurl +
          "/static/cloudfile/" +
          this.$store.state.my_id +
          this.filename;

        return;
      } else {
        this.$store.commit("updateObj", { my_id: this.getMyId() });
        this.ShowStaticFileUrl =
          this.linuxurl +
          "/static/cloudfile/" +
          this.$store.state.my_id +
          this.filename;
        return;
      }
    },
    async lookfile() {
      if (
        this.$store.state.my_id &&
        this.$store.state.my_id != null &&
        this.$store.state.my_id != undefined &&
        this.$store.state.my_id != ""
      ) {
        let url =
          this.linuxurl +
          "/static/cloudfile/" +
          this.$store.state.my_id +
          this.filename;
        this.IsShowCode = false;
        this.IsShowStaticFile = false;
        this.$router.push({
          path: "/staticfile",
          query: {
            path: urlencode(url, "gbk"),
          },
        });
        return;
      } else {
        this.$store.commit("updateObj", { my_id: this.getMyId() });
        let url =
          this.linuxurl +
          "/static/cloudfile/" +
          this.$store.state.my_id +
          this.filename;
        this.IsShowCode = false;
        this.IsShowStaticFile = false;
        this.$router.push({
          path: "/staticfile",
          query: {
            path: urlencode(url, "gbk"),
          },
        });
        return;
      }
    },
    async lookView() {
      if (
        this.$store.state.my_id &&
        this.$store.state.my_id != null &&
        this.$store.state.my_id != undefined &&
        this.$store.state.my_id != 0
      ) {
        let url =
          this.linuxurl +
          "/Filehtml/lookView?path=" +
          this.$store.state.my_id +
          this.filename;
        window.open(url);
        return;
      }
      this.$store.commit("updateObj", { my_id: this.getMyId() });
      let url =
        this.linuxurl +
        "/Filehtml/lookView?path=" +
        this.$store.state.my_id +
        this.filename;
      window.open(url);
    },
    async backfile() {
      if (this.filepath.length <= 1) return;
      this.filepath.pop(); //去除当前路径
      let lastpath = this.filepath[this.filepath.length - 1];
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
        data: {
          path: lastpath,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.list = res.data;
    },

    async deletefile(path) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/deleteFile",
        data: {
          path: path,
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
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.refreshlist(this.filepath[this.filepath.length - 1]);
      this.getPercentage();
    },

    showdelete(path, name = "") {
      this.$alert(`此操作将永久删除【${name}】, 是否继续?`, "提示", {
        confirmButtonText: "确定",
        type: "warning",
      })
        .then(() => {
          this.deletefile(path);
        })
        .catch(() => {
          this.$msg({
            type: "info",
            message: "已取消删除",
            duration: 600,
            offset: 80,
          });
        });
    },
    //下载单个文件
    async downloadonefile(downloadpath) {
      this.$msg({
        type: "success",
        message: "开始下载",
        duration: 1600,
        offset: 80,
      });
      await this.$ajax({
        method: "post",
        url: "/Cloudfile/downloadFile",
        responseType: "blob",
        headers: {
          "Content-Type": "application/json; application/octet-stream;",
        },
        data: {
          path: downloadpath,
        },
      })
        .then((res) => {
          this.$msg({
            type: "success",
            message: "下载完成",
            duration: 1600,
            offset: 80,
          });

          let slanting_bar = 0;
          let point_loc = downloadpath.length;
          let first_name = "";
          let last_name = "";
          let len = downloadpath.length;
          for (let i = len - 1; i >= 0; --i) {
            if (downloadpath[i] == "/") {
              slanting_bar = i;
              break;
            }
          }
          for (let i = len - 1; i >= 0; --i) {
            if (downloadpath[i] == ".") {
              point_loc = i;
              last_name = downloadpath[i] + last_name;
              break;
            }
            last_name = downloadpath[i] + last_name;
          }
          for (let i = slanting_bar + 1; i < point_loc; ++i) {
            first_name += downloadpath[i];
          }

          let Name = this.Base64Decode(first_name, this.char_set) + last_name;
          if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            const blob = new Blob([res.data], {
              type: "application/octet-stream;application/zip",
            });
            window.navigator.msSaveOrOpenBlob(blob, Name);
          } else {
            /* 火狐谷歌的文件下载方式 */
            const blob = new Blob([res.data], {
              type: "application/octet-stream;application/zip",
            });
            let url = window.URL.createObjectURL(blob);
            const link = document.createElement("a"); // 创建a标签
            link.href = url;
            link.download = Name; // 重命名文件
            link.click();
            URL.revokeObjectURL(url); // 释放内存
          }
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
    //下载当前页所有文件
    async downloadFile(downloadpath) {
      this.$msg({
        type: "success",
        message: "开始下载",
        duration: 1600,
        offset: 80,
      });
      await this.$ajax({
        method: "post",
        url: "/Cloudfile/downloadFile",
        responseType: "blob",
        headers: {
          "Content-Type": "application/json; application/octet-stream;",
        },
        data: {
          path: downloadpath,
        },
      })
        .then((res) => {
          this.$msg({
            type: "success",
            message: "下载完成",
            duration: 1600,
            offset: 80,
          });
          let reslastname = ".zip";
          let Name = "下载" + reslastname;

          if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            const blob = new Blob([res.data], {
              type: "application/zip",
            });
            window.navigator.msSaveOrOpenBlob(blob, Name);
          } else {
            /* 火狐谷歌的文件下载方式 */
            const blob = new Blob([res.data], {
              type: "application/zip",
            });
            let url = window.URL.createObjectURL(blob);
            const link = document.createElement("a"); // 创建a标签
            link.href = url;
            link.download = Name; // 重命名文件
            link.click();
            URL.revokeObjectURL(url); // 释放内存
          }
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
  },
};
</script>
<style lang="less" scoped>
@import "../../../public/md/markdown/github-markdown.min.css";

/deep/.el-textarea__inner {
  color: #ffffffe6 !important;
  background-color: rgba(30, 30, 30, 0.688) !important;
  border-color: rgba(30, 30, 30, 0.688) !important;
}
.folder {
  float: left;
  background-image: url("../../assets/file.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}
.code {
  float: left;
  background-image: url("../../assets/code.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.music {
  float: left;
  background-image: url("../../assets/music.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.video {
  float: left;
  background-image: url("../../assets/video.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.pdf {
  float: left;
  background-image: url("../../assets/pdf.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.compressed {
  float: left;
  background-image: url("../../assets/zip.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.txt {
  float: left;
  background-image: url("../../assets/txt.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.photo {
  float: left;
  background-image: url("../../assets/photo.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.exe {
  float: left;
  background-image: url("../../assets/exe.png");
  background-size: 80% auto;
  height: 4rem;
  width: 4rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 1rem 1rem;
}

.flow {
  float: left;
  text-align: center;
  margin-right: 10px;
}
.clear {
  clear: both;
}
</style>