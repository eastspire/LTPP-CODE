/* 主页 */
<template>
  <div @contextmenu.prevent="" id="bk" class="no-select">
    <div
      v-if="$store.state.login"
      v-show="scroll_percent"
      style="
        position: fixed;
        top: -0.66rem;
        width: 100vw;
        z-index: 1000010 !important;
      "
    >
      <el-progress
        :text-inside="true"
        :percentage="scroll_percent"
        color="#409EFF"
      ></el-progress>
    </div>
    <div v-if="$store.state.login && lookmusic == 1">
      <music v-domDrag class="musicdiv"></music>
    </div>
    <div
      class="home-default-bk"
      :style="`background-image:url(${
        $store.state.login ? $store.state.bkimage : ''
      })`"
    ></div>
    <video
      id="video"
      @error="videoLoadError"
      v-if="
        $store.state.login &&
        $store.state.bkvideo &&
        reg.test($store.state.bkvideo) &&
        !video_load_error
      "
      muted
      :src="$store.state.bkvideo"
      class="video-bk"
    ></video>

    <el-container>
      <div class="HomeMain">
        <div class="no-select shadow">
          <el-menu
            :default-active="$route.path"
            :popper-append-to-body="true"
            :collapse="true"
            active-text-color="rgb(0, 123, 255)"
            background-color="rgb(41, 50, 56)"
            text-color="rgb(248, 249, 250)"
            class="el-menu-demo no-select left_menu shadow"
            router
          >
            <div style="height: 6px"></div>
            <el-avatar
              :src="$store.state.headimage"
              style="height: 38px; width: 38px; margin-left: 6px; padding: 0"
            >
            </el-avatar>
            <div style="height: 6px"></div>
            <el-menu-item index="/homelist">
              <i class="el-icon-s-home"></i>
              <span slot="title" class="my-span">首页</span>
            </el-menu-item>
            <el-submenu index="1" class="no-select">
              <template slot="title">
                <i class="el-icon-s-order"></i>
              </template>
              <el-menu-item index="/allarticle">
                <template slot="title">
                  <i class="el-icon-s-management" style="font-size: 1.06rem"
                    >文章广场</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/goods">
                <template slot="title">
                  <i class="el-icon-s-ticket" style="font-size: 1.06rem"
                    >商店</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/questionlist">
                <template slot="title">
                  <i class="el-icon-s-opportunity" style="font-size: 1.06rem"
                    >问答圈</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/sendquestion">
                <template slot="title">
                  <i class="el-icon-upload" style="font-size: 1.06rem"
                    >问答圈提问</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/randomarticle">
                <template slot="title">
                  <i class="el-icon-s-order" style="font-size: 1.06rem"
                    >随机文章</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/write">
                <template slot="title">
                  <i class="el-icon-upload" style="font-size: 1.06rem"
                    >发布文章</i
                  >
                </template>
              </el-menu-item>
            </el-submenu>
            <el-submenu index="2" class="no-select">
              <template slot="title">
                <i class="el-icon-s-platform"></i>
              </template>
              <el-menu-item index="/dayproblem">
                <template slot="title">
                  <i class="el-icon-s-marketing" style="font-size: 1.06rem"
                    >每日一题</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/oj">
                <template slot="title">
                  <i class="el-icon-s-data" style="font-size: 1.06rem"
                    >题库训练</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/contest">
                <template slot="title">
                  <i class="el-icon-s-flag" style="font-size: 1.06rem"
                    >竞赛系统</i
                  >
                </template>
              </el-menu-item>
            </el-submenu>
            <el-submenu index="3" class="no-select">
              <template slot="title">
                <i class="el-icon-s-custom"></i>
              </template>
              <el-menu-item index="/user">
                <template slot="title">
                  <i class="el-icon-s-custom" style="font-size: 1.06rem"
                    >全站用户</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/rank">
                <template slot="title">
                  <i class="el-icon-s-marketing" style="font-size: 1.06rem"
                    >全站排名</i
                  >
                </template>
              </el-menu-item>
            </el-submenu>
            <el-submenu index="4" class="no-select">
              <template slot="title">
                <i class="el-icon-s-claim"></i>
              </template>
              <el-menu-item index="/mycodehistory">
                <template slot="title">
                  <i class="el-icon-s-claim" style="font-size: 1.06rem"
                    >我的记录</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/allcodehistory">
                <template slot="title">
                  <i class="el-icon-s-claim" style="font-size: 1.06rem"
                    >全站记录</i
                  >
                </template>
              </el-menu-item>
            </el-submenu>
            <el-menu-item index="/ide">
              <i class="el-icon-s-platform"></i>
              <span slot="title" class="my-span">编辑器</span>
            </el-menu-item>
            <el-menu-item index="/videocommunity">
              <i class="el-icon-video-camera-solid"></i>
              <span slot="title" class="my-span">短视频</span>
            </el-menu-item>
            <el-menu-item index="/classteach">
              <i class="el-icon-s-opportunity"></i>
              <span slot="title" class="my-span">在线课堂</span>
            </el-menu-item>

            <el-menu-item index="/cloudfile">
              <i class="el-icon-upload"></i>
              <span slot="title" class="my-span">云盘</span>
            </el-menu-item>
            <el-menu-item index="/chat">
              <i class="el-icon-s-comment"></i>
              <span slot="title" class="my-span">在线聊天</span>
            </el-menu-item>
            <el-menu-item @click="goFront()">
              <i class="el-icon-caret-left"></i>
              <span slot="title" class="my-span"> 前进 </span>
            </el-menu-item>
          </el-menu>
        </div>
        <div
          :style="`padding-left:${$store.state.default_home_to_left_right}px;padding-right: ${$store.state.default_home_to_left_right}px;
          `"
        >
          <div
            :style="`height: ${$store.state.default_margin_top_bottom}rem`"
          ></div>
          <div v-if="$store.state.login">
            <transition name="fadeIn">
              <keep-alive :max="Infinity">
                <router-view
                  :key="$route.fullPath"
                  :style="`min-height:88vh;width:${$store.state.max_width}px;margin-left: auto;margin-right: auto;`"
                >
                </router-view>
              </keep-alive>
            </transition>
          </div>
          <div
            :style="`height: ${$store.state.default_margin_top_bottom}rem`"
          ></div>
        </div>
        <div class="no-select">
          <el-menu
            :default-active="$route.path"
            :popper-append-to-body="true"
            :collapse="true"
            active-text-color="rgb(0, 123, 255)"
            background-color="rgb(41, 50, 56)"
            text-color="rgb(248, 249, 250)"
            class="el-menu-demo no-select right_menu shadow"
            router
          >
            <div style="height: 6px"></div>
            <el-avatar
              :src="$store.state.headimage"
              style="height: 38px; width: 38px; margin-left: 6px; padding: 0"
            >
            </el-avatar>
            <div style="height: 6px"></div>
            <el-submenu index="8" class="no-select">
              <template slot="title">
                <i class="el-icon-s-custom"></i>
              </template>
              <el-menu-item index="/follow">
                <template slot="title">
                  <i class="el-icon-s-custom" style="font-size: 1.06rem"
                    >关注管理</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="/fans"
                ><template slot="title">
                  <i class="el-icon-s-custom" style="font-size: 1.06rem"
                    >粉丝管理</i
                  >
                </template></el-menu-item
              >
              <el-menu-item index="/myquestion"
                ><template slot="title">
                  <i class="el-icon-s-ticket" style="font-size: 1.06rem"
                    >问答管理</i
                  >
                </template></el-menu-item
              >
              <el-menu-item index="/mygoods"
                ><template slot="title">
                  <i class="el-icon-s-ticket" style="font-size: 1.06rem"
                    >我的商品</i
                  >
                </template></el-menu-item
              >
            </el-submenu>
            <el-menu-item index="/mynotice">
              <i class="el-icon-message-solid"></i>
              <span slot="title" class="my-span">消息通知</span></el-menu-item
            >

            <el-menu-item index="/myjoincontest">
              <i class="el-icon-s-flag"></i>
              <span slot="title" class="my-span">我的竞赛</span>
            </el-menu-item>

            <el-menu-item index="/fabulousvideo">
              <i class="el-icon-video-camera-solid"></i>
              <span slot="title" class="my-span">点赞视频</span>
            </el-menu-item>
            <el-menu-item index="/lovevideo">
              <i class="el-icon-star-on"></i>
              <span slot="title" class="my-span">收藏视频</span>
            </el-menu-item>
            <el-menu-item
              index=""
              @click="
                lookmusic == 1 ? (lookmusic = 0) : (lookmusic = 1);
                changemusic();
              "
            >
              <i class="el-icon-s-help"></i>
              <span slot="title" class="my-span">打开/关闭音乐</span>
            </el-menu-item>
            <el-menu-item index="/myarticlemanage">
              <i class="el-icon-s-order"></i>
              <span slot="title" class="my-span">我的文章</span>
            </el-menu-item>

            <el-menu-item index="/lovearticle">
              <i class="el-icon-star-on"></i>
              <span slot="title" class="my-span">收藏文章</span>
            </el-menu-item>

            <el-menu-item index="/mydatamanage">
              <i class="el-icon-user-solid"></i>
              <span slot="title" class="my-span">个人信息</span>
            </el-menu-item>

            <el-submenu index="11" class="no-select">
              <template slot="title">
                <i class="el-icon-s-tools"></i>
              </template>

              <el-menu-item
                index=""
                @click="addnotice()"
                v-if="$store.state.root"
              >
                <template slot="title">
                  <i class="el-icon-message-solid" style="font-size: 1.06rem"
                    >发布通知</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item
                index="/setting"
                v-if="$store.state.root && $store.state.my_name === 'root'"
              >
                <template slot="title">
                  <i class="el-icon-s-tools" style="font-size: 1.06rem"
                    >站点设置</i
                  >
                </template>
              </el-menu-item>
              <el-submenu
                index="5"
                v-if="$store.state.root && $store.state.my_name === 'root'"
              >
                <template slot="title">
                  <i class="el-icon-menu" style="font-size: 1.06rem"
                    >首页管理</i
                  >
                </template>
                <el-menu-item
                  index="/noticemanage"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                  ><template slot="title">
                    <i class="el-icon-s-order" style="font-size: 1.06rem"
                      >公告管理</i
                    >
                  </template>
                </el-menu-item>
                <el-menu-item
                  index="/shortsentencemanage"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                  ><template slot="title">
                    <i class="el-icon-s-order" style="font-size: 1.06rem"
                      >短句管理</i
                    >
                  </template>
                </el-menu-item>
                <el-menu-item
                  index="/photomanage"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                  ><template slot="title">
                    <i class="el-icon-picture" style="font-size: 1.06rem"
                      >首页图片管理</i
                    >
                  </template>
                </el-menu-item>
                <el-menu-item
                  index="/videomanage"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                  ><template slot="title">
                    <i
                      class="el-icon-video-camera-solid"
                      style="font-size: 1.06rem"
                      >视频管理</i
                    >
                  </template>
                </el-menu-item>
              </el-submenu>
              <el-submenu
                index="6"
                class="no-select"
                v-if="$store.state.root || $store.state.admin"
              >
                <template slot="title">
                  <i class="el-icon-menu" style="font-size: 1.06rem"
                    >后台管理</i
                  >
                </template>
                <el-menu-item
                  index="/usermanage"
                  v-if="$store.state.root || $store.state.admin"
                >
                  <template slot="title">
                    <i class="el-icon-s-custom" style="font-size: 1.06rem"
                      >用户管理</i
                    >
                  </template>
                </el-menu-item>

                <el-menu-item
                  index="/managecontest"
                  v-if="$store.state.root || $store.state.admin"
                >
                  <template slot="title">
                    <i class="el-icon-s-flag" style="font-size: 1.06rem"
                      >竞赛管理</i
                    >
                  </template>
                </el-menu-item>
                <el-menu-item
                  index="/problemmanage"
                  v-if="$store.state.root || $store.state.admin"
                >
                  <template slot="title">
                    <i class="el-icon-s-data" style="font-size: 1.06rem"
                      >题库管理</i
                    >
                  </template>
                </el-menu-item>

                <el-menu-item
                  index="/userarticle"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                >
                  <template slot="title">
                    <i class="el-icon-s-order" style="font-size: 1.06rem"
                      >用户文章管理</i
                    >
                  </template>
                </el-menu-item>
                <el-menu-item
                  index="/goodsmanage"
                  v-if="$store.state.root && $store.state.my_name === 'root'"
                >
                  <template slot="title">
                    <i class="el-icon-s-ticket" style="font-size: 1.06rem"
                      >商品管理</i
                    >
                  </template>
                </el-menu-item>
              </el-submenu>
              <el-menu-item index="" @click="clearmusiclogin()">
                <template slot="title">
                  <i class="el-icon-s-release" style="font-size: 1.06rem"
                    >注销音乐</i
                  >
                </template>
              </el-menu-item>
              <el-menu-item index="" @click="logout()">
                <template slot="title">
                  <i class="el-icon-s-release" style="font-size: 1.06rem"
                    >退出登录</i
                  >
                </template>
              </el-menu-item>
            </el-submenu>
            <el-menu-item @click="goBack()">
              <i class="el-icon-caret-right"></i>
              <span slot="title" class="my-span"> 返回 </span>
            </el-menu-item>
          </el-menu>
        </div>
      </div>
      <el-backtop
        :bottom="10"
        style="position: fixed; bottom: 5.5rem; right: 5.5rem"
        class="pulse-enter-active"
      >
        <div
          style="
            border-radius: 1rem;
            height: 100%;
            width: 100%;
            background-color: #cecfd1;
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.12);
            text-align: center;
            line-height: 40px;
            color: #008bb6;
          "
        >
          UP
        </div>
      </el-backtop>
      <div v-if="$store.state.root">
        <el-dialog
          :append-to-body="true"
          :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
          title="发布通知"
          :visible.sync="isseenotice"
        >
          <div class="search">
            <el-input
              type="textarea"
              style="font-size: 1rem; overflow: hidden"
              :autosize="{ minRows: 16, maxRows: 10000000 }"
              placeholder="请输入内容"
              v-model.lazy="notice"
            ></el-input>
          </div>
          <br />
          <div style="text-align: right" v-if="ws_connect_finish">
            <el-button
              type="text"
              class="el-icon-edit-outline"
              style="font-size: 1.06rem; margin-right: 1rem"
              @click="
                postNoticemessage(notice);
                notice = '';
                isseenotice = false;
              "
            >
              发布通知
            </el-button>
          </div>
        </el-dialog>
      </div>
    </el-container>
  </div>
</template>

<script>
import { resolve } from "../../updateCompoents/monaco-editor/esm/vs/base/common/path";
import music from "./home/music.vue";

export default {
  name: "Home",
  components: { music },
  beforeCreate() {
    this.$store.commit("updateObj", { login: false });
    let authorization = window.localStorage.getItem("authorization");
    let key = window.localStorage.getItem("key");
    if (!authorization || !key) {
      this.logoutRemove();
      return;
    }
  },
  async created() {
    addEventListener("scroll", () => {
      if (document.body.scrollHeight <= window.innerHeight) {
        this.scroll_percent = 0;
        return;
      }
      let body = document.body;
      let html = document.documentElement;
      let scrollTop = body.scrollTop || html.scrollTop;
      let scrollHeight = body.scrollHeight || html.scrollHeight;
      let clientHeight = html.clientHeight || window.innerHeight;
      if (scrollHeight - clientHeight) {
        const tp = Math.min(
          100,
          (scrollTop / (scrollHeight - clientHeight)) * 100
        );
        this.scroll_percent = tp ?? 0;
      } else {
        this.scroll_percent = 100;
      }
    });

    this.judgelogin();
    await this.getGrade();
    await this.loadSelfData();
    setTimeout(() => {
      this.$nextTick(async () => {
        await this.videoPlay();
        this.video_dom &&
          this.video_dom.addEventListener("ended", this.videoEnd);
      });
    }, 0);
    this.$store.commit("updateObj", { my_id: this.getMyId() });
    this.loadmynoticenum();
    this.isseenotice = false;
    this.getisusemusic();
    /* 每1分钟发送一次心跳 并 获取一下未读消息数目*/
    this.timer = setInterval(() => {
      this.loadmynoticenum();
      this.sendheart();
    }, 60000);
    await this.getBackUrl();
  },
  activated() {
    this.onRouteChanged();
  },
  deactivated() {
    try {
      clearInterval(this.timer);
    } catch (e) {
      this.timer = null;
      return;
    }
  },
  destroyed() {
    try {
      this.websocket.close();
      clearInterval(this.timer);
      clearInterval(this.socket_timer);
      this.video_dom &&
        this.video_dom.removeEventListener("ended", this.videoEnd);
    } catch (e) {
      this.timer = null;
      this.socket_timer = null;
      return;
    }
    this.timer = null;
    this.socket_timer = null;
  },
  data() {
    return {
      socketurl: "",
      msgtypeObj: {
        heart: "heart",
        private_chat: "private_chat",
        group_chat: "group_chat",
        create_group: "create_group",
        join_group: "join_group",
        delete_group: "delete_group",
        exit_group: "exit_group",
        connect_group: "connect_group",
      },
      is_connect_success: false,
      icon_left: "<",
      icon_right: ">",
      scroll_percent: 0,
      last_notice_num: -1,
      video_load_error: false,
      timer: null,
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      socket_timer: null,
      activeIndex: "1",
      lookmusic: 0,
      classurl: "",
      websocket: null,
      isseenotice: false,
      notice: "",
      video_dom: null,
      ws_connect_finish: false,
    };
  },
  async mounted() {
    this.initDevice();
    this.$EventBus.$on("closeWs", () => this.closeWs());
    this.$EventBus.$on("chatSendMsg", (e) => {
      this.postmessage(e);
    });
    await this.getsocketurl();
    await this.getclassurl();
    await this.setup();
    // 点击关闭浏览器时触发关闭事件
    window.addEventListener("beforeunload", (e) => {
      try {
        this.websocket && this.websocket.close && this.websocket.close();
      } catch (err) {}
    });
  },

  methods: {
    videoLoadError() {
      this.video_load_error = true;
    },
    goFront() {
      this.$router.go(1);
    },
    goBack() {
      this.$router.go(-1);
    },
    async videoPlay() {
      let deep = 0;
      this.video_dom = document.getElementById("video");
      while (deep < this.$SqsGlobal.max_video_retry_times) {
        try {
          if (this.video_load_error || !this.video_dom) {
            return;
          }
          this.video_dom && (await this.video_dom.play());
          break;
        } catch (err) {
          ++deep;
        }
      }
    },
    videoEnd() {
      if (this.video_load_error || !this.video_dom) {
        return;
      }
      this.videoPlay();
    },
    addnotice() {
      this.isseenotice = true;
    },
    closeWsAsync() {
      return new Promise((resolve, reject) => {
        try {
          if (
            this.websocket &&
            this.websocket.readyState &&
            this.websocket.readyState === WebSocket.OPEN
          ) {
            this.websocket.onclose = (event) => {
              this.websocket.close(1000, "断开通知服务器连接");
              this.$destroy();
              resolve();
            };
            this.websocket.close(1000, "断开通知服务器连接");
            this.ws_connect_finish = false;
          }
        } catch (error) {
          reject(error);
        }
      });
    },
    async closeWs() {
      this.ws_connect_finish = false;
      await this.closeWsAsync().catch((error) => {
        this.$notice({
          title: "通知(通知服务器连接断开异常)",
          dangerouslyUseHTMLString: true,
          message: error.message,
          duration: 3600,
          offset: 80,
        });
        return;
      });
      this.$notice({
        title: "通知(通知服务器连接已断开)",
        dangerouslyUseHTMLString: true,
        message: "断开成功",
        duration: 3600,
        offset: 80,
      });
    },
    postNoticemessage(mymessage) {
      if (!this.ws_connect_finish) {
        this.$msg({
          type: "error",
          message: "请等待通知服务器连接完成",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      let t1 = mymessage;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "内容不能为空",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      let msg = {
        msgtype: "notice",
        name: this.name,
        msg: mymessage,
      };
      this.postmessage(msg);
      this.mymessage = "";
      this.$msg({
        type: "success",
        message: "通知下达成功",
        duration: 1600,
        offset: 80,
      });
    },

    postmessage(msg) {
      this.websocket.send(JSON.stringify(msg));
    },

    async setup() {
      await this.wsInitConnect();
    },

    /**
     * ws初始化连接
     */
    async wsInitConnect() {
      try {
        let authorization = window.localStorage.getItem("authorization");
        let key = window.localStorage.getItem("key");
        if (!authorization || !key) {
          this.logoutRemove();
          return;
        }
        this.$notice({
          title: "聊天服务器",
          dangerouslyUseHTMLString: true,
          message: "正在连接！",
          duration: 1000,
          offset: 80,
        });
        this.websocket = new WebSocket(
          this.socketurl +
            "?" +
            authorization +
            this.$SqsGlobal.websocket_connect_str +
            key
        );
        return new Promise((resolve) => {
          this.websocket.onopen = () => {
            this.websocket.onmessage = (e) => this.wsOnmessage(e);
            this.websocket.onclose = () => this.wsOnclose();
            this.wsOnopen(resolve);
          };
          this.websocket.onerror = () => this.wsOnerror(resolve);
        });
      } catch (err) {}
      return;
    },

    wsOnmessage(e) {
      try {
        const temdata = eval("(" + e.data + ")");
        if (temdata.msgtype == this.msgtypeObj.heart) {
          return;
        }
        if (temdata.msgtype && temdata.msgtype == "notice") {
          this.$notice({
            title: "通知(" + temdata.time + ")",
            dangerouslyUseHTMLString: true,
            message: temdata.msg,
            duration: 0,
            offset: 80,
          });
        } else if (
          temdata.msgtype &&
          temdata.msgtype == "connect_all_group_success"
        ) {
          if (this.is_connect_success) {
            return;
          }
          this.is_connect_success = true;
          this.$notice({
            title: "操作成功",
            dangerouslyUseHTMLString: true,
            message: temdata.msg,
            duration: 1600,
            offset: 80,
          });
        } else {
          this.$EventBus.$emit("chatGetMsg", e);
          if (this.$route.path != "/chat") {
            this.$notice({
              title: "新消息提醒",
              dangerouslyUseHTMLString: true,
              message: "您有一条新聊天消息",
              duration: 1600,
              offset: 80,
            });
          }
        }
      } catch (err) {
        resolve();
      }
    },

    async wsOnclose() {
      try {
        this.is_connect_success = false;
        this.ws_connect_finish = false;
        let clock_timer = setInterval(() => {
          if (
            this.websocket &&
            this.websocket.readyState &&
            this.websocket.readyState === WebSocket.OPEN
          ) {
            clearInterval(clock_timer);
            clock_timer = null;
          } else if (this.websocket.readyState != WebSocket.CONNECTING) {
            this.wsInitConnect();
          }
        }, 3600);
      } catch (err) {
        resolve();
      }
    },

    wsOnopen(resolve) {
      try {
        this.ws_connect_finish = true;
        this.$notice({
          title: "聊天服务器",
          dangerouslyUseHTMLString: true,
          message: "连接成功！",
          duration: 3600,
          offset: 80,
        });
        // 心跳包
        this.socket_timer = setInterval(() => {
          let msg = {
            msgtype: "heart",
          };
          this.postmessage(msg);
        }, 10000);
        // 连接所有群聊
        let connect = setInterval(() => {
          if (this.is_connect_success) {
            clearInterval(connect);
            connect = null;
            resolve();
            return;
          }
          let msg = {
            msgtype: this.msgtypeObj.connect_group,
            group_data: {},
          };
          this.postmessage(msg);
        }, 666);
        resolve();
      } catch (err) {
        resolve();
      }
    },

    wsOnerror(resolve) {
      this.is_connect_success = false;
      try {
        this.$msg({
          type: "error",
          message: "聊天服务器连接发生错误！",
          duration: 1000,
          offset: 80,
        });
        if (this.websocket.readyState === WebSocket.CLOSED) {
          // 如果连接已经关闭,不再重复关闭
          resolve();
          return;
        }
        this.websocket.close();
        resolve();
      } catch (err) {
        resolve();
      }
    },

    async loadmynoticenum() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Mynotice/loadMyNoticeNum",
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
      });
      if (res.allnum > 0) {
        if (this.last_notice_num == -1 || res.allnum > this.last_notice_num) {
          this.$notice({
            title: "通知(" + res.time + ")",
            dangerouslyUseHTMLString: true,
            message: res.msg,
            duration: 10000,
            offset: 80,
          });
          this.last_notice_num = res.allnum;
        }
      }
    },
    async loadSelfData() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/loadSelfData",
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
      });
      if (res.code == 1) {
        this.$store.commit("updateObj", { my_name: res.data["name"] });
        this.$store.commit("updateObj", { headimage: res.data["headimage"] });
        this.$store.commit("updateObj", { bkimage: res.data["bkimage"] });
        this.$store.commit("updateObj", { bkvideo: res.data["bkvideo"] });
      }
    },
    async getisusemusic() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/getIsUseMusic",
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
      });
      if (res.code == 1) {
        this.lookmusic = res.data;
      } else {
        this.lookmusic = 0;
      }
    },
    async changemusic() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/changeMusic",
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
      });
      if (res.code == 1) {
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
    async getclassurl() {
      this.classurl = window.sessionStorage.getItem("classurl");
      if (this.classurl) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "get",
        url: "/Url/getClassUrl",
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
      if (res.code == 1) {
        this.classurl = res.data;
        window.sessionStorage.setItem("classurl", res.data);
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async getsocketurl() {
      this.socketurl = window.sessionStorage.getItem("socketurl");
      if (this.socketurl) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Url/getSocketUrl",
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
      if (res.code == 1) {
        this.socketurl = res.data;
        window.sessionStorage.setItem("socketurl", res.data);
      }
    },
    onRouteChanged() {
      let that = this;
      that.activeIndex = that.$route.path;
    },
    sendheart() {
      this.$ajax({
        method: "post",
        url: "/User/sendHeart",
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
      });
    },
    async getGrade() {
      this.$store.commit("updateObj", { login: false });
      this.$store.commit("updateObj", { admin: false });
      this.$store.commit("updateObj", { root: false });
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/judgeGrade",
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
        return;
      });
      this.$store.commit("updateObj", { login: true });
      if (res.code == 2) {
        this.$store.commit("updateObj", { admin: true });
      } else if (res.code == 3) {
        this.$store.commit("updateObj", { admin: true });
        this.$store.commit("updateObj", { root: true });
      }
    },
    judgelogin() {
      let authorization = window.localStorage.getItem("authorization");
      let key = window.localStorage.getItem("key");
      if (!authorization || !key) {
        this.logoutRemove();
        return;
      }
      this.$store.commit("updateObj", { login: true });
    },
    logout() {
      clearInterval(this.timer);
      this.timer = null;
      clearInterval(this.socket_timer);
      this.socket_timer = null;
      this.logoutRemove(true);
      this.$msg({
        type: "success",
        message: "注销成功",
        duration: 1600,
        offset: 80,
      });
    },
    clearmusiclogin() {
      this.lookmusic = 0;
      window.localStorage.removeItem("cookie");
      this.$nextTick(() => {
        this.lookmusic = 1;
      });
    },
  },
};
</script>
<style lang="less" scoped>
.left_menu {
  position: fixed;
  top: 0%;
  left: 0%;
  font-weight: bold;
  border-width: 0rem;
}

.right_menu {
  position: fixed;
  top: 0%;
  right: 0%;
  font-weight: bold;
  border-width: 0rem;
}

.el-menu--collapse {
  height: 100% !important;
}

::v-deep .el-pagination.is-background .el-pager li {
  color: rgb(255, 255, 255) !important;
  background-color: rgba(30, 30, 30, 1) !important;
}

::v-deep .el-icon-arrow-left:before,
::v-deep .el-icon-arrow-right:before {
  color: rgb(255, 255, 255) !important;
  background-color: rgba(30, 30, 30, 1) !important;
}

::v-deep .el-pagination.is-background .el-pager li:not(.disabled).active {
  color: rgba(30, 30, 30, 1) !important;
  background-color: rgb(255, 255, 255) !important;
}

::v-deep .el-switch__label {
  color: aliceblue;
  font-weight: bold;
}

::v-deep .is-active {
  color: deepskyblue !important;
  font-weight: bold;
}

/deep/.el-carousel__arrow,
/deep/.el-carousel__arrow--right,
/deep/.btn-prev,
/deep/ .btn-next {
  color: rgb(255, 255, 255) !important;
  background-color: rgba(30, 30, 30, 1) !important;
}

/deep/.el-input,
/deep/.el-pagination__editor,
/deep/.is-in-pagination {
  padding: 0rem !important;
}

/deep/.el-pagination__jump,
/deep/.el-pagination__total {
  color: rgba(255, 255, 255, 0.88) !important;
  padding: 0rem 0.6rem !important;
}

/deep/.el-menu-item {
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

/deep/.el-submenu__title {
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

/deep/.el-submenu__title:hover {
  color: deepskyblue !important;
  background-color: rgba(0, 0, 0, 0.46) !important;
}

/deep/.search {
  padding: 0.6rem 0.6rem !important;
}

/deep/.v-note-navigation-title,
/deep/.v-note-read-model,
/deep/.scroll-style.show,
/deep/.v-note-navigation-content,
/deep/.scroll-style,
/deep/.auto-textarea-input,
/deep/.no-border,
/deep/.no-resize,
/deep/.v-left-item,
/deep/.transition,
/deep/.v-right-item,
/deep/.transition {
  color: rgb(255, 255, 255) !important;
  background-color: rgba(0, 0, 0, 0) !important;
  border-width: 0rem !important;
  padding: 0rem 0rem;
}

/deep/.add-image-link {
  color: rgb(0, 0, 0) !important;
}

/deep/.v-note-op,
/deep/.content-input-wrapper,
/deep/.v-note-edit.diletea-wrapper.scroll-style.transition {
  color: rgb(255, 255, 255) !important;
  background-color: rgba(0, 0, 0, 0) !important;
}

/deep/.v-show-content,
/deep/.scroll-style,
/deep/.scroll-style-border-radius {
  background-color: rgba(0, 0, 0, 0) !important;
  padding: 0rem 0rem;
  border-width: 0rem !important;
}

/deep/.el-range-input,
/deep/.el-range-separator,
/deep/.el-data-editor,
/deep/.el-range-editor,
/deep/.el-data-editor--datetimerange {
  color: rgba(255, 255, 255, 1) !important;
  background-color: rgba(0, 0, 0, 0) !important;
  border-width: 0rem !important;
}

/deep/.el-notification {
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}

/deep/.el-pager {
  color: rgba(255, 255, 255, 1) !important;
  background-color: Transparent !important;
  border-width: 0rem !important;
}

/deep/.el-textarea__inner {
  color: rgba(255, 255, 255, 1) !important;
  background-color: rgba(30, 30, 30, 0.688) !important;
  border-color: rgba(30, 30, 30, 0.688) !important;
}

/deep/.el-scrollbar__thumb {
  border-width: 0rem !important;
  width: 0rem !important;
  height: 0rem !important;
  background-color: rgba(0, 0, 0, 0) !important;
}

/deep/.el-icon-arrow-left:before,
/deep/.el-icon-arrow-right:before {
  background-color: rgba(0, 0, 0, 0) !important;
}

/deep/.el-button--default {
  padding: 0rem 1.6rem !important;
  background-color: rgba(0, 0, 0, 0.1) !important;
  color: rgba(255, 255, 255, 1) !important;
}

/deep/.el-input-group__append {
  background-color: rgba(0, 0, 0, 0.2) !important;
  color: rgba(255, 255, 255, 1) !important;
  border-width: 0rem !important;
}

/deep/.el-descriptions__body {
  background-color: rgba(30, 30, 30, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
}

/deep/.el-descriptions-item__cell,
/deep/.el-descriptions-item__label,
/deep/.is-bordered-label {
  background-color: rgba(30, 30, 30, 1) !important;
  color: rgba(255, 255, 255, 1) !important;
}

/deep/.el-scrollbar__bar,
/deep/.is-horizontal {
  background-color: rgba(0, 0, 0, 0) !important;
  color: rgba(255, 255, 255, 1) !important;
  height: 0rem !important;
  border: none !important;
}

.musicdiv {
  position: fixed;
  bottom: 20rem;
  left: 0rem;
  padding: 0;
  max-width: 20rem;
  max-height: 6rem;
  z-index: 100000000;
}

.HomeMain {
  /* 页面样式编辑 */
  background-attachment: fixed !important;
  /* 图片固定 */
  background-repeat: no-repeat !important;
  background-size: 100%, auto !important;
  position: relative !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  padding: 0;
  /*清除元素的内边距*/
  margin: 0;
}

.fadeIn-enter-active {
  animation: fadeIn 1s;
  -webkit-touch-callout: none !important;
  /* iOS Safari */
  -webkit-user-select: none !important;
  /* Chrome/Safari/Opera */
  -khtml-user-select: none !important;
  /* Konqueror */
  -moz-user-select: none !important;
  /* Firefox */
  -ms-user-select: none !important;
  /* Internet Explorer/Edge */
  user-select: none !important;
  /* Non-prefixed version, currently not supported by any browser */
}
</style>
