<template>
  <div
    v-show="isseetip"
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
        <div style="margin-left: 1.6rem; margin-right: 1.6rem">
          <div>
            <div style="height: 2rem"></div>
            <div
              class="can-select"
              style="
                color: var(--ltpp-title-color);
                border-width: 0rem;
                min-height: 3rem;
                text-align: left;
                margin-left: 1.38rem;
                font-weight: bold;
                font-size: 1.66rem;
                overflow: hidden;
              "
            >
              {{ this.name }}
            </div>
          </div>
          <div
            style="text-align: right"
            v-show="problemid != '' && problemid != 0"
          >
            <el-button
              type="text"
              @click="topropage()"
              class="el-icon-s-opportunity pulse-enter-active"
              style="
                font-size: 1.06rem;
                margin: 0rem 2rem;
                color: var(--ltpp-title-color);
                font-weight: bold;
              "
            >
              去 挑 战 此 题 ->
            </el-button>
          </div>
          <div class="markdown-body">
            <mavon-editor
              class="md can-select"
              :codeStyle="prop.codeStyle"
              :toolbars="toolbars"
              :value="article || '<br>'"
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
                  size="small"
                  class="pulse-enter-active"
                  effect="dark"
                  type="danger"
                  style="cursor: pointer; font-size: 1.06rem; font-weight: bold"
                  @click="touserpage(writerid)"
                  >发布者：{{ writer }}
                </el-tag>
              </p>
            </div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  size="small"
                  effect="dark"
                  type="success"
                  style="font-size: 1.06rem; font-weight: bold"
                  >发布于：{{ releasetime }}
                </el-tag>
              </p>
            </div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  size="small"
                  effect="dark"
                  type="success"
                  style="font-size: 1.06rem; font-weight: bold"
                  >最后一次修改：{{ lastchangetime }}
                </el-tag>
              </p>
            </div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  size="small"
                  effect="dark"
                  type="success"
                  style="font-size: 1.06rem; font-weight: bold"
                  >点赞数：{{ fabulous }}
                </el-tag>
              </p>
            </div>
            <div style="float: left" class="no-select">
              <p style="margin: 1rem 1rem 0.5rem 1rem">
                <el-tag
                  size="small"
                  effect="dark"
                  type="success"
                  style="font-size: 1.06rem; font-weight: bold"
                  >收藏数：{{ collection }}
                </el-tag>
              </p>
            </div>
          </div>
          <div style="clear: both"></div>
          <div style="height: 1rem"></div>
          <!-- 发表评论 -->
          <div class="no-select">
            <div class="markdown-body">
              <mavon-editor
                ref="md1"
                @imgAdd="$imgAdd1"
                class="md"
                :ishljs="true"
                :toolbars="toolbars"
                v-model.lazy="commenttext"
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
                <!-- 发表评论 -->
                <template v-slot:right-toolbar-after>
                  <el-button
                    v-if="issendfinish"
                    type="text"
                    aria-hidden="true"
                    class="op-icon fa"
                    style="color: deepskyblue !important"
                    title="发表评论"
                    @click="
                      touserid = 0;
                      addcomment();
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
                @click="tohome()"
                width="auto"
                style="
                  float: right;
                  margin-right: 0rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                class="el-icon-s-unfold pulse-enter-active"
              >
                返回</el-button
              >
              <el-button
                v-show="!islove"
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
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
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                @click="delcollectionClick()"
                width="auto"
                class="el-icon-star-on pulse-enter-active"
              >
                取消收藏
              </el-button>

              <el-button
                v-show="!isfabulous"
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                @click="fabulousClick()"
                width="auto"
                class="el-icon-message-solid pulse-enter-active"
              >
                点赞</el-button
              >
              <el-button
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                @click="btnExport()"
                width="auto"
                class="el-icon-s-platform pulse-enter-active"
              >
                下载</el-button
              >
              <el-button
                v-if="is_public == 1"
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                @click="shareArticle()"
                width="auto"
                class="el-icon-share pulse-enter-active"
              >
                分享</el-button
              >
              <el-button
                v-if="is_can_edit"
                style="
                  float: right;
                  margin-right: 2rem;
                  background-color: rgba(
                    var(--ltpp-main-bk-color),
                    1
                  ) !important;
                  color: rgba(var(--ltpp-light-color), 1) !important;
                  font-size: 1.06rem;
                "
                @click="toupdateonearticle()"
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
              <!-- 查看评论 -->
              <div v-show="comment.length <= 0" class="no-select">
                <p
                  style="font-size: 1rem; font-weight: bold; text-align: center"
                >
                  暂无评论
                </p>
                <div style="height: 2.6rem"></div>
              </div>
              <div v-for="temcomment in comment" :key="temcomment.index">
                <div>
                  <!-- 评论 -->
                  <div>
                    <el-avatar
                      :src="temcomment.userheadimg"
                      style="height: 3rem; width: 3rem; float: left"
                    >
                    </el-avatar>
                    <el-tooltip
                      class="item"
                      effect="dark"
                      :content="'用户名：' + temcomment.username"
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
                        @click="touserpage(temcomment.userid)"
                        >{{ temcomment.username }}</el-button
                      >
                    </el-tooltip>
                    <el-button
                      class="no-select"
                      type="text"
                      style="
                        font-size: 1rem !important;
                        color: greenyellow;
                        float: left;
                        cursor: auto;
                        margin-top: 0.16rem;
                      "
                      >（评论时间：{{ temcomment.time }}）</el-button
                    >
                    <div style="clear: both; height: 0.56rem"></div>
                    <div class="markdown-body">
                      <mavon-editor
                        class="md shadow can-select"
                        ref="md2"
                        :ishljs="true"
                        :toolbars="toolbars"
                        :value="temcomment.text || '<br>'"
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
                          temcomment.userid == myid ||
                          ($store.state.root &&
                            $store.state.my_name === $SqsGlobal.root_name)
                        "
                        @click="deletecomment(temcomment.id)"
                      ></el-button>
                      <el-button
                        style="font-size: 1.2rem"
                        width="auto"
                        type="text"
                        class="el-icon-s-comment pulse-enter-active"
                        @click="
                          touserid = temcomment.userid;
                          maincomment_id = temcomment.id;
                          istousercomment = true;
                        "
                      ></el-button>
                    </div>
                  </div>
                  <!-- 评论回复 -->
                  <div>
                    <div
                      v-for="temtousercomment in temcomment.touserarray"
                      :key="temtousercomment.index"
                    >
                      <div style="margin-left: 3.6rem">
                        <el-avatar
                          :src="temtousercomment.userheadimg"
                          style="height: 3.4rem; width: 3.4rem; float: left"
                        >
                        </el-avatar>
                        <el-tooltip
                          class="item"
                          effect="dark"
                          :content="'用户名：' + temtousercomment.username"
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
                            @click="touserpage(temtousercomment.userid)"
                          >
                            {{ temtousercomment.username }}
                          </el-button>
                        </el-tooltip>
                        <el-button
                          class="no-select"
                          type="text"
                          style="
                            font-size: 1rem !important;
                            color: deepskyblue;
                            float: left;
                            margin-left: 1rem;
                            cursor: auto;
                            margin-top: 0.46rem;
                          "
                          >回复</el-button
                        >
                        <el-avatar
                          :src="temtousercomment.touserheadimg"
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
                          :content="'用户名：' + temtousercomment.tousername"
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
                            @click="touserpage(temtousercomment.touserid)"
                          >
                            {{ temtousercomment.tousername }}
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
                          >（评论时间：{{ temtousercomment.time }}）
                        </el-button>
                        <div style="clear: both; height: 0.56rem"></div>
                        <div class="markdown-body">
                          <mavon-editor
                            class="md shadow can-select"
                            ref="md3"
                            :ishljs="true"
                            :toolbars="toolbars"
                            :value="temtousercomment.text || '<br>'"
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
                              temtousercomment.userid == myid ||
                              ($store.state.root &&
                                $store.state.my_name === $SqsGlobal.root_name)
                            "
                            @click="deletecomment(temtousercomment.id)"
                          ></el-button>

                          <el-button
                            style="font-size: 1.2rem"
                            width="auto"
                            type="text"
                            class="el-icon-s-comment pulse-enter-active"
                            @click="
                              touserid = temtousercomment.userid;
                              maincomment_id = temtousercomment.maincommentid;
                              istousercomment = true;
                            "
                          ></el-button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 回复用户 -->
            <el-dialog
              :close-on-click-modal="false"
              class="no-select"
              :append-to-body="true"
              :visible.sync="istousercomment"
              :width="
                ($store.state.max_width / $store.state.now_width) * 100 + '%'
              "
              title="回复评论"
            >
              <div class="markdown-body">
                <mavon-editor
                  ref="md4"
                  @imgAdd="$imgAdd4"
                  class="md"
                  v-model.lazy="tousercommenttext"
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
                  <!-- 回复评论 -->
                  <template v-slot:right-toolbar-after>
                    <el-button
                      v-if="issendfinish"
                      type="text"
                      aria-hidden="true"
                      class="op-icon fa"
                      style="color: deepskyblue !important"
                      title="回复评论"
                      @click="
                        addtousercomment();
                        istousercomment = false;
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
              <span class="my-span"
                >视频链接格式错误，请重新确认后再输入！</span
              >
              <span slot="footer" class="dialog-footer my-span">
                <el-button type="primary" @click="dialogVisible = false"
                  >确 定</el-button
                >
              </span>
            </el-dialog>
          </div>
        </div>
        <div style="height: 2rem"></div>
      </div>
      <div style="height: 2rem"></div>
    </div>
  </div>
</template>

<script>
import urlencode from '../../../updateCompoents/urlencode';
import '../../../updateCompoents/mavon-editor/dist/markdown/github-markdown.min.css';
import '../../../updateCompoents/mavon-editor/dist/css/index.css';
let copy_lock = false;

export default {
  name: 'onearticle',
  async activated() {
    this.comment = [];
    this.issendfinish = true;
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
    this.page = 1;
    this.isseetip = true;
    this.form.region = 'url';
    this.fronturl = await this.getFronturl();
    this.article_id = urlencode.decode(this.$route.query.path, 'gbk');
    this.islove = false;
    this.islock = false;
    await this.getmyid();
    await this.lookarticle();
    await this.lookcomment();
    this.$nextTick(() => {
      this.totop();
    });
    window.addEventListener('scroll', this.addlist);
    window.addEventListener('copy', this.copyText);
  },
  deactivated() {
    this.comment = [];
    this.islock = true;
    this.isseetip = false;
    window.removeEventListener('scroll', this.addlist);
    window.removeEventListener('copy', this.copyText);
  },
  destroyed() {
    this.comment = [];
    this.islock = true;
    this.isseetip = false;
    window.removeEventListener('scroll', this.addlist);
    window.removeEventListener('copy', this.copyText);
  },
  data() {
    return {
      is_public: 0,
      issendfinish: true,
      isfabulous: false,
      isseetip: true,
      fronturl: window?.location?.href,
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      dialogFormVisible1: false, // 用于控制表单对话框的开启和关闭
      dialogFormVisible2: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: '5rem', // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: '',
        region: '',
      },
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => 'md/highlightjs/highlight.min.js',
        hljs_css: (css) => 'md/highlightjs/styles/' + css + '.min.css',
        hljs_lang: (lang) => 'md/highlightjs/languages/' + lang + '.min.js',
        katex_css: () => 'md/katex/katex.min.css',
        katex_js: () => 'md/katex/katex.min.js',
      },
      tousercommenttext: '',
      maincomment_id: 0,
      touserid: 0,
      istousercomment: false,
      is_can_edit: false,
      name: '加载中',
      writer: '加载中',
      article: '加载中',
      article_id: 0,
      problemid: 0,
      image: '',
      fabulous: 0,
      collection: 0,
      lastchangetime: '加载中',
      releasetime: '加载中',
      writerid: 0,
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
      comment: [],
      commenttext: '',
      islove: false,
      myid: 0,
      loadfinish: false,
    };
  },
  methods: {
    async shareArticle() {
      try {
        const back_url = await this.getBackurl();
        let url = back_url + '/Article/oneArticle?path=' + this.article_id;
        this.copy(url);
      } catch (err) {}
    },
    addlist() {
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
      this.lookcomment();
    },
    btnExport() {
      const url = window.URL.createObjectURL(
        new Blob([this.article], {
          type: 'application/vnd.ms-excel,charset=utf-8',
        })
      );
      const link = document.createElement('a');

      const fileName = this.name + '.md';
      link.style.display = 'none';
      link.href = url;
      link.setAttribute('download', fileName);

      document.body.appendChild(link);
      link.click();
      URL.revokeObjectURL(link.href); // 释放URL对象
      document.body.removeChild(link);
      this.$msg({
        type: 'success',
        message: '开始下载！',
        duration: 800,
        offset: 80,
      });
    },

    toupdateonearticle() {
      //加载修改文章
      this.article_id &&
        this.$router.push({
          path: '/updateonearticle',
          query: {
            path: urlencode(this.article_id, 'gbk'),
          },
        });
    },
    topropage() {
      this.problemid &&
        this.$router.push({
          path: '/oneproblem',
          query: {
            path: urlencode(this.problemid, 'gbk'),
            contest: urlencode('', 'gbk'),
          },
        });
    },

    async copyText(e) {
      if (copy_lock) {
        return;
      }
      let clipboardData = e.clipboardData || window.clipboardData;
      // 如果 未复制或者未剪切，直接 return
      if (!clipboardData) {
        return;
      }
      const text = window.getSelection().toString();
      if (!text) {
        return;
      }
      copy_lock = true;
      e.preventDefault();
      const msgType = '内容';
      const userName = this.writer;
      let url = window.location.href;
      if (
        this.fronturl != undefined &&
        this.fronturl &&
        this.fronturl != null
      ) {
        let loc = url.indexOf('/onearticle');
        let oriurl = url;
        let len = oriurl.length;
        url = this.fronturl;
        for (let i = loc; i < len; ++i) {
          url += oriurl[i];
        }
      }

      const value = text;

      // const value =
      //   text +
      //   '\n\n————————————\n' +
      //   '版权声明：内容来自LTPP作者「' +
      //   userName +
      //   '」的文章或者文章的评论「' +
      //   this.name +
      //   '」，著作权归作者所有，商业转载请联系作者获得授权，非商业转载请注明出处。\n' +
      //   '原文链接：' +
      //   url;

      try {
        clipboardData.setData('text/plain', value.trim());
        this.$msg({
          type: 'success',
          message: '复制' + msgType + '成功',
          duration: 800,
          offset: 80,
        });
      } catch {
        this.$msg({
          type: 'error',
          message: '复制' + msgType + '失败',
          duration: 800,
          offset: 80,
        });
      }
      setTimeout(() => {
        copy_lock = false;
      }, 0);
    },
    videoLink1() {
      // 准备链接模板
      let linkFrame = '';
      if (this.form.region == '') {
        this.form.region = 'url';
      }
      // 创建一个div盒子，为提取src做准备
      let box = document.createElement('div');
      // 将原始链接插入到盒子中

      box.innerHTML = this.form.link;
      // 判断不同的视频原链接类型
      if (this.form.region == 'url') {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><video style="height:46vh; width: 100%" controls controlslist="nodownload"><source src="`;
        let linkFrameEnd = `" type="video/mp4" /></video></div>`;

        linkFrame = linkFrameStart + this.form.link + linkFrameEnd;
      } else if (
        this.form.region == 'iframe' &&
        box.getElementsByTagName('iframe').length > 0
      ) {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><iframe height="${
          (window.innerHeight - 240) / 2 + 'px'
        }" width="80%" src="`;
        let linkFrameEnd = `" allowfullscreen="true" scrolling="no" border="0" frameborder="no" framespacing="0" style="border-width: 0rem; min-height: 31.2rem;"></iframe></div>`;

        // 从iframe标签中提取src属性
        linkFrame =
          linkFrameStart +
          box.getElementsByTagName('iframe')[0].getAttribute('src') +
          linkFrameEnd;
      } else {
        // 原始链接格式错误时弹出错误提示
        this.dialogFormVisible1 = false;
        this.dialogVisible = true;
      }
      // 复原表单文本框内容
      this.form.link = '';

      // 获取文本域中当前光标起始位置、结束位置以及滚动条位置（滚动条位置我认为没有必要，如有需要可以自己取消注释）
      let textarea = document.getElementsByClassName('auto-textarea-input')[0];
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
      this.$refs.md1.d_value = subStart + '\n' + linkFrame + '\n' + subEnd;
      // 关闭对话框
      this.dialogFormVisible1 = false;
    },
    videoLink2() {
      // 准备链接模板
      let linkFrame = '';
      if (this.form.region == '') {
        this.form.region = 'url';
      }
      // 创建一个div盒子，为提取src做准备
      let box = document.createElement('div');
      // 将原始链接插入到盒子中

      box.innerHTML = this.form.link;
      // 判断不同的视频原链接类型
      if (this.form.region == 'url') {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><video style="height:46vh; width: 100%" controls controlslist="nodownload"><source src="`;
        let linkFrameEnd = `" type="video/mp4" /></video></div>`;

        linkFrame = linkFrameStart + this.form.link + linkFrameEnd;
      } else if (
        this.form.region == 'iframe' &&
        box.getElementsByTagName('iframe').length > 0
      ) {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><iframe height="${
          (window.innerHeight - 240) / 2 + 'px'
        }" width="80%" src="`;
        let linkFrameEnd = `" allowfullscreen="true" scrolling="no" border="0" frameborder="no" framespacing="0" style="border-width: 0rem; min-height: 31.2rem;"></iframe></div>`;

        // 从iframe标签中提取src属性
        linkFrame =
          linkFrameStart +
          box.getElementsByTagName('iframe')[0].getAttribute('src') +
          linkFrameEnd;
      } else {
        // 原始链接格式错误时弹出错误提示
        this.dialogFormVisible2 = false;
        this.dialogVisible = true;
      }
      // 复原表单文本框内容
      this.form.link = '';

      // 获取文本域中当前光标起始位置、结束位置以及滚动条位置（滚动条位置我认为没有必要，如有需要可以自己取消注释）
      let textarea = document.getElementsByClassName('auto-textarea-input')[0];
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
      this.$refs.md4.d_value = subStart + '\n' + linkFrame + '\n' + subEnd;
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
          path: '/userpage',
          query: {
            path: urlencode(id, 'gbk'),
          },
        });
    },
    tohome() {
      this.$router.go(-1);
    },

    async $imgAdd1(pos, $file) {
      this.imgAddMiddleware(pos, $file, 'md1');
    },
    async $imgAdd4(pos, $file) {
      this.imgAddMiddleware(pos, $file, 'md4');
    },
    async lookarticle() {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Article/loadOneArticle',
        portType: {
          process: '8792',
        },
        data: {
          article_id: this.article_id,
        },
      }).catch((t) => {
        this.loadfinish = true;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      this.loadfinish = true;
      if (res.code == 1) {
        this.name = res.data['name'];
        this.article = res.data['article'];
        this.fabulous = res.data['fabulous'];
        this.collection = res.data['collection'];
        this.image = res.data['image'];
        this.writer = res.data['writer'];
        this.lastchangetime = res.data['lastchangetime'];
        this.releasetime = res.data['releasetime'];
        this.writerid = res.data['writerid'];
        this.problemid = res.data['problemid'];
        this.is_can_edit = res.edit;
        this.islove = res.love;
        this.is_public = res.data['public'];
        this.isfabulous = res.fabulous;
      } else {
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      }
    },
    lookcomment() {
      if (this.islock) {
        return;
      }
      this.islock = true;
      this.$ajax({
        method: 'post',
        url: '/ArticleComment/loadComment',
        portType: {
          process: '8798',
        },
        data: {
          article_id: this.article_id,
          comment_id:
            this.comment?.length > 0
              ? this.comment[this.comment?.length - 1].id
              : 0,
        },
      })
        .then((res) => {
          if (
            res.data &&
            res.data.data &&
            res.data.data.length &&
            res.data.data.length > 0
          ) {
            res.data.data.forEach((tem) => {
              this.comment.push(tem);
            });
            this.islock = false;
          } else {
            this.$msg({
              type: 'success',
              message: '没有更多评论了！',
              duration: 800,
              offset: 80,
            });
          }
        })
        .catch((t) => {
          this.islock = false;
          this.$msg({
            type: 'error',
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
    },

    async deletecomment(delcomment_id) {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/ArticleComment/deletecomment',
        portType: {
          process: '8798',
        },
        data: {
          comment_id: delcomment_id,
          article_id: this.article_id,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        let len = this.comment.length;
        for (let i = 0; i < len; ++i) {
          let tlen = this.comment[i].touserarray.length;
          if (this.comment[i].id == delcomment_id) {
            this.comment.splice(i, 1);
            break;
          }
          if (tlen > 0) {
            for (let j = 0; j < tlen; ++j) {
              if (this.comment[i].touserarray[j].id == delcomment_id) {
                this.comment[i].touserarray.splice(j, 1);
                break;
              }
            }
          }
        }
        this.$msg({
          type: 'success',
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 800,
          offset: 80,
        });
      }
    },

    async addcomment() {
      if (!this.issendfinish) {
        return;
      }
      let t1 = this.commenttext;
      let value1 = t1.replace(/\s+/g, '');
      if (value1 == '') {
        this.$msg({
          type: 'error',
          message: '评论不能为空',
          duration: 800,
          offset: 80,
        });
        return;
      }
      this.issendfinish = false;
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/ArticleComment/addComment',
        portType: {
          process: '8798',
        },
        data: {
          article_id: this.article_id,
          text: this.commenttext,
        },
      }).catch((t) => {
        this.issendfinish = true;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.issendfinish = true;
      this.touserid = 0;
      if (res.code == 1) {
        this.commenttext = '';
      } else {
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 1800,
          offset: 80,
        });
      }
      this.comment = [];
      this.islock = false;
      this.lookcomment();
    },
    async addtousercomment() {
      if (!this.issendfinish) {
        return;
      }
      let t1 = this.tousercommenttext;
      let value1 = t1.replace(/\s+/g, '');
      if (value1 == '') {
        this.$msg({
          type: 'error',
          message: '评论不能为空',
          duration: 800,
          offset: 80,
        });
        return;
      }
      this.issendfinish = false;
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/ArticleComment/addToUserComment',
        portType: {
          process: '8798',
        },
        data: {
          article_id: this.article_id,
          text: this.tousercommenttext,
          touser_id: this.touserid,
          maincomment_id: this.maincomment_id,
        },
      }).catch((t) => {
        this.issendfinish = true;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.issendfinish = true;
      if (res.code == 1) {
        this.tousercommenttext = '';
        this.$msg({
          type: 'success',
          message: res.msg,
          duration: 600,
          offset: 80,
        });
        this.comment = [];
        this.islock = false;
        await this.lookcomment();
      } else {
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 800,
          offset: 80,
        });
      }
    },

    async fabulousClick() {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Article/fabulousOneArticle',
        portType: {
          process: '8792',
        },
        data: {
          article_id: this.article_id,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        ++this.fabulous;
        this.isfabulous = true;
        this.$msg({
          type: 'success',
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 800,
          offset: 80,
        });
      }
    },

    async collectionClick() {
      ++this.collection;
      this.islove = true;
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Article/collectionOneArticle',
        portType: {
          process: '8792',
        },
        data: {
          article_id: this.article_id,
        },
      }).catch((t) => {
        this.islove = false;
        --this.collection;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: 'success',
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.islove = false;
        --this.collection;
        this.$msg({
          type: 'error',
          message: res.msg,
          duration: 800,
          offset: 80,
        });
      }
    },

    async delcollectionClick() {
      --this.collection;
      this.islove = false;
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Article/deleteLoveArticle',
        portType: {
          process: '8792',
        },
        data: {
          article_id: this.article_id,
        },
      }).catch((t) => {
        this.islove = true;
        ++this.collection;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: 'success',
          message: res.msg,
          duration: 600,
          offset: 80,
        });
      } else {
        this.islove = true;
        ++this.collection;
        this.$msg({
          type: 'error',
          message: res.msg,
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
        defaultOpen: 'preview', //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: false,
        toolbarsFlag: false, //工具栏
        scrollStyle: true,
        codeStyle: 'atom-one-dark',
        boxShadow: false,
        ishljs: true,
        tabSize: 4,
        toolbarsBackground: 'rgba(0,0,0,0)',
        editorBackground: 'rgba(0,0,0,0)',
        previewBackground: 'rgba(0,0,0,0)',
        fontSize: '1.06rem',
        navigation: false,
      };
      return data;
    },
  },
};
</script>
<style lang="less" scoped>
@import '../../../public/md/markdown/github-markdown.min.css';
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
