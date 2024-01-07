/* 
在线课堂
 */
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto; min-height: 50rem"
  >
    <div>
      <div>
        <div v-show="finish">
          <div>
            <div
              style="
                background-color: rgba(var(--ltpp-main-bk-color), 0.2);
                color: azure;
                border-width: 0rem;
                border-color: rgba(
                  var(--ltpp-deep-color),
                  var(--ltpp-center-box-bk-opacity)
                );
              "
            >
              <div class="search shadow">
                <div
                  v-if="
                    classurl != undefined && classurl != null && classurl != ''
                  "
                  id="dplayer"
                  :style="`height:${
                    $store.state.no_scroll_height * 0.82
                  }vh; width: 100%`"
                ></div>
              </div>
            </div>
            <div style="height: 2rem"></div>
          </div>
          <div>
            <div
              style="
                background-color: rgba(248, 249, 250, 0.2);
                color: azure;
                border-width: 0rem;
              "
            >
              <div class="search shadow" style="position: relative">
                <el-input
                  type="textarea"
                  style="font-size: 1rem; overflow: hidden"
                  autosize
                  placeholder="请输入内容"
                  v-model.lazy="mymessage"
                ></el-input>
                <el-button
                  type="text"
                  width="auto"
                  class="el-icon-s-promotion pulse-enter-active"
                  style="
                    position: absolute;
                    top: -0.2rem;
                    right: 0.6rem;
                    font-size: 1.06rem;
                    color: deeppink;
                    margin-right: 1rem;
                    margin-top: 0.6rem;
                  "
                  @click="postmessage()"
                  >发送</el-button
                >
              </div>
            </div>
          </div>
          <div style="height: 1rem"></div>
          <div v-show="messagedata && messagedata.length > 0">
            <div
              class="shadow"
              style="
                height: 36rem;
                overflow-x: hidden;
                background-color: rgba(var(--ltpp-main-bk-color), 0.2);
                padding: 1rem 1rem;
              "
              id="chatBox-content-demo"
            >
              <div v-for="temcomment in messagedata" :key="temcomment.index">
                <p
                  type="text"
                  style="font-size: 1.06rem; color: red; margin: 0.6rem"
                >
                  {{ temcomment.name }}:
                </p>
                <el-input
                  type="textarea"
                  style="font-size: 1rem; overflow: hidden"
                  autosize
                  v-model.lazy="temcomment.msg"
                ></el-input>
              </div>
              <div style="height: 6rem"></div>
            </div>
          </div>
        </div>
        <div style="clear: both"></div>
      </div>
      <div style="clear: both"></div>
    </div>
  </div>
</template>

<script>
import DPlayer from "../../../updateCompoents/dplayer";
export default {
  name: "classteach",
  data() {
    return {
      timer: null,
      socketurl: "",
      name: "",
      finish: 0,
      isroot: false,
      mymessage: "",
      messagedata: [],
      dplayer: null,
      classurl: "",
      websocket: null,
    };
  },
  async activated() {
    this.classurl = window.sessionStorage.getItem("classurl");
    if (!this.classurl) {
      this.getclassurl();
    }
    this.timer = setInterval(() => {
      if (!this.classurl) {
        this.getclassurl();
      } else {
        this.finish++;
      }
      if (this.finish == 1) {
        /* 只挂载一次 */
        try {
          this.dplayer = new DPlayer({
            container: document.getElementById("dplayer"),
            live: true, //是否直播
            autoplay: false, //是否自动播放
            lang: "zh-cn", //设置中文
            video: {
              url: this.classurl,
              type: "hls", //这一步必须要写，播放直播流
            },
          });
        } catch (err) {}
        clearInterval(this.timer);
        this.timer = null;
      }
    }, 500);
    this.socketurl = window.sessionStorage.getItem("socketurl");
    await this.getname();
    let authorization = window.localStorage.getItem("authorization");
    let key = window.localStorage.getItem("key");
    if (authorization && key && this.$store.state.login) {
      this.websocket = new WebSocket(
        this.socketurl +
          "?" +
          authorization +
          this.$SqsGlobal.websocket_connect_str +
          key
      );
    }
    this.setup();
  },
  async deactivated() {
    this.messagedata = [];
    await this.websocket.close();
    clearInterval(this.timer);
    this.timer = null;
  },
  async destroyed() {
    this.messagedata = [];
    await this.websocket.close();
    clearInterval(this.timer);
    this.timer = null;
  },
  methods: {
    async getname() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/getMyName",
        portType: {
          process: "8792",
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
        this.name = res.name;
      } else {
        this.name = "无名氏";
      }
    },
    setup() {
      let func_this = this;
      let temdata = {};
      //Listen for the connection open event then call the sendMessage function
      this.websocket.onopen = function (e) {
        func_this.$msg({
          type: "success",
          message: "您已进入课堂",
          duration: 1600,
          offset: 80,
        });
      };
      //Listen for the close connection event
      this.websocket.onclose = function (e) {
        func_this.$msg({
          type: "success",
          message: "您已退出课堂",
          duration: 1600,
          offset: 80,
        });
      };
      //Listen for connection errors
      this.websocket.onerror = function (e) {
        func_this.$msg({
          type: "error",
          message: "发生错误",
          duration: 1600,
          offset: 80,
        });
      };
      //Listen for new messages arriving at the client
      this.websocket.onmessage = function (e) {
        temdata = eval("(" + e.data + ")");
        if (temdata.msgtype && temdata.msgtype == "class") {
          func_this.messagedata.push(temdata);
        }
      };
    },
    postmessage() {
      let t1 = this.mymessage;
      let value1 = t1.replace(/\s+/g, "");
      if (value1 == "") {
        this.$msg({
          type: "error",
          message: "评论不能为空",
          duration: 800,
          offset: 80,
        });
        return;
      }
      let msg = JSON.stringify({
        msgtype: "class",
        msg: this.mymessage,
      });
      this.websocket.send(msg);
      this.mymessage = "";
      this.$msg({
        type: "success",
        message: "发言成功",
        duration: 800,
        offset: 80,
      });
    },
    async getclassurl() {
      const { data: res } = await this.$ajax({
        method: "get",
        url: "/Url/getClassUrl",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.finish = 1;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.finish = 1;
      if (res.code == 1) {
        this.classurl = res.classurl;
        window.sessionStorage.setItem("classurl", res.classurl);
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 800,
          offset: 80,
        });
      }
    },
  },
};
</script>