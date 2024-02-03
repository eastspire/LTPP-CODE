<template>
  <div
    :style="`height:${$store.state.no_scroll_height}vh`"
    @contextmenu.prevent=""
  >
    <el-container
      class="shadow"
      :style="`height:${$store.state.no_scroll_height}vh; border: 1px solid rgba(0,0,0,0.1);`"
    >
      <el-aside
        v-loading.lock="!chat_list_loadfinish"
        element-loading-text="拼命加载中"
        element-loading-spinner="el-icon-loading"
        element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"          
        width="200px"
        style="background-color: rgba(var(--ltpp-main-bk-color), 0.16)"
        id="scroll"
      >
        <div>
          <el-autocomplete
            class="search_input"
            v-model.lazy="search_user"
            :fetch-suggestions="querySearchAsync"
            placeholder="请输入名称"
            @select="handleSelect"
          ></el-autocomplete>
        </div>
        <div style="text-align: center">
          <el-button
            type="primary"
            icon="el-icon-s-custom"
            style="margin: 0.4rem; font-weight: bold"
            class="pulse-enter-active"
            @click="isSeeCreatGroup = true"
            >新 建 群 聊</el-button
          >
        </div>
        <div id="list">
          <div v-for="tem in user_list" :key="tem.idnex" class="user">
            <div
              @click="changeNowWindow(tem)"
            >
              <el-image
                fit="cover"
                style="height: 3.4rem; width: 3.4rem"
                :src="tem.headimage"
                lazy
              >
              </el-image>

              <span
                :class="`${
                  tem.type == 'group_chat'
                    ? 'group-name'
                    : tem.online == 1
                    ? 'online-name'
                    : 'unonline-name'
                }`"
                >{{
                  tem.name && judgeIsString(tem.name)
                    ? tem.name.substr(0, 11)
                    : ""
                }}
              </span>
              <span class="num" v-show="tem.no_look_num > 0">
                <span class="num-txt">
                  {{
                    tem.no_look_num > 99
                      ? "99"
                      : tem.no_look_num.toString().padStart(2, "0")
                  }}
                </span>
              </span>
            </div>
          </div>
        </div>
      </el-aside>

      <el-container
        v-loading.lock="!load_msg_list_finish"
        element-loading-text="拼命加载中"
        element-loading-spinner="el-icon-loading"
        element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"    
        v-if="user_list && typeof user_list == 'object' && user_list.length > 0"
      >
        <el-header class="main_header" v-show="now_user && now_user.id">
          <p>
            <span
              @click="
                now_user.type == 'private_chat'
                  ? touserpage(now_user.id)
                  : getGroupUserList()
              "
              style="cursor: pointer"
            >
              {{
                now_user && now_user.name && judgeIsString(now_user.name)
                  ? now_user.name
                  : ""
              }}
            </span>
          </p>
        </el-header>
        <el-main class="main_dia" id="chatDataScrollDiv">
          <div style="text-align: center">
            <el-button
              type="text"
              v-show="isSeeLastBtn && now_user && now_user.id"
              @click="getHistoryChatData()"
              class="pulse-enter-active"
              >点击加载历史消息</el-button
            >
          </div>
          <div id="chatlist">
            <div
              v-for="(tem, index) in chat_msg_list"
              :key="tem.index"
              :id="tem.id"
            >
              <div v-show="index % 10 == 0" class="show_time">
                <span>{{ tem.time }}</span>
              </div>
              <div class="user_chat_dia">
                <el-avatar
                  :src="tem.headimage"
                  :style="`height: 3.4rem; width: 3.4rem; float: ${
                    tem.name == $store.state.my_name ? 'right' : 'left'
                  }`"
                >
                </el-avatar>
                <span
                  class="user_name"
                  :style="`float: ${
                    tem.name == $store.state.my_name ? 'right' : 'left'
                  }`"
                  @click="
                    touserpage(
                      tem.type == 'group_chat'
                        ? tem.get_user_id
                        : tem.post_user_id
                    )
                  "
                  >{{ tem.name }} :</span
                >
                <div style="clear: both; height: 0.4rem"></div>
                <div
                  @dblclick="copy(tem.msg)"
                  :style="`display: flex; justify-content:${
                    tem.name == $store.state.my_name ? 'end' : 'start'
                  }`"
                >
                  <mavon-editor
                    class="md shadow"
                    :value="tem.msg"
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
                    :style="`
                      width: fit-content !important;
                      min-width:0rem !important;
                      min-height: 0rem;
                      height: auto;
                      border-width: 0rem;
                      border-radius: 0.6rem; ${
                        tem.name == $store.state.my_name
                          ? 'margin-right: 4rem'
                          : 'margin-left: 4rem'
                      };`"
                  >
                  </mavon-editor>
                </div>
              </div>
              <div style="clear: both"></div>
            </div>
          </div>
        </el-main>
        <el-footer height="16rem" v-show="now_user && now_user.id">
          <div>
            <el-button
              type="text"
              class="el-icon-circle-plus pulse-enter-active"
              style="font-size: 1.06rem; color: deepskyblue"
              @click="isSeeUpload = true"
              >上传文件</el-button
            >
            <el-button
              type="text"
              class="el-icon-menu pulse-enter-active"
              style="font-size: 1.06rem; color: deepskyblue; margin-left: 1rem"
              @click="
                loadFileList();
                isSeeChatFile = true;
              "
              >云文件</el-button
            >
          </div>
          <div @contextmenu.prevent="postmessage">
            <mavon-editor
              ref="md"
              @imgAdd="$imgAdd"
              class="md shadow"
              v-model.lazy="mymessage"
              :toolbars="toolbars"
              :subfield="prop.subfield"
              :defaultOpen="'edit'"
              :toolbarsFlag="true"
              :editable="true"
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
              style="
                min-height: 12.6rem;
                max-height: 12.6rem;
                z-index: 0;
                width: 100% !important;
                border-width: 0rem;
              "
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
              <!-- 发送 -->
              <template v-slot:right-toolbar-after>
                <el-button
                  type="text"
                  aria-hidden="true"
                  class="op-icon fa"
                  style="color: deepskyblue !important"
                  title="发送"
                  @click="postmessage()"
                >
                  <i class="el-icon-s-promotion" />
                </el-button>
              </template>
            </mavon-editor>
          </div>
        </el-footer>
      </el-container>
    </el-container>
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
      <span>视频链接格式错误，请重新确认后再输入！</span>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false"
          >确 定</el-button
        >
      </span>
    </el-dialog>
    <el-drawer
      :size="drawer_size"
      @contextmenu.prevent=""
      title="新建群聊"
      :visible.sync="isSeeCreatGroup"
      direction="rtl"
      ref="drawer"
    >
      <div style="overflow: hidden">
        <img
          class="animate"
          v-show="creat_group_chat_image && reg.test(creat_group_chat_image)"
          :src="creat_group_chat_image"
          style="display: block; width: 100%; height: 16rem; object-fit: cover"
        />
      </div>
      <div class="demo-drawer__content creat_group_input">
        <el-form :model="group_data">
          <el-form-item label="群聊名称">
            <el-input
              maxlength="11"
              show-word-limit
              v-model.lazy="group_data.name"
              autocomplete="off"
            ></el-input>
          </el-form-item>
          <el-form-item label="加群密码">
            <el-input
              maxlength="11"
              show-word-limit
              v-model.lazy="group_data.code"
              autocomplete="off"
            ></el-input>
          </el-form-item>
          <el-form-item label="群聊头像">
            <el-upload
              class="upload-demo"
              style="width: 100%; text-align: center"
              :limit="1"
              :headers="head"
              :auto-upload="true"
              :on-success="uploadPhotoSuccess"
              drag
              ref="upload_headimage"
              :action="backurl"
              multiple
            >
              <i class="el-icon-upload"></i>
              <div class="el-upload__text">
                将图片拖到此处，或<em>点击上传</em>
              </div>
              <div class="el-upload__tip" slot="tip">
                仅允许上传jpg/png/jpeg文件
              </div>
            </el-upload>
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer" style="text-align: center">
          <el-button
            type="primary"
            @click="
              now_post_type = 'create_group';
              postmessage();
              isSeeCreatGroup = false;
            "
            >创建</el-button
          >
          <el-button
            type="danger"
            @click="isSeeCreatGroup = false"
            style="margin-left: 2rem"
            >返回</el-button
          >
        </div>
      </div>
    </el-drawer>

    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      width="36%"
      title="加群"
      :visible.sync="isSeeJoinGroupDia"
    >
      <div>
        <el-input
          style="font-size: 1rem; overflow: hidden"
          placeholder="请输入加群密码（如果该群没有设置密码该项请不要填写）"
          v-model.lazy="my_join_group_code"
        ></el-input>
      </div>
      <br />
      <div style="text-align: right">
        <el-button
          type="text"
          maxlength="11"
          show-word-limit
          class="el-icon-s-promotion"
          style="font-size: 1.06rem; margin-right: 1rem"
          @click="
            now_post_type = 'join_group';
            postmessage();
          "
        >
          进群
        </el-button>
      </div>
    </el-dialog>
    <div>
      <el-dialog
        :close-on-click-modal="false"
        @contextmenu.prevent.native="isSeeUpload = false"
        :visible.sync="isSeeUpload"
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
            ref="upload_file"
            :auto-upload="true"
            :data="passparam"
            :action="linuxurl + '/Chatfile/chatUpFile'"
            :on-success="uploadFinish"
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
    <el-drawer
      :size="drawer_size"
      @contextmenu.prevent=""
      title="云文件"
      :visible.sync="isSeeChatFile"
      direction="rtl"
      ref="drawer"
    >
      <div>
        <div v-for="(tem, index) in filelist" :key="index">
          <div>
            <div
              style="margin: 1rem; cursor: pointer"
              @dblclick="downloadonefile(tem[0], tem[4])"
            >
              <div :class="gitclass[tem[1] - 1]"></div>
              <div style="white-space:nowrap;text-overflow:ellipsis;overflow:hidden;width:84%;">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="base64_decode(tem[0])"
                  placement="top"
                >
                  <p style="font-size: 1rem; color: deepskyblue">
                    {{ base64_decode(tem[0]).substr(0, 36) }}
                  </p>
                </el-tooltip>
              </div>
              <div>
                <p style="font-size: 0.78rem; color: deeppink">
                  ({{ tem[3] }}) {{ tem[2] }}
                </p>
              </div>
            </div>
          </div>
          <!-- 清除浮动 -->
          <div style="clear: both"></div>
        </div>
      </div>
    </el-drawer>

    <el-drawer
      :size="drawer_size"
      @contextmenu.prevent=""
      :title="`群成员(群密码:${
        now_user && now_user.group_data && now_user.group_data.code
          ? now_user.group_data.code
          : '空密码'
      })`"
      :visible.sync="isSeeChatUser"
      direction="rtl"
      ref="drawer"
    >
      <div>
        <div v-for="(tem, index) in group_user_list" :key="index">
          <div>
            <div
              class="user"
              style="margin: 0.6rem; cursor: pointer"
              @click="
                isSeeChatUser = false;
                touserpage(tem.id);
              "
            >
              <div>
                <el-image
                  fit="cover"
                  style="height: 3.4rem; width: 3.4rem"
                  :src="tem.headimage"
                  lazy
                >
                </el-image>

                <span
                  :class="`${
                    tem.online == 1 ? 'online-name' : 'unonline-name'
                  }`"
                  >{{
                    tem.name && judgeIsString(tem.name)
                      ? tem.grade == 2
                        ? tem.name.substr(0, 11) + "（群主）"
                        : tem.grade == 1
                        ? tem.name.substr(0, 11) + "（管理员）"
                        : tem.name.substr(0, 11)
                      : ""
                  }}
                </span>
              </div>
            </div>
          </div>
          <!-- 清除浮动 -->
          <div style="clear: both"></div>
        </div>
      </div>
    </el-drawer>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/css/index.css";
export default {
  name: "chat",
  data() {
    return {
      chat_list_loadfinish: false,
      load_msg_list_finish: false,
      requestid_timer: null,
      char_set: [],
      toolbars: {
        bold: true, // 粗体
        italic: true, // 斜体
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
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      creat_group_chat_image: "",
      drawer_size: "460px",
      isSeeChatUser: false,
      group_user_list: [],
      user_deep_color:
        "background-color: rgba(117,63,178,0.66);color:rgba(255,255,255,0.9)",
      user_no_deep_color: "background-color:rgba(117,63,178,0.06)",
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
      filelist: [],
      linuxurl: "",
      passparam: {
        user_id: 0,
      },
      isSeeChatFile: false,
      isSeeUpload: false,
      last_dom_id: "",
      timer: null,
      willJoinGroupData: {},
      my_join_group_code: "",
      isSeeJoinGroupDia: false,
      now_post_type: "",
      msgtypeObj: {
        private_chat: "private_chat",
        group_chat: "group_chat",
        create_group: "create_group",
        join_group: "join_group",
        delete_group: "delete_group",
        exit_group: "exit_group",
        connect_group: "connect_group",
      },
      head: {},
      linuxurl: "",
      backurl: "",
      group_data: {},
      isSeeCreatGroup: false,
      timeout: null,
      search_user: "",
      isSeeLastBtn: false,
      url: "",
      mymessage: "",
      now_user: {}, //当前窗口的用户个人信息
      id: "",
      user_list: [],
      chat_msg_list: [],
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
    };
  },
  async created() {
    this.isSeeChatFile = false;
    this.isSeeUpload = false;
    this.linuxurl = window.sessionStorage.getItem("linuxurl");
    if (!this.linuxurl) {
      await this.getlinuxurl();
    }    
    await this.loadCharset();
    // 获取聊天列表
    await this.getUserAndGroupList();
  },
  activated() {
    try{
      this.isSeeLastBtn = false;
      this.head = {
        Authorization: "Bearer " + window.localStorage.getItem("authorization"),
        Key: window.localStorage.getItem("key"),
        Requestid : this.Base64Encode(new Date().getTime())
      };
      this.requestid_timer = setInterval(() => {
          this.head.Requestid = this.Base64Encode(new Date().getTime())
      }, 1000);
      let list = document.getElementById("list");
      list.addEventListener("click", this.onclicklist);
      this.chat_msg_list = this.chat_msg_list.slice(-50);  
      window.localStorage.setItem(
        "Chat " + this.now_user.type + " " + this.now_user.id,
        JSON.stringify(this.chat_msg_list.slice(-50))
      );
      this.getHistoryChatData(true);
    }catch(err){}
    this.$nextTick(() => {
      this.to_scroll_bottom(1);
    });
  },
  async mounted() {
    let authorization = window.localStorage.getItem("authorization");
    let key = window.localStorage.getItem("key");
    if (authorization && key && this.$store.state.login) {
      this.setup();
    }
  },
  deactivated() {
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
    let list = document.getElementById("list");
    try {
      list && list.removeEventListener("click", this.onclicklist);
    } catch (e) {
      return;
    }
  },

  destroyed() {
    try {
      clearInterval(this.timer);
    } catch (e) {
      this.timer = null;
      return;
    }
    this.timer = null;
  },

  methods: {
    async changeNowWindow(tem){
      this.id = 0;
      tem.no_look_num = 0;
      this.now_post_type = tem.type;
      this.now_user = tem;
      this.chat_msg_list = [];
      this.isSeeLastBtn = false;
      this.passparam.user_id = tem.id;
      this.clearNolookNum();
      await this.getLatestChatData();
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
          (window.innerHeight / 4) * 3 + "px"
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
      formdata.append('file', $file);
      await this.$ajax({
        url: "/File/saveImage",
        method: "post",
        data: formdata,
        headers: {
          "Content-Type": "multipart/form-data",
        },
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
    async getGroupUserList() {
      this.isSeeChatUser = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chat/getGroupUserList",
        portType: {
          process: "8797",
        },
        data: {
          group_id: this.now_user.id,
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
        this.group_user_list = res?.data;
      }
    },
    async clearNolookNum() {
      this.$ajax({
        method: "post",
        url: "/Chat/clearNolookNum",
        portType: {
          process: "8797",
        },
        data: {
          user_id: this.now_user.id,
          type: this.now_user.type,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
    },
    touserpage(id) {
      id &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    //下载单个文件
    async downloadonefile(file_name, file_path) {
      this.$msg({
        type: "success",
        message: "开始下载",
        duration: 1600,
        offset: 80,
      });
      await this.$ajax({
        method: "post",
        url: "/Chatfile/downloadFile",
        responseType: "blob",
        headers: {
          "Content-Type": "application/json; application/octet-stream;",
        },
        data: {
          path: file_path
        },
      })
        .then((res) => {
          this.$msg({
            type: "success",
            message: "下载完成",
            duration: 1600,
            offset: 80,
          });
          file_name = this.Base64Decode(file_name, this.char_set);
          /* 火狐谷歌的文件下载方式 */
          const blob = new Blob([res?.data], {
            type: "application/octet-stream;application/zip",
          });
          let url = window.URL.createObjectURL(blob);
          const link = document.createElement("a"); // 创建a标签
          link.href = url;
          link.download = file_name; // 重命名文件
          link.click();
          URL.revokeObjectURL(url); // 释放内存
        }).catch((t) => {
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
    base64_decode(str) {
      return this.get_name(str);
    },
    // 获取字符集
    async loadCharset() {
      this.char_set = await this.loadCloudCharset();
    },
    async loadFileList() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chatfile/loadList",
        data: {
          user_id: this.passparam.user_id,
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
      this.filelist = res?.data;
    },
    //上传文件成功
    async uploadFinish(response, file, file_list) {
      if (response && response.code && response.code == 1) {
        this.$msg({
          type: "success",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
        this.mymessage = "我刚刚上传" + response.filename + "到云文件，快去看看吧。";
        this.postmessage();
        this.mymessage = "";
      } else {
        this.$msg({
          type: "error",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.deleteOneFileHistoryFromUpList(file, file_list);
    },
    judgeIsString(str) {
      return typeof str == "string" && str.constructor == String;
    },
    uploadPhotoSuccess(response, file, file_list) {
      if (response?.url) {
        this.group_data.headimage = response.url;
        this.creat_group_chat_image = response.url;
      } 
      this.deleteOneFileHistoryFromUpList(file, file_list);
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.linuxurl = res;
      this.backurl = this.linuxurl + "/File/saveImage";
    },

    // 滚动到顶部
    to_scroll_Top() {
      this.$nextTick(() => {
        let scrollElem = document.getElementById("chatDataScrollDiv");
        if (scrollElem && scrollElem.scrollHeight) {
          let scroll_top = setInterval(() => {
            if (Math.floor(scrollElem.scrollTop) <= 1) {
              clearInterval(scroll_top);
              scroll_bottom = null;
            }
            scrollElem.scrollTop--;
          }, 1);
        }
      });
    },
    // 加载历史消息滚到之前的视图
    to_last_scroll() {
      this.$nextTick(() => {
        let sign_dom = null;
        let scrollElem = document.getElementById("chatDataScrollDiv");
        for (let i = 0; i < scrollElem.children[1].children.length; ++i) {
          if (scrollElem.children[1].children[i].id === this.last_dom_id) {
            sign_dom = scrollElem.children[1].children[i];
            break;
          }
        }
        if (scrollElem && sign_dom && scrollElem.scrollHeight) {
          scrollElem.scrollTop = sign_dom.offsetTop - 148.6;
        }
      });
    },
    // 滚动到底部
    to_scroll_bottom(is_init = 0) {
      this.$nextTick(() => {
        let scrollElem = document.getElementById("chatDataScrollDiv");
        if (scrollElem && scrollElem.scrollHeight) {
          if (
            Math.floor(scrollElem.scrollHeight - scrollElem.scrollTop) -
              scrollElem.clientHeight <=
            1
          ) {
            return;
          }
          if (is_init == 1) {
            // 首次打开该用户聊天框，迅速加载
            scrollElem.scrollTop = Math.max(
              2000,
              scrollElem.scrollHeight - 2000
            );
          }
          let scroll_bottom = setInterval(() => {
            if (
              Math.floor(scrollElem.scrollHeight - scrollElem.scrollTop) -
                scrollElem.clientHeight <=
              1
            ) {
              clearInterval(scroll_bottom);
              scroll_bottom = null;
            }
            scrollElem.scrollTop += 16;
          }, 1);
        }
      });
    },
    async searchuser() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chat/ChatFindUser",
        portType: {
          process: "8793",
        },
        data: {
          key: this.search_user,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      return res;
    },
    async getUserAndGroupList() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chat/getUserAndGroupList",
        portType: {
          process: "8793",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.chat_list_loadfinish = true;
        return;
      });
      this.user_list = res?.data;
      this.chat_list_loadfinish = true;
      // 初始化显示第一个用户聊天框
      setTimeout(() => {
        this.$nextTick(async () => {
          let list = document.getElementById("list");
          if (
            list &&
            this.user_list &&
            typeof this.user_list == "object" &&
            this.user_list.length > 0 &&
            list.children[0] &&
            list.children[0].style
          ) {
            list.children[0].style = this.user_deep_color;
            this.now_user = this.user_list[0];
            if (this.now_user.type == "group_chat") {
              this.now_post_type = "group_chat";
            } else if (this.now_user.type == "private_chat") {
              this.now_post_type = "private_chat";
            }
            this.passparam.user_id = this.now_user.id;
            for (let i = 1; i < this.now_user.length; ++i) {
              list.children[i].style = this.user_no_deep_color;
            }
            await this.getLatestChatData();
          }
          this.$nextTick(() => {
            this.to_scroll_bottom(1);
          });
        });
      }, 0);
      return res;
    },
    // 判断是否以及在聊天列表
    judge_user_list_has_persion(id) {
      let is_has = false;
      let len = this.user_list.length;
      for (let i = 0; i < len; ++i) {
        if (this.user_list[i].id == id) {
          is_has = true;
          break;
        }
      }
      return is_has;
    },
    async judgeIsJoinGroup(group_id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chat/judgeIsJoinGroup",
        portType: {
          process: "8793",
        },
        data: {
          group_id: group_id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return -1;
      });
      if (res?.code == 1) {
        return 1;
      } else if (res?.code == 0) {
        this.willJoinGroupData = res?.data;
        return 0;
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      return -1;
    },

    async handleSelect(item) {
      if (this.judge_user_list_has_persion(item.id)) {
        this.search_user = "";
        await this.openUserChatWindow(item);
        return;
      }
      item.no_look_num = 0;
      if (item.type == "group_chat") {
        // 判断是否加群
        let code = await this.judgeIsJoinGroup(item.id);
        if (code == 0) {
          this.isSeeJoinGroupDia = true;
          return;
        } else if (code == -1) {
          return;
        }
      }
      await this.user_list.push(item);
      await this.openUserChatWindow(item);
      this.search_user = "";
    },

    async openUserChatWindow(item) {
      this.id = 0;
      this.now_post_type = item.type;
      this.now_user = item;
      this.chat_msg_list = [];
      this.isSeeLastBtn = false;
      this.passparam.user_id = item.id;
      this.clearNolookNum();
      await this.getLatestChatData();
      setTimeout(() => {
        this.$nextTick(() => {
          let list = document.getElementById("list");
          if (!list) {
            return;
          }
          for (let i = 0; i < this.user_list.length; ++i) {
            if (this.user_list[i].id == item.id) {
              this.user_list[i].no_look_num = 0;
              list.children[i].style = this.user_deep_color;
            } else {
              list.children[i].style = this.user_no_deep_color;
            }
          }
        });
      }, 0);
    },

    async querySearchAsync(queryString, cb) {
      let res = await this.searchuser();
      if (res == null || !res) {
        return;
      }
      res = res?.data;
      res.forEach((t) => {
        t.value = t.name;
      });
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        cb(res);
      }, 666);
    },

    onclicklist(e) {
      let list = document.getElementById("list").children;
      for (const tem of list) {
        if (
          e.target == tem.children[0] ||
          e.target == tem.children[0].children[0].children[0] ||
          e.target == tem.children[0].children[1]
        ) {
          tem.style = this.user_deep_color;
        } else {
          tem.style = this.user_no_deep_color;
        }
      }
    },
    // 加载新消息
    async getLatestChatData() {
      try{
        if (!this.now_user.id || !this.now_user.type) {
          this.$msg({
            type: "error",
            message: "用户加载出错",
            duration: 1600,
            offset: 80,
          });
          return;
        }
        const copy_now_user_id = this.now_user.id;
        let cacheData = window.localStorage.getItem(
          "Chat " + this.now_user.type + " " + this.now_user.id
        );
        this.chat_msg_list = eval("(" + cacheData + ")");
        if (
          this.chat_msg_list &&
          typeof this.chat_msg_list == "object" &&
          this.chat_msg_list.length > 0
        ) {
          this.id = this.chat_msg_list[this.chat_msg_list.length - 1].id;
        } else {
          this.id = 0;
          this.chat_msg_list = [];
        }
        this.load_msg_list_finish = false;
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Chat/getLatestChatData",
          portType: {
            process: "8793",
          },
          data: {
            type: this.now_user.type,
            msg_id: this.id,
            user_id: this.now_user.id,
          },
        }).catch((t) => {
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
          this.load_msg_list_finish = true;
        });
        if(this.now_user.id != copy_now_user_id){
          // 切换用户了
          this.load_msg_list_finish = true;
          return;
        }
        if (res?.code == 1 || res?.code == 0) {
          if (res?.code == 0) {
            // 删除本地旧数据
            this.chat_msg_list = [];
            window.localStorage.removeItem(
              "Chat " + this.now_user.type + " " + this.now_user.id
            );
          }
          // 添加新数据
          res.data = res?.data.reverse();
          await this.chat_msg_list.push(...res?.data);
          window.localStorage.setItem(
            "Chat " + this.now_user.type + " " + this.now_user.id,
            JSON.stringify(res?.data)
          );          
        } else {
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
        }
        this.load_msg_list_finish = true;
        this.getHistoryChatData(true);
        this.$nextTick(() => {
          this.to_scroll_bottom(1);
        });
      }catch(err){
        this.load_msg_list_finish = true;
      }
    },
    // 加载历史消息
    async getHistoryChatData(is_init = false) {
      if (!this.now_user.id || !this.now_user.type) {
        !is_init && this.$msg({
          type: "error",
          message: "用户加载出错",
          duration: 1600,
          offset: 80,
        });
        return;
      }

      if (
        this.chat_msg_list &&
        typeof this.chat_msg_list == "object" &&
        this.chat_msg_list.length > 0
      ) {
        this.id = this.chat_msg_list[0].id;
      } else {
        this.id = 0;
      }

      if (!this.id) {
        this.isSeeLastBtn = false;
        !is_init && this.$msg({
          type: "success",
          message: "没有更久远的历史记录啦",
          duration: 1600,
          offset: 80,
        });
        return;
      }

      let tem_dom = document.getElementById("chatDataScrollDiv");
      if (tem_dom && tem_dom.children[1] && tem_dom.children[1].children[0]) {
        this.last_dom_id = tem_dom.children[1].children[0].id;
      }

      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Chat/getHistoryChatData",
        portType: {
          process: "8793",
        },
        data: {
          type: this.now_user.type,
          msg_id: this.id,
          user_id: this.now_user.id,
        },
      }).catch((t) => {        
        !is_init && this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        let len = res?.data.length;
        if(!is_init){
          if(len < 50){
            this.isSeeLastBtn = false;
          }
        }else{
          if(len > 0){
            this.isSeeLastBtn = true;
          }
        }
        if (len <= 0) {
          !is_init && this.$msg({
            type: "success",
            message: "没有更久远的历史记录啦",
            duration: 1600,
            offset: 80,
          });
          return;
        }
        if(!is_init){        
          res.data = res?.data.reverse();
          this.chat_msg_list = [...res?.data, ...this.chat_msg_list];
          this.to_last_scroll();
        }
      } else {
        !is_init && tthis.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      !is_init && this.getHistoryChatData(true);
    },
    // 累加未读消息
    addNoLookNum(id) {
      for (let i = 0; i < this.user_list.length; ++i) {
        if (this.user_list[i].id == id) {
          this.user_list[i].no_look_num++;
          break;
        }
      }
    },
    setup() {
      let temdata = {};
      this.$EventBus.$on("chatGetMsg", async (e) => {
        temdata = eval("(" + e.data + ")");
        if (
          temdata.msgtype &&
          (temdata.msgtype == "private_chat" || temdata.msgtype == "group_chat")
        ) {
          // 获取该用户的浏览器缓存列表
          let tem_chat_list = window.localStorage.getItem(
            "Chat " + temdata.type + " " + temdata.post_user_id
          );
          tem_chat_list = eval("(" + tem_chat_list + ")");
          // 消息发送者id
          let tem_id =
            temdata.type == "group_chat"
              ? temdata.group_data.id
              : temdata.post_user_id;
          if (this.now_user.id != tem_id) {
            if (tem_chat_list) {
              await tem_chat_list.push(temdata);
              // 不是当前擦窗口的用户 且 该用户在本地 有缓存
              window.localStorage.setItem(
                "Chat " + temdata.type + " " + tem_id,
                JSON.stringify(tem_chat_list.slice(-50))
              );
            }
          } else {
            // 是当前窗口的用户直接显示
            this.clearNolookNum();
            this.chat_msg_list.push(temdata);
            this.chat_msg_list = this.chat_msg_list.slice(-50);
            window.localStorage.setItem(
              "Chat " + temdata.type + " " + tem_id,
              JSON.stringify(this.chat_msg_list.slice(-50))
            );
            this.$nextTick(() => {
              this.to_scroll_bottom(0);
            });
          }
          // 判断用户是否在用户列表
          if (!this.judge_user_list_has_persion(tem_id)) {
            // 用户不在列表，加入列表
            if (temdata.type == "private_chat") {
              this.user_list.push({
                type: temdata.type,
                id: tem_id,
                name: temdata.name,
                headimage: temdata.headimage,
                no_look_num: 1,
              });
            } else if (temdata.type == "group_chat") {
              this.user_list.push({
                type: temdata.type,
                id: tem_id,
                name: temdata.group_data.name,
                headimage: temdata.group_data.headimage,
                no_look_num: 1,
              });
            }
          } else {
            // 在列表且不是当前窗口，累加未读消息
            if (this.now_user.id != tem_id) {
              this.addNoLookNum(tem_id);
            }
          }
        } else if (temdata.msgtype && temdata.msgtype == "create_group") {
          // 新建群聊完成时的操作
          this.isSeeJoinGroupDia = false;
          this.willJoinGroupData = {};
          this.my_join_group_code = "";
          // 群聊加入列表
          if (this.judge_user_list_has_persion(temdata.group_data.id)) {
            // 存在列表就返回
            this.addNoLookNum(temdata.group_data.id);
            return;
          }
          await this.user_list.push({
            type: temdata.type,
            id: temdata.group_data.id,
            group_data: temdata.group_data,
            name: temdata.group_data.name,
            code: temdata.group_data.code,
            headimage: temdata.group_data.headimage,
            no_look_num: 0,
          });
          this.openUserChatWindow({
            type: temdata.type,
            id: temdata.group_data.id,
            group_data: temdata.group_data,
            name: temdata.group_data.name,
            code: temdata.group_data.code,
            headimage: temdata.group_data.headimage,
          });
        } else if (temdata.msgtype && temdata.msgtype == "error") {
          this.$notice({
            title: "错误警告",
            dangerouslyUseHTMLString: true,
            message: temdata.msg,
            duration: 3600,
            offset: 80,
          });
        } else if (temdata.msgtype && temdata.msgtype == "success") {
          if (temdata.operate && temdata.operate == "join_group") {
            this.isSeeJoinGroupDia = false;
            this.willJoinGroupData = {};
            this.my_join_group_code = "";
            this.openUserChatWindow({
              type: temdata.type,
              id: temdata.group_data.id,
              group_data: temdata.group_data,
              name: temdata.group_data.name,
              code: temdata.group_data.code,
              headimage: temdata.group_data.headimage,
            });
          }
          this.$notice({
            title: "消息",
            dangerouslyUseHTMLString: true,
            message: temdata.msg,
            duration: 3600,
            offset: 80,
          });
        }
      });
    },
    judgePostMsg() {
      let t1 = this.mymessage;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "消息不能为空",
          duration: 1600,
          offset: 80,
        });
        return false;
      }
      if (this.mymessage.length > 10000) {
        this.$msg({
          type: "error",
          message: "消息不能超过10000字",
          duration: 1600,
          offset: 80,
        });
        return false;
      }
      return true;
    },
    postmessage() {
      let msgtype = this.now_post_type;
      if (!msgtype) {
        this.$msg({
          type: "error",
          message: "发送消息异常",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      let msg = {};
      if (msgtype == "private_chat" || msgtype == "group_chat") {
        if (!this.judgePostMsg()) return;
        // 私聊 和 群聊
        msg = {
          msgtype: this.msgtypeObj[msgtype],
          user_id: this.now_user.id,
          user_name: this.now_user.name,
          msg: this.mymessage,
        };
      } else if (msgtype == "create_group") {
        // 新建群聊
        msg = {
          msgtype: this.msgtypeObj[msgtype],
          group_data: this.group_data,
        };
      } else if (msgtype == "join_group") {
        // 加入群聊
        msg = {
          msgtype: this.msgtypeObj[msgtype],
          group_data: {
            group_id: this.willJoinGroupData.id,
            code: this.my_join_group_code,
          },
        };
      } else if (msgtype == "exit_group") {
        // 退出群聊
      } else if (msgtype == "delete_group") {
        // 解散群聊
      }
      this.$EventBus.$emit("chatSendMsg", msg);
      this.mymessage = "";
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
        fontSize: "1rem",
        navigation: false,
      };
      return data;
    },
  },
};
</script>

<style lang="less" scoped>
@import "../../../public/md/markdown/github-markdown.min.css";

/deep/.md,
/deep/.markdown-body {
  background-color: rgba(
    var(--ltpp-light-color),
    var(--ltpp-center-box-bk-opacity)
  ) !important;
  width: fit-content !important;
  border-width: 0rem !important;
  height: auto !important;
}

.demo-drawer__content {
  padding: 0rem 2rem;
  text-align: center;
}

.folder {
  float: left;
  background-image: url("../../assets/file.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.code {
  float: left;
  background-image: url("../../assets/code.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.music {
  float: left;
  background-image: url("../../assets/music.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.video {
  float: left;
  background-image: url("../../assets/video.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.pdf {
  float: left;
  background-image: url("../../assets/pdf.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.compressed {
  float: left;
  background-image: url("../../assets/zip.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.txt {
  float: left;
  background-image: url("../../assets/txt.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.photo {
  float: left;
  background-image: url("../../assets/photo.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

.exe {
  float: left;
  background-image: url("../../assets/exe.png");
  background-size: 80% auto;
  height: 2rem;
  width: 2rem;
  border: 1rem;
  background-repeat: no-repeat;
  margin: 0rem 1rem;
}

/deep/.creat_group_input .el-input__inner {
  border-width: 1px !important;
  border-color: aliceblue !important;
  border: dashed;
}

.user {
  border-width: 0rem;
  background-color: rgba(
    var(--ltpp-light-color),
    var(--ltpp-center-box-bk-opacity)
  );
}

.user div {
  border: 2px solid
    rgba(var(--ltpp-light-color), var(--ltpp-center-box-bk-opacity));
  position: relative;
}

.user .num {
  border: 4px solid deeppink;
  border-radius: 50%;
  width: 1.06rem;
  height: 1.06rem;
  text-align: center;
  vertical-align: middle;
  background-color: deeppink;
  position: absolute;
  top: 50%;
  right: 4%;
  transform: translate(0%, -50%);
}

.user .num .num-txt {
  position: absolute;
  top: 50%;
  right: 2%;
  transform: translate(0%, -50%);
  color: var(--ltpp-box-text-color);
}

.user .online-name {
  white-space: nowrap;
  /*强制span不换行*/
  overflow: hidden;
  width: 100px;
  color: deeppink;
  font-weight: bold;
  position: absolute;
  margin-left: 0.4rem;
  top: 50%;
  transform: translate(0%, -50%);
}

.user .group-name {
  white-space: nowrap;
  /*强制span不换行*/
  overflow: hidden;
  width: 100px;
  color: deepskyblue;
  font-weight: bold;
  position: absolute;
  margin-left: 0.4rem;
  top: 50%;
  transform: translate(0%, -50%);
}

.user .unonline-name {
  white-space: nowrap;
  /*强制span不换行*/
  overflow: hidden;
  width: 100px;
  color: #67c23a;
  font-weight: bold;
  position: absolute;
  margin-left: 0.4rem;
  top: 50%;
  transform: translate(0%, -50%);
}

.user_chat_dia {
  margin-top: 0.6rem;
  width: 100%;
  will-change: transform;
}

.main_dia {
  overflow: scroll;
}

.main_header {
  margin: 0.4rem;
  text-align: center;
  overflow: hidden;
  font-weight: bold;
  height: 2.2rem !important;
  background-image: linear-gradient(
    to bottom right,
    rgb(76, 139, 255),
    rgb(32, 209, 254)
  );
  color: var(--ltpp-box-text-color);
}

.main_header p {
  font-size: 1.46rem;
  vertical-align: middle;
  text-align: center;
}

.search_input {
  margin: 0.4rem;
}

.show_time {
  text-align: center;
  margin: 1rem;
}

.show_time span {
  padding: 0.4rem;
  background-color: rgba(248, 249, 250, 0.4);
  border-radius: 0.6rem;
}

.user_name {
  margin: 1rem;
  color: var(--ltpp-box-text-color);
  font-weight: bold;
  cursor: pointer;
}

::-webkit-scrollbar {
  z-index: 1000000;
  width: 0.16rem !important;
  height: 0rem !important;
  border-radius: 0rem;
  background-color: rgba(var(--ltpp-main-bk-color), 0);
}

/* 滚动条上的按钮 (上下箭头). */
::-webkit-scrollbar-button {
  background-color: Transparent;
  border-radius: 0rem;
  height: 0rem;
  width: 0rem;
}

/* 滚动条上的滚动滑块. */
::-webkit-scrollbar-thumb {
  background-color: rgb(183, 185, 186);
  border-radius: 0rem;
}

/*  滚动条轨道. */
::-webkit-scrollbar-track {
  border-radius: 0rem;
  background-color: rgba(var(--ltpp-main-bk-color), 0);
}

/* 滚动条没有滑块的轨道部分 */
::-webkit-scrollbar-track-piece {
  background-color: rgba(var(--ltpp-main-bk-color), 0);
}

/* 当同时有垂直滚动条和水平滚动条时交汇的部分. */
::-webkit-scrollbar-corner {
  background-color: rgba(var(--ltpp-main-bk-color), 0);
  border-radius: 0rem;
}

/* 某些元素的corner部分的部分样式(例:textarea的可拖动按钮). */
::-webkit-resizer {
  width: 0rem;
  height: 0rem;
  background-color: Transparent;
}
</style>
