<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select"
  >
    <div style="height: 1rem"></div>
    <div
      v-loading.lock="!loadfinish"
      element-loading-text="拼命加载中"
      element-loading-spinner="el-icon-loading"
      element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"
    >
      <div class="shadow main-center-box-content">
        <div style="height: 1rem"></div>
        <div style="display: flex; flex-direction: row">
          <el-avatar
            style="height: 3.6rem; width: 3.6rem; margin-left: 1rem"
            :src="data['headimage']"
            alt=""
          ></el-avatar>
          <p
            @click="touserpage(data['userid'])"
            style="
              cursor: pointer;
              font-size: 1.06rem;
              font-weight: bold;
              color: deepskyblue;
              margin-top: 1rem;
              margin-left: 1rem;
            "
          >
            {{ data["writer"] }}
          </p>
          <p
            style="
              font-size: 1.06rem;
              color: rgba(38, 205, 77, 1);
              margin-top: 0.96rem;
              margin-left: 1.06rem;
            "
          >
            提问于：{{ data["time"] }}
          </p>
        </div>
        <div style="height: 1rem"></div>
        <div style="margin-left: 1.6rem; margin-right: 1.6rem">
          <div
            class="markdown-body"
            @dblclick="copyText(data['question'], '问题内容', data['writer'])"
          >
            <mavon-editor
              class="md"
              :codeStyle="prop.codeStyle"
              :toolbars="toolbars"
              :value="data['question'] || '<br>'"
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
          <div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  class="pulse-enter-active"
                  effect="dark"
                  type="danger"
                  style="cursor: pointer; font-size: 1.06rem; font-weight: bold"
                  @click="touserpage(data['userid'])"
                  >发布者：{{ data["writer"] }}
                </el-tag>
              </p>
            </div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  effect="dark"
                  type="success"
                  style="font-size: 1.06rem; font-weight: bold"
                  >发布于：{{ data["time"] }}
                </el-tag>
              </p>
            </div>
          </div>
          <div style="clear: both"></div>
          <div style="height: 1rem"></div>
          <!-- 发表回答 -->
          <div class="no-select">
            <div class="markdown-body">
              <mavon-editor
                ref="md1"
                @imgAdd="$imgAdd1"
                class="md"
                :ishljs="true"
                :toolbars="toolbars"
                v-model.lazy="answertext"
                :subfield="false"
                defaultOpen="edit"
                :toolbarsFlag="true"
                :editable="true"
                :scrollStyle="true"
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
                style="min-height: 16rem !important; height: auto !important"
              >
                <!-- 引用视频链接的自定义按钮 -->
                <template v-slot:left-toolbar-after>
                  <!--点击按钮触发的事件是打开表单对话框-->
                  <el-button
                    type="text"
                    @click="
                      form.region = 'url';
                      dialogFormVisible1 = true;
                    "
                    aria-hidden="true"
                    class="op-icon fa"
                    title="插入视频资源"
                  >
                    <i class="el-icon-video-camera-solid" />
                  </el-button>
                  <el-button
                    type="text"
                    @click="changeImageSaveType"
                    aria-hidden="true"
                    class="op-icon fa"
                    title="切换图片保存方式"
                  >
                    <i
                      v-if="$store.state.image_use_remote"
                      class="el-icon-upload"
                    />
                    <i v-else class="el-icon-picture" />
                  </el-button>
                </template>
                <!-- 发表回答 -->
                <template v-slot:right-toolbar-after>
                  <el-button
                    type="text"
                    aria-hidden="true"
                    class="op-icon fa"
                    style="color: deepskyblue !important"
                    title="发表回答"
                    @click="
                      touserid = 0;
                      addanswer();
                    "
                  >
                    <i class="el-icon-s-promotion" />
                  </el-button>
                </template>
              </mavon-editor>
            </div>

            <div style="height: 8rem"></div>
            <div slot="footer">
              <el-button
                type="text"
                style="
                  float: left;
                  margin-left: 0rem;
                  color: chartreuse;
                  font-size: 1.06rem;
                  cursor: auto;
                "
                width="auto"
                class="el-icon-message-solid"
              >
                累计回答：{{ data["answer_num"] }}次</el-button
              >
              <el-button
                type="text"
                @click="tohome()"
                width="auto"
                style="
                  float: right;
                  margin-right: 0rem;
                  color: red;
                  font-size: 1.06rem;
                "
                class="el-icon-s-unfold pulse-enter-active"
              >
                返回</el-button
              >
              <el-button
                type="text"
                style="
                  float: right;
                  margin-right: 2rem;
                  color: aqua;
                  font-size: 1.06rem;
                "
                @click="btnExport()"
                width="auto"
                class="el-icon-s-platform pulse-enter-active"
              >
                下载</el-button
              >
              <el-button
                v-show="!islove"
                type="text"
                style="
                  float: right;
                  margin-right: 2rem;
                  color: aqua;
                  font-size: 1.06rem;
                "
                @click="collectionClick()"
                width="auto"
                class="el-icon-star-off pulse-enter-active"
              >
                收藏
              </el-button>

              <el-button
                v-show="islove"
                type="text"
                style="
                  float: right;
                  margin-right: 2rem;
                  color: deeppink;
                  font-size: 1.06rem;
                "
                @click="delcollectionClick()"
                width="auto"
                class="el-icon-star-on pulse-enter-active"
              >
                取消收藏
              </el-button>

              <el-button
                v-if="is_can_edit"
                type="text"
                style="
                  float: right;
                  margin-right: 2rem;
                  color: red;
                  font-size: 1.06rem;
                "
                @click="toupdateonequestion()"
                width="auto"
                class="el-icon-s-order pulse-enter-active"
              >
                编辑</el-button
              >
            </div>
          </div>
          <div style="height: 4rem"></div>

          <div>
            <div style="height: 3.2rem"></div>
            <div>
              <!-- 查看回答 -->
              <div v-show="answer.length <= 0" class="no-select">
                <p
                  style="
                    font-size: 1.06rem;
                    font-weight: bold;
                    text-align: center;
                  "
                >
                  暂无回答
                </p>
                <div style="height: 2.6rem"></div>
              </div>
              <div v-for="temanswer in answer" :key="temanswer.index">
                <div>
                  <!-- 回答 -->
                  <div>
                    <el-avatar
                      :src="temanswer.userheadimg"
                      style="height: 3rem; width: 3rem; float: left"
                    >
                    </el-avatar>
                    <el-tooltip
                      class="item"
                      effect="dark"
                      :content="'用户名：' + temanswer.username"
                      placement="right"
                    >
                      <el-button
                        class="no-select"
                        type="text"
                        style="
                          font-size: 1.06rem !important;
                          color: deeppink;
                          float: left;
                          margin-left: 0.16rem;
                          margin-top: 0.2rem;
                        "
                        @click="touserpage(temanswer.userid)"
                        >{{ temanswer.username }}</el-button
                      >
                    </el-tooltip>
                    <el-button
                      class="no-select"
                      type="text"
                      style="
                        font-size: 1.06rem !important;
                        color: greenyellow;
                        float: left;
                        cursor: auto;
                        margin-top: 0.16rem;
                      "
                      >（回答时间：{{ temanswer.time }}）</el-button
                    >
                    <div style="clear: both; height: 0.6rem"></div>
                    <div
                      class="markdown-body"
                      @dblclick="
                        copyText(temanswer.answer, '回答', temanswer.username)
                      "
                    >
                      <mavon-editor
                        class="md shadow"
                        ref="md2"
                        :ishljs="true"
                        :toolbars="toolbars"
                        :value="temanswer.answer || '<br>'"
                        :subfield="prop.subfield"
                        :defaultOpen="prop.defaultOpen"
                        :toolbarsFlag="prop.toolbarsFlag"
                        :editable="prop.editable"
                        :scrollStyle="prop.scrollStyle"
                        :boxShadow="prop.boxShadow"
                        :fontSize="prop.fontSize"
                        :codeStyle="prop.codeStyle"
                        :tabSize="prop.tabSize"
                        :toolbarsBackground="prop.toolbarsBackground"
                        :previewBackground="prop.previewBackground"
                        :externalLink="externalLink"
                        :editorBackground="prop.editorBackground"
                        :xssOptions="xss_options"
                        :stripIgnoreTagBody="stripIgnoreTagBody"
                        style="
                          min-height: 0rem !important;
                          height: auto !important;
                          color: var(--ltpp-main-text-color) !important;
                        "
                      ></mavon-editor>
                    </div>
                    <div style="text-align: right" class="no-select">
                      <el-button
                        style="
                          font-size: 1.2rem;
                          color: deeppink;
                          margin-right: 2rem;
                        "
                        width="auto"
                        type="text"
                        class="el-icon-delete pulse-enter-active"
                        v-show="
                          temanswer.userid == myid ||
                          ($store.state.root &&
                            $store.state.my_name === $SqsGlobal.root_name)
                        "
                        @click="deleteanswer(temanswer.id)"
                      ></el-button>
                      <el-button
                        style="font-size: 1.2rem"
                        width="auto"
                        type="text"
                        class="el-icon-s-comment pulse-enter-active"
                        @click="
                          touserid = temanswer.userid;
                          mainanswer_id = temanswer.id;
                          istouseranswer = true;
                        "
                      ></el-button>
                    </div>
                  </div>
                  <!-- 回答回复 -->
                  <div>
                    <div
                      v-for="temtouseranswer in temanswer.touserarray"
                      :key="temtouseranswer.index"
                    >
                      <div style="margin-left: 3.6rem">
                        <el-avatar
                          :src="temtouseranswer.userheadimg"
                          style="height: 3.4rem; width: 3.4rem; float: left"
                        >
                        </el-avatar>
                        <el-tooltip
                          class="item"
                          effect="dark"
                          :content="'用户名：' + temtouseranswer.username"
                          placement="right"
                        >
                          <el-button
                            class="no-select"
                            type="text"
                            style="
                              font-size: 1.06rem !important;
                              color: deeppink;
                              float: left;
                              margin-left: 0.16rem;
                              margin-top: 0.4rem;
                            "
                            @click="touserpage(temtouseranswer.userid)"
                          >
                            {{ temtouseranswer.username }}
                          </el-button>
                        </el-tooltip>
                        <el-button
                          class="no-select"
                          type="text"
                          style="
                            font-size: 1.06rem !important;
                            color: deepskyblue;
                            float: left;
                            margin-left: 1rem;
                            cursor: auto;
                            margin-top: 0.46rem;
                          "
                          >回复</el-button
                        >
                        <el-avatar
                          :src="temtouseranswer.touserheadimg"
                          style="
                            height: 3.4rem;
                            width: 3.4rem;
                            float: left;
                            margin-left: 1rem;
                          "
                        >
                        </el-avatar>
                        <el-tooltip
                          class="item"
                          effect="dark"
                          :content="'用户名：' + temtouseranswer.tousername"
                          placement="right"
                        >
                          <el-button
                            class="no-select"
                            type="text"
                            style="
                              font-size: 1.06rem !important;
                              color: deeppink;
                              float: left;
                              margin-left: 0.16rem;
                              margin-top: 0.4rem;
                            "
                            @click="touserpage(temtouseranswer.touserid)"
                          >
                            {{ temtouseranswer.tousername }}
                          </el-button>
                        </el-tooltip>
                        <el-button
                          class="no-select"
                          type="text"
                          style="
                            font-size: 1.06rem !important;
                            color: deepskyblue;
                            float: left;
                            cursor: auto;
                            margin-top: 0.4rem;
                          "
                          >（回答时间：{{ temtouseranswer.time }}）
                        </el-button>
                        <div style="clear: both; height: 0.6rem"></div>
                        <div
                          class="markdown-body"
                          @dblclick="
                            copyText(
                              temtouseranswer.answer,
                              '回答',
                              temtouseranswer.username
                            )
                          "
                        >
                          <mavon-editor
                            class="md shadow"
                            ref="md3"
                            :ishljs="true"
                            :toolbars="toolbars"
                            :value="temtouseranswer.answer || '<br>'"
                            :subfield="prop.subfield"
                            :defaultOpen="prop.defaultOpen"
                            :toolbarsFlag="prop.toolbarsFlag"
                            :editable="prop.editable"
                            :scrollStyle="prop.scrollStyle"
                            :boxShadow="prop.boxShadow"
                            :fontSize="prop.fontSize"
                            :codeStyle="prop.codeStyle"
                            :tabSize="prop.tabSize"
                            :toolbarsBackground="prop.toolbarsBackground"
                            :previewBackground="prop.previewBackground"
                            :editorBackground="prop.editorBackground"
                            :externalLink="externalLink"
                            :xssOptions="xss_options"
                            :stripIgnoreTagBody="stripIgnoreTagBody"
                            style="
                              min-height: 0rem !important;
                              height: auto !important;
                              color: var(--ltpp-main-text-color) !important;
                            "
                          ></mavon-editor>
                        </div>
                        <div style="text-align: right" class="no-select">
                          <el-button
                            style="
                              font-size: 1.2rem;
                              color: deeppink;
                              margin-right: 2rem;
                            "
                            width="auto"
                            type="text"
                            class="el-icon-delete pulse-enter-active"
                            v-show="
                              temtouseranswer.userid == myid ||
                              ($store.state.root &&
                                $store.state.my_name === $SqsGlobal.root_name)
                            "
                            @click="deleteanswer(temtouseranswer.id)"
                          ></el-button>

                          <el-button
                            style="font-size: 1.2rem"
                            width="auto"
                            type="text"
                            class="el-icon-s-comment pulse-enter-active"
                            @click="
                              touserid = temtouseranswer.userid;
                              mainanswer_id = temtouseranswer.mainanswerid;
                              istouseranswer = true;
                            "
                          ></el-button>
                        </div>
                      </div>
                      <div></div>
                    </div>
                  </div>
                  <div style="height: 1rem"></div>
                </div>
              </div>
            </div>
          </div>
          <!-- 回复用户 -->
          <el-dialog
            :close-on-click-modal="false"
            :append-to-body="true"
            class="no-select"
            :visible.sync="istouseranswer"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
            title="回复回答"
          >
            <div class="markdown-body">
              <mavon-editor
                ref="md4"
                @imgAdd="$imgAdd4"
                class="md"
                v-model.lazy="touseranswertext"
                :toolbars="toolbars"
                :ishljs="true"
                :subfield="false"
                defaultOpen="edit"
                :toolbarsFlag="true"
                :editable="true"
                :scrollStyle="true"
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
                style="min-height: 16rem; height: auto; border-width: 0rem"
              >
                <!-- 引用视频链接的自定义按钮 -->
                <template v-slot:left-toolbar-after>
                  <!--点击按钮触发的事件是打开表单对话框-->
                  <el-button
                    type="text"
                    @click="
                      form.region = 'url';
                      dialogFormVisible2 = true;
                    "
                    aria-hidden="true"
                    class="op-icon fa"
                    title="插入视频资源"
                  >
                    <i class="el-icon-video-camera-solid" />
                  </el-button>
                  <el-button
                    type="text"
                    @click="changeImageSaveType"
                    aria-hidden="true"
                    class="op-icon fa"
                    title="切换图片保存方式"
                  >
                    <i
                      v-if="$store.state.image_use_remote"
                      class="el-icon-upload"
                    />
                    <i v-else class="el-icon-picture" />
                  </el-button>
                </template>
                <!-- 回复回答 -->
                <template v-slot:right-toolbar-after>
                  <el-button
                    type="text"
                    aria-hidden="true"
                    class="op-icon fa"
                    style="color: deepskyblue !important"
                    title="回复回答"
                    @click="
                      addtouseranswer();
                      istouseranswer = false;
                    "
                  >
                    <i class="el-icon-s-promotion" />
                  </el-button>
                </template>
              </mavon-editor>
            </div>
          </el-dialog>

          <!-- 插入视频链接的dialog提示框，表单对话框 -->
          <el-dialog
            :close-on-click-modal="false"
            title="插入视频资源"
            :append-to-body="true"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
            :visible.sync="dialogFormVisible1"
          >
            <el-form :model="form">
              <el-form-item label="视频链接" :label-width="formLabelWidth">
                <el-input
                  v-model.lazy="form.link"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
              <el-form-item label="链接类型" :label-width="formLabelWidth">
                <el-select
                  v-model.lazy="form.region"
                  placeholder="请选择链接类型"
                >
                  <el-option label="iframe标签" value="iframe"></el-option>
                  <el-option label="url链接" value="url"></el-option>
                </el-select>
              </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
              <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
              <el-button type="primary" @click="videoLink1">确 定</el-button>
              <el-button
                style="margin-left: 2rem"
                type="warning"
                @click="dialogFormVisible1 = false"
                >取 消</el-button
              >
            </div>
          </el-dialog>
          <!-- 插入视频链接的dialog提示框，表单对话框 -->
          <el-dialog
            :close-on-click-modal="false"
            class="no-select"
            title="插入视频资源"
            :append-to-body="true"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
            :visible.sync="dialogFormVisible2"
          >
            <el-form :model="form">
              <el-form-item label="视频链接" :label-width="formLabelWidth">
                <el-input
                  v-model.lazy="form.link"
                  autocomplete="off"
                ></el-input>
              </el-form-item>
              <el-form-item label="链接类型" :label-width="formLabelWidth">
                <el-select
                  v-model.lazy="form.region"
                  placeholder="请选择链接类型"
                >
                  <el-option label="iframe标签" value="iframe"></el-option>
                  <el-option label="url链接" value="url"></el-option>
                </el-select>
              </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
              <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
              <el-button type="primary" @click="videoLink2">确 定</el-button>
              <el-button
                style="margin-left: 2rem"
                type="warning"
                @click="dialogFormVisible2 = false"
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
        </div>
        <div style="height: 2rem"></div>
      </div>
      <div style="height: 2rem"></div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
import "../../../public/md/css/index.css";

export default {
  name: "onequestion",
  async activated() {
    this.data = {};
    this.answer = [];
    if (
      !(
        this.$route &&
        this.$route.query &&
        this.$route.query.path &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.loadfinish = true;
      this.$router.go(-1);
      return;
    }
    this.loadfinish = false;
    this.form.region = "url";
    this.fronturl = this.getFronturl();
    this.question_id = urlencode.decode(this.$route.query.path, "gbk");
    this.islove = false;
    this.islock = false;
    this.getmyid();
    await this.lookquestion();
    await this.lookanswer();
    this.$nextTick(() => {
      this.totop();
    });
    window.addEventListener("scroll", this.addlist);
  },

  deactivated() {
    this.islock = true;
    this.data = {};
    this.answer = [];
    window.removeEventListener("scroll", this.addlist);
  },

  destroyed() {
    this.islock = true;
    this.data = {};
    this.answer = [];
    window.removeEventListener("scroll", this.addlist);
  },

  data() {
    return {
      fronturl: window?.location?.href,
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      dialogFormVisible1: false, // 用于控制表单对话框的开启和关闭
      dialogFormVisible2: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      islock: false /* 锁 */,
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      touseranswertext: "",
      mainanswer_id: 0,
      touserid: 0,
      istouseranswer: false,
      is_can_edit: false,
      data: {},
      total: 0,
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
      answernum: "",
      answer: [],
      answertext: "",
      myid: 0,
      islove: false,
      loadfinish: false,
    };
  },
  methods: {
    async addlist() {
      //加载更多
      let scrollTop =
        document.documentElement.scrollTop || document.body.scrollTop;
      //变量windowHeight是可视区的高度
      let windowHeight =
        document.documentElement.clientHeight || document.body.clientHeight;
      //变量scrollHeight是滚动条的总高度
      let scrollHeight =
        document.documentElement.scrollHeight || document.body.scrollHeight;
      //滚动条到底部的条件
      if (!(scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100)) {
        return;
      }
      await this.lookanswer();
    },
    btnExport() {
      const url = window.URL.createObjectURL(
        new Blob([this.data["question"]], {
          type: "application/vnd.ms-excel,charset=utf-8",
        })
      );
      const link = document.createElement("a");
      const fileName = this.data["question"].slice(0, 36) + ".md";
      link.style.display = "none";
      link.href = url;
      link.setAttribute("download", fileName);

      document.body.appendChild(link);
      link.click();
      URL.revokeObjectURL(link.href); // 释放URL对象
      document.body.removeChild(link);
      this.$msg({
        type: "success",
        message: "开始下载！",
        duration: 800,
        offset: 80,
      });
    },

    toupdateonequestion() {
      //加载修改回答
      this.question_id &&
        this.$router.push({
          path: "/updateonequestion",
          query: {
            path: urlencode(this.question_id, "gbk"),
          },
        });
    },

    async copyText(text, msgType, userName) {
      let target = document.createElement("textarea"); //创建textarea节点
      let url = window.location.href;
      if (
        this.fronturl != undefined &&
        this.fronturl &&
        this.fronturl != null
      ) {
        let loc = url.indexOf("/onequestion");
        let oriurl = url;
        let len = oriurl.length;
        url = this.fronturl;
        for (let i = loc; i < len; ++i) {
          url += oriurl[i];
        }
      }
      target.setAttribute("id", "LTPPSQScopyText"); //添加id
      if (msgType == "回答") {
        target.value =
          text +
          "\n\n————————————\n" +
          "版权声明：本内容为LTPP用户「" +
          userName +
          "」的回答，著作权归该用户所有，商业转载请联系该用户获得授权，非商业转载请注明出处。\n" +
          "原文链接：" +
          url; // 给textarea的value赋值
      } else {
        target.value =
          text +
          "\n\n————————————\n" +
          "版权声明：本文为LTPP用户「" +
          userName +
          "」的问题，著作权归作者所有，商业转载请联系作者获得授权，非商业转载请注明出处。\n" +
          "原文链接：" +
          url; // 给textarea的value赋值
      }
      document.body.appendChild(target); // 向页面插入textarea节点
      target.select(); // 选中input
      try {
        await document.execCommand("Copy"); // 执行浏览器复制命令
        this.$msg({
          type: "success",
          message: "复制" + msgType + "成功",
          duration: 800,
          offset: 80,
        });
      } catch {
        this.$msg({
          type: "error",
          message: "复制" + msgType + "失败",
          duration: 800,
          offset: 80,
        });
      }
      let deldom = document.getElementById("LTPPSQScopyText"); //根据id选择节点
      deldom.parentNode.removeChild(deldom); //删除节点
    },
    videoLink1() {
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
        this.dialogFormVisible1 = false;
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
      let subStart = this.$refs.md1.d_value.substring(0, posStart);
      let subEnd = this.$refs.md1.d_value.substring(
        posEnd,
        this.$refs.md1.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.md1.d_value = subStart + "\n" + linkFrame + "\n" + subEnd;
      // 关闭对话框
      this.dialogFormVisible1 = false;
    },
    videoLink2() {
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
        this.dialogFormVisible2 = false;
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
      let subStart = this.$refs.md4.d_value.substring(0, posStart);
      let subEnd = this.$refs.md4.d_value.substring(
        posEnd,
        this.$refs.md4.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.md4.d_value = subStart + "\n" + linkFrame + "\n" + subEnd;
      // 关闭对话框
      this.dialogFormVisible2 = false;
    },
    getmyid() {
      this.myid = this.getMyId();
    },
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
    tohome() {
      this.$router.go(-1);
    },

    async $imgAdd1(pos, $file) {
      // 第一步.将图片上传到服务器.
      let formdata = new FormData();
      formdata.append("file", $file);
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
          this.$refs.md1.$img2Url(pos, res?.data.url);
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
    async $imgAdd4(pos, $file) {
      // 第一步.将图片上传到服务器.
      let formdata = new FormData();
      formdata.append("file", $file);
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
          this.$refs.md4.$img2Url(pos, res?.data.url);
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
    async lookquestion() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Question/loadOneQuestion",
        portType: {
          process: "8792",
        },
        data: {
          question_id: this.question_id,
        },
      }).catch((t) => {
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
      if (res?.code == 1) {
        this.data = res?.data;
        this.islove = res?.data.islove;
        this.is_can_edit = res.is_can_edit;
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
    async lookanswer() {
      if (this.islock) {
        return;
      }
      this.islock = true;
      this.$ajax({
        method: "post",
        url: "/Answer/loadAnswer",
        portType: {
          process: "8798",
        },
        data: {
          question_id: this.question_id,
          answer_id:
            this.answer?.length > 0
              ? this.answer[this.answer?.length - 1].id
              : 0,
        },
      })
        .then((res) => {
          this.answernum = res?.data.allnum;
          if (this.answernum != 0) {
            if (res?.data.data.length > 0) {
              res?.data.data.forEach((tem) => {
                this.answer.push(tem);
              });
            } else {
              this.$msg({
                type: "success",
                message: "没有更多回答了！",
                duration: 800,
                offset: 80,
              });
            }
          }
          setTimeout(() => {
            this.islock = false;
          }, 360);
        })
        .catch((t) => {
          setTimeout(() => {
            this.islock = false;
          }, 360);
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
    },

    async deleteanswer(answer_id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Answer/deleteanswer",
        portType: {
          process: "8798",
        },
        data: {
          answer_id: answer_id,
          question_id: this.question_id,
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
        let len = this.answer.length;
        for (let i = 0; i < len; ++i) {
          let tlen = this.answer[i].touserarray.length;
          if (this.answer[i].id == answer_id) {
            this.answer.splice(i, 1);
            break;
          }
          if (tlen > 0) {
            for (let j = 0; j < tlen; ++j) {
              if (this.answer[i].touserarray[j].id == answer_id) {
                this.answer[i].touserarray.splice(j, 1);
                break;
              }
            }
          }
        }
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
          duration: 800,
          offset: 80,
        });
      }
    },

    async addanswer() {
      let t1 = this.answertext;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "回答不能为空",
          duration: 800,
          offset: 80,
        });
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Answer/addAnswer",
        portType: {
          process: "8798",
        },
        data: {
          question_id: this.question_id,
          answer: this.answertext,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.touserid = 0;
      if (res?.code == 1) {
        this.answertext = "";
        this.data["answer_num"]++;
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1800,
          offset: 80,
        });
      }
      this.answer = [];
      this.islock = false;
      await this.lookanswer();
    },
    async addtouseranswer() {
      let t1 = this.touseranswertext;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "回答不能为空",
          duration: 800,
          offset: 80,
        });
        return;
      }

      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Answer/addToUserAnswer",
        portType: {
          process: "8798",
        },
        data: {
          question_id: this.question_id,
          answer: this.touseranswertext,
          touser_id: this.touserid,
          mainanswer_id: this.mainanswer_id,
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
        this.touseranswertext = "";
        this.data["answer_num"]++;
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
          duration: 800,
          offset: 80,
        });
      }
      this.answer = [];
      this.islock = false;
      await this.lookanswer();
    },

    async collectionClick() {
      this.islove = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Question/collectionOneQuestion",
        portType: {
          process: "8792",
        },
        data: {
          question_id: this.question_id,
        },
      }).catch((t) => {
        this.islove = false;
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
        this.islove = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 800,
          offset: 80,
        });
      }
    },

    async delcollectionClick() {
      this.islove = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Question/deleteLoveQuestion",
        portType: {
          process: "8792",
        },
        data: {
          question_id: this.question_id,
        },
      }).catch((t) => {
        this.islove = true;
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
        this.islove = true;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 800,
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
<style  lang="less" scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
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