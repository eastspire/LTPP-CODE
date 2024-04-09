<template>
  <div v-show="isseetip">
    <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
      <div
        v-loading.lock="!loadfinish"
        element-loading-text="拼命加载中"
        element-loading-spinner="el-icon-loading"
        element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"
        class="shadow ltpp-list-box"
        :style="`min-height:${$store.state.no_scroll_height}vh`"
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
                >新建文件</el-button
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
              <div style="clear: both"></div>
            </div>
          </div>
        </div>
        <div style="clear: both"></div>
        <div style="height: 0.8rem"></div>
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
              class="shadow ltpp-list-box"
              :key="index"
              :style="`cursor: pointer; background-color:${
                index % 2 != 0 ? deepcolor : notdeepcolor
              };margin-top:0.46rem;`"
              @contextmenu.prevent="showdelete(tem[4], tem[0])"
              @dblclick="tolookcode(tem[0], tem[4], index)"
            >
              <div>
                <div>
                  <div
                    :class="gitclass[tem[1] - 1]"
                    style="margin-left: 1rem"
                  ></div>
                  <div
                    style="
                      white-space: nowrap;
                      text-overflow: ellipsis;
                      float: left;
                      margin: 2rem 1rem 1rem 0rem;
                      overflow: hidden;
                      width: 46%;
                    "
                  >
                    <el-tooltip
                      class="item;"
                      effect="dark"
                      :content="base64_decode(tem[0])"
                      placement="top"
                    >
                      <span style="font-size: 1.06rem; color: deepskyblue">
                        {{ base64_decode(tem[0]) }}
                      </span>
                    </el-tooltip>
                  </div>

                  <div style="float: right">
                    <p
                      style="
                        float: left;
                        margin: 2rem 1rem 1rem 1rem;
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
                        margin: 2rem 1rem 1rem 1rem;
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

            <!-- 新建文件 -->
            <div>
              <el-dialog
                :close-on-click-modal="false"
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

            <div>
              <el-dialog
                :close-on-click-modal="false"
                @contextmenu.prevent.native="savacode"
                :visible.sync="isShowFileDialog"
                title="文件"
                :width="
                  ($store.state.max_width / $store.state.now_width) * 100 + '%'
                "
                :append-to-body="true"
                @closed="iscloseFile = true"
              >
                <!-- 文本文件 -->
                <div
                  v-if="IsShowTxt"
                  style="border-radius: 0rem; height: auto; border-width: 0rem"
                >
                  <div>
                    <div v-if="is_code_file">
                      <myide
                        v-if="txt_load_finish"
                        :code="code"
                        :language="language"
                        @upCodeFile="updateIdeCode"
                        :iscloudfile="true"
                      ></myide>
                      <ShowCode
                        v-else
                        :code="code"
                        :iscloudfile="true"
                        language="php"
                      ></ShowCode>
                    </div>
                    <div v-else>
                      <div class="markdown-body">
                        <mavon-editor
                          ref="md"
                          @imgAdd="$imgAdd"
                          class="md"
                          v-model.lazy="code"
                          :toolbars="toolbars"
                          :subfield="prop.subfield"
                          :defaultOpen="prop.defaultOpen"
                          :toolbarsFlag="prop.toolbarsFlag"
                          :editable="prop.editable && txt_load_finish"
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
                          :close-on-click-modal="false"
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
                          :close-on-click-modal="false"
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
                        @click="downloadonefile()"
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
                        @click="copy(linuxurl + base64_decode(filepath))"
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
                <!-- 静态资源 -->
                <div v-if="IsShowStaticFile">
                  <iframe
                    v-if="!iscloseFile"
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
                      @click="downloadonefile()"
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
                      @click="copy(linuxurl + base64_decode(filepath))"
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
                        isShowFileDialog = false;
                        lookfile();
                      "
                      >新页面打开</el-button
                    >
                  </div>
                  <div style="clear: both"></div>
                </div>
              </el-dialog>
            </div>
          </div>
          <div style="height: 2.46rem"></div>
        </div>
      </div>
      <div>
        <el-dialog
          :close-on-click-modal="false"
          @contextmenu.prevent.native="IsShowUp = false"
          :visible.sync="IsShowUp"
          width="30%"
          :append-to-body="true"
        >
          <div
            style="
              width: 100%;
              margin-left: auto;
              margin-right: auto;
              text-align: center;
              height: auto;
            "
          >
            <el-upload
              class="upload-demo"
              style="
                width: 100%;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
              "
              :headers="head"
              drag
              ref="upload"
              :auto-upload="true"
              :action="cloudfileurl"
              :on-success="reloadList"
              multiple
            >
              <i class="el-icon-upload"></i>
              <div class="el-upload__text" style="font-size: 1.06rem">
                将文件拖到此处，或<em>点击上传</em>
              </div>
              <div class="el-upload__tip" slot="tip" style="font-size: 1.06rem">
                （支持多文件上传）
              </div>
            </el-upload>
          </div>
        </el-dialog>
      </div>
    </div>
  </div>
</template>


<script>
import "../../../public/md/markdown/github-markdown.min.css";
import urlencode from "../../../updateCompoents/urlencode";
import myide from "../../components/myide.vue";
const file_txt_loading_tips = "资源加载中！请耐心等待！";
import ShowCode from "../../components/showcode.vue";

export default {
  name: "cloudfile",
  components: {
    myide,
    ShowCode,
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
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
  },
  destroyed() {
    this.isseetip = false;
  },
  activated() {
    this.isseetip = true;
    this.isShowFileDialog = false;
    this.IsShowTxt = false;
    this.IsShowStaticFile = false;
    this.iscloseFile = false;
    this.IsShowUp = false;
    this.Isnew = false;
    this.txt_load_finish = false;
    this.height = window.innerHeight - 198 + "px";
    this.head = {
      Authorization: "Bearer " + window.localStorage.getItem("authorization"),
      Key: window.localStorage.getItem("key"),
      Requestid: this.Base64Encode(new Date().getTime()),
    };
    this.requestid_timer = setInterval(() => {
      this.head.Requestid = this.Base64Encode(new Date().getTime());
    }, 1000);
  },
  async created() {
    this.isseetip = true;
    const tem_linuxurl = window.sessionStorage.getItem("linuxurl");
    if (!tem_linuxurl) {
      await this.getlinuxurl();
    } else {
      this.linuxurl = tem_linuxurl;
      this.cloudfileurl = this.linuxurl + "/Cloudfile/upFile";
    }
    await this.loadCharset();
    this.getlist();
    this.getPercentage();
  },
  data() {
    return {
      txt_load_finish: false,
      isShowFileDialog: false,
      loadfinish: false,
      file_idx: 0,
      requestid_timer: null,
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
      isseetip: true,
      iscloseFile: false,
      height: window.innerHeight + "px",
      deepcolor: "rgba(var(--ltpp-light-color), 0.16)",
      notdeepcolor: "rgba(var(--ltpp-main-bk-color), 0.06)",
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
      linuxurl: window?.location?.href,
      cloudfileurl: window?.location?.href,
      IsShowTxt: false,
      IsShowUp: false,
      code: "",
      list: [],
      filename: "",
      filepath: "",
      onetheme: "monokai",
      head: {},
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
        if (res && res?.code && res?.code == 1) {
          this.char_set = res?.data;
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
      this.file_percentage = res?.data;
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
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 600,
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
      this.getlist();
      this.getPercentage();
    },
    //首次加载自动获取文件
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/loadList",
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.loadfinish = true;
        return;
      });
      this.list = res?.data;
      this.loadfinish = true;
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
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.list = res?.data;
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
      this.list = res?.data;
    },
    async tolookcode(name, path, index) {
      this.file_idx = index;
      this.filename = name;
      this.filepath = path;
      this.IsShowTxt = false;
      this.IsShowStaticFile = false;
      this.is_code_file = false;
      let is_code = false;
      path = this.base64_decode(path);
      for (const key in this.$SqsGlobal.map_language_file) {
        if (
          Object.hasOwnProperty.call(this.$SqsGlobal.map_language_file, key) &&
          path.endsWith(key)
        ) {
          is_code = true;
          this.language = this.$SqsGlobal.map_language_file[key];
        }
      }
      this.isShowFileDialog = true;
      if (is_code) {
        this.is_code_file = true;
      }
      this.IsShowTxt = true;
      this.code = file_txt_loading_tips;
      this.txt_load_finish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/lookCode",
        data: {
          path: this.filepath,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.txt_load_finish = false;
        this.code = "资源加载失败！请稍后重试！";
        return;
      });
      this.txt_load_finish = true;
      if (res?.code == 1) {
        this.code = res?.data;
      } else {
        this.IsShowTxt = false;
        this.iscloseFile = false;
        this.IsShowStaticFile = true;
        this.ShowStaticDialog();
      }
    },
    async savacode() {
      if (!this.IsShowTxt || this.IsShowStaticFile) {
        return;
      }
      if (this.is_code_file) {
        this.code = this.ide_code;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Cloudfile/updataCode",
        data: {
          path: this.filepath,
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
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 600,
          offset: 80,
        });
        try {
          this.list[this.file_idx][2] = res?.data;
        } catch (err) {}
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
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
          this.linuxurl + this.base64_decode(this.filepath);
      } else {
        this.$store.commit("updateObj", { my_id: this.getMyId() });
        this.ShowStaticFileUrl =
          this.linuxurl + this.base64_decode(this.filepath);
      }
    },
    async lookfile() {
      if (
        this.$store.state.my_id &&
        this.$store.state.my_id != null &&
        this.$store.state.my_id != undefined &&
        this.$store.state.my_id != ""
      ) {
        let url = this.linuxurl + this.base64_decode(this.filepath);
        this.IsShowTxt = false;
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
        let url = this.linuxurl + this.base64_decode(this.filepath);
        this.IsShowTxt = false;
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
        let url = this.linuxurl + "/Filehtml/lookView?path=" + this.filepath;
        window.open(url);
        return;
      }
      this.$store.commit("updateObj", { my_id: this.getMyId() });
      let url = this.linuxurl + "/Filehtml/lookView?path=" + this.filepath;
      window.open(url);
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
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 600,
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
      this.refreshlist();
      this.getPercentage();
    },
    showdelete(path, name = "") {
      name = this.base64_decode(name);
      this.$alert(`此操作将永久把【${name}】隐藏（文件依然存在）`, "提示", {
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
    async downloadonefile() {
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
          path: this.filepath,
        },
      })
        .then((res) => {
          let name = this.Base64Decode(this.filename, this.char_set);
          if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            const blob = new Blob([res?.data], {
              type: "application/octet-stream;application/zip",
            });
            window.navigator.msSaveOrOpenBlob(blob, name);
          } else {
            /* 火狐谷歌的文件下载方式 */
            const blob = new Blob([res?.data], {
              type: "application/octet-stream;application/zip",
            });
            let url = window.URL.createObjectURL(blob);
            const link = document.createElement("a"); // 创建a标签
            link.href = url;
            link.download = name; // 重命名文件
            link.click();
            URL.revokeObjectURL(url); // 释放内存
          }
          this.$msg({
            type: "success",
            message: "下载完成",
            duration: 1600,
            offset: 80,
          });
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
  color: var(--ltpp-box-text-color) !important;
  background-color: rgba(var(--ltpp-light-color), 1) !important;
  border-color: rgba(var(--ltpp-light-color), 1) !important;
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