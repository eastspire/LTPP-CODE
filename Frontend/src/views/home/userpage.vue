<template>
  <div v-show="isseetip">
    <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
      <div
        class="shadow"
        style="
          background-color: rgba(var(--ltpp-deep-color), var(--ltpp-center-box-bk-opacity));
          color: azure;
          border-width: 0rem;
          border-color: rgba(var(--ltpp-deep-color), var(--ltpp-center-box-bk-opacity))
          height: auto;
          width: 100%;
        "
      >
        <div style="margin-left: 1.6rem; margin-right: 1.6rem">
          <div style="height: 0.6rem"></div>
          <div>
            <p
              style="
                font-size: 2.46rem;
                color: aqua;
                text-align: center;
                margin: 1.6rem 1rem;
              "
            >
              {{
                userdata.name == undefined
                  ? "加载中"
                  : "「 " + userdata.name + " 」的主页"
              }}
            </p>
          </div>
          <div
            v-show="userdata.name != undefined"
            style="width: 26%; float: left; overflow: hidden"
          >
            <img
              class="animate"
              style="
                display: block;
                width: 100%;
                height:auto
                margin: 1rem 1rem;
              "
              title=""
              alt=""
              :src="userdata.headimage"
            />
          </div>

          <div style="width: 60%; float: left; margin-left: 2.6rem">
            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                用户名/账号：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="danger"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined
                    ? "加载中"
                    : String(userdata.name).substr(0, 10)
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                邮箱：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="success"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.email == undefined
                    ? "无"
                    : String(userdata.email).substr(0, 20)
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                性别：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.sex
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                状态：
              </p>
              <el-tag
                class="pulse-enter-active"
                v-show="userdata.online == 1"
                effect="dark"
                type="danger"
                style="font-size: 1.06rem; font-weight: bold"
                >{{ userdata.name == undefined ? "未知" : "在线" }}</el-tag
              >
              <el-tag
                class="pulse-enter-active"
                v-show="userdata.online != 1"
                effect="dark"
                type="info"
                style="font-size: 1.06rem; font-weight: bold"
                >{{ userdata.name == undefined ? "未知" : "离线" }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                粉丝：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="warning"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.fans
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                关注：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="warning"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.follow
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                竞赛积分：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="danger"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.acnum
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                注册时间：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.registertime
                }}</el-tag
              >
            </div>

            <div style="float: left; margin: 1rem 1rem">
              <p
                style="
                  float: left;
                  margin: 0.12rem 0.6rem 0rem 0rem;
                  font-weight: bold;
                  font-size: 1.06rem;
                "
              >
                最近在线：
              </p>
              <el-tag
                class="pulse-enter-active"
                effect="dark"
                type="danger"
                style="font-size: 1.06rem; font-weight: bold"
                >{{
                  userdata.name == undefined ? "未知" : userdata.lastlogin
                }}</el-tag
              >
            </div>
            <!-- 清除浮动 -->
            <div class="clear"></div>
          </div>
          <div class="clear"></div>
          <br />
          <div v-show="aclist.length > 0">
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 0rem 1rem 0rem;
                color: deeppink;
              "
            >
              AC的题目（ 共{{ aclist.length }}题 ）
            </p>
            <el-tag
              v-for="(tem, index) in aclist"
              class="pulse-enter-active"
              :key="index"
              effect="dark"
              type="success"
              @click="toonepro(tem.id)"
              style="
                cursor: pointer;
                font-size: 1.06rem;
                font-weight: bold;
                margin-right: 1rem;
                margin-bottom: 1rem;
              "
              >{{
                tem.problemName == undefined ? "未知" : tem.problemName
              }}</el-tag
            >
          </div>

          <div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 0rem 1rem 0rem;
                color: deeppink;
              "
            >
              个性签名
            </p>
            <div class="markdown-body">
              <mavon-editor
                class="md"
                :codeStyle="prop.codeStyle"
                :toolbars="toolbars"
                :value="
                  userdata.mysay ? userdata.mysay : '该用户暂未发布个性签名'
                "
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
                :xssOptions="whiteList"
                style="
                  color: #ebeef5;
                  min-height: 0rem;
                  height: auto;
                  border-width: 0rem;
                "
              >
              </mavon-editor>
            </div>
          </div>
          <div style="height: 2rem"></div>
          <div>
            <div style="text-align: left; float: left; padding: 0.6rem 2rem">
              <el-button
                v-show="!isfollow && canclick"
                width="auto"
                type="text"
                style="font-size: 1.06rem; font-weight: bold; color: aqua"
                @click="followuser"
                class="el-icon-star-off pulse-enter-active"
                >关注</el-button
              >
              <el-button
                v-show="isfollow && canclick"
                width="auto"
                type="text"
                style="
                  color: red;

                  font-size: 1.06rem;
                  font-weight: bold;
                "
                @click="deletefollowuser"
                class="el-icon-star-off pulse-enter-active"
                >取消关注</el-button
              >
            </div>
            <div style="text-align: right; padding: 0.6rem 2rem">
              <el-button
                width="auto"
                type="text"
                @click="toback()"
                class="el-icon-s-unfold pulse-enter-active"
                style="font-size: 1.06rem; font-weight: bold; color: red"
                >点击返回</el-button
              >
            </div>
            <div style="clear: both"></div>
          </div>

          <div style="height: 5.46rem"></div>
        </div>
      </div>
      <div style="height: 4rem"></div>
      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
          color: aliceblue;
        "
      >
        用户发布历史
      </p>
      <div style="height: 1rem"></div>
      <div style="margin-left: auto; margin-right: auto">
        <div
          v-for="temtable in userarticle"
          :key="temtable.index"
          style="width: 44%; float: left; margin: 0 3%"
        >
          <div
            @click="toonearticle(temtable.id)"
            style="cursor: pointer"
            class="shadow"
          >
            <div
              style="
                background-color: rgba(var(--ltpp-deep-color), var(--ltpp-center-box-bk-opacity));
                color: azure;
                height: 22rem;
                width: 100%;
              "
            >
              <div style="padding: 0rem; position: relative">
                <div
                  style="
                    text-align: center;
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 22rem;
                    z-index: 0;
                    overflow: hidden;
                  "
                >
                  <!-- img才生效图片裁剪适应，el-image不生效 -->
                  <img
                    class="animate"
                    style="width: 100%; height: 100%; object-fit: cover"
                    title=""
                    alt=""
                    :src="temtable.image"
                  />
                </div>
                <div
                  style="
                    text-align: center;
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 22rem;
                    overflow: hidden;
                  "
                >
                  <div>
                    <h3
                      style="
                        text-align: center;
                        background-color: rgba(248, 249, 250, 0.46);
                        margin: 1.2rem 0.6rem 0.6rem 0.6rem;
                        border-radius: 0.36rem;
                        padding: 0.6rem;
                      "
                    >
                      <el-tooltip
                        class="item;"
                        effect="dark"
                        :content="temtable.name"
                        placement="top"
                      >
                        <p
                          style="
                            overflow: hidden;
                            text-align: center;
                            color: rgba(0, 0, 0, 0.88);
                          "
                        >
                          {{ temtable.name.substr(0, 14) }}
                        </p>
                      </el-tooltip>
                    </h3>
                    <div
                      style="
                        background-color: rgba(248, 249, 250, 0.46);
                        margin: 0.6rem;
                        border-radius: 0.36rem;
                        color: rgba(0, 0, 0, 0.88);
                        padding: 0.6rem;
                      "
                    >
                      <p
                        style="
                          font-size: 1rem;
                          height: 2.5rem;
                          overflow: hidden;
                          text-align: left;
                        "
                      >
                        {{
                          temtable.article
                            .substr(0, 160)
                            .replace($store.state.article_reg, "")
                            .substr(0, 40)
                        }}[...]
                      </p>
                      <div>
                        <div style="font-size: 0.88rem">
                          <span class="my-span">
                            <span class="my-span">
                              <div style="height: 0.4rem; clear: both"></div>
                              <p style="float: left; font-weight: bold">
                                发布时间：
                              </p>

                              <p style="float: left; font-weight: bold">
                                {{ temtable.releasetime }}
                              </p>

                              <div style="height: 0.4rem; clear: both"></div>
                              <p style="float: left; font-weight: bold">
                                点赞数：
                              </p>

                              <p style="float: left; font-weight: bold">
                                {{ temtable.fabulous }}
                              </p>
                              <div style="clear: both"></div>
                            </span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div style="height: 2.6rem"></div>
        </div>
      </div>
      <!-- 清除浮动 -->
      <div class="clear"></div>
      <p
        v-show="!showfoot"
        style="
          font-size: 1.06rem;
          color: deeppink;
          text-align: center;
          font-weight: bold;
        "
      >
        没有更多内容啦！
      </p>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../updateCompoents/mavon-editor/dist/markdown/github-markdown.min.css";
import "../../../updateCompoents/mavon-editor/dist/css/index.css";

export default {
  name: "userpage",
  created() {
    this.isseetip = true;
    this.showfoot = false;
    this.page = 1;
    this.isfollow = false;
    if (
      !(
        this.$route &&
        this.$route.query &&
        this.$route.query.path &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.$router.go(-1);
      return;
    }
    this.userid = urlencode.decode(this.$route.query.path, "gbk");
    this.userarticle = [];
    this.page = 1;
    this.lookuseraclist();
    this.lookarticle();
    this.$nextTick(() => {
      this.totop();
    });
  },
  activated() {
    this.judgefollow();
    this.lookdata();
    this.isseetip = true;
    window.addEventListener("scroll", this.scrollBottom);
    this.timer = setInterval(() => {
      this.timeclock = false;
    }, 600);
  },
  deactivated() {
    this.isseetip = false;
    clearInterval(this.timer);
    this.timer = null;
    window.removeEventListener("scroll", this.scrollBottom);
  },
  destroyed() {
    this.isseetip = false;
    this.userarticle = [];
    this.page = 1;
    clearInterval(this.timer);
    this.timer = null;
    window.addEventListener("scroll", this.scrollBottom);
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
      timer: null,
      isseetip: true,
      aclist: [],
      canclick: true,
      showfoot: false,
      userid: 0,
      page: 1,
      userdata: [],
      userarticle: [],
      isfollow: false,
      timeclock: false,
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
        imagelink: true, // 图片链接

        code: true, // code
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
    };
  },
  methods: {
    toonepro(id) {
      this.$router.push({
        path: "/oneproblem",
        query: {
          path: urlencode(id, "gbk"),
          contest: urlencode("", "gbk"),
        },
      });
    },
    async lookuseraclist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/lookUserAcList",
        portType: {
          process: "8793",
        },
        data: {
          user_id: this.userid,
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
        this.aclist = res.data;
      }
    },
    scrollBottom() {
      if (this.timeclock) {
        return;
      }
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
      if (scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100) {
        //写后台加载数据的函数
        this.page++;
        this.timeclock = true;
        this.lookarticle();
      }
    },
    toback() {
      this.$router.go(-1);
    },
    toonearticle(id) {
      id &&
        this.$router.push({
          path: "/onearticle",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    async deletefollowuser() {
      this.canclick = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/deleteFollow",
        portType: {
          process: "8793",
        },
        data: {
          delete_id: this.userid,
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
        this.userdata.fans--;
        if (this.userid == this.getMyId()) {
          this.userdata.follow = Math.max(0, --this.userdata.follow);
        }
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
          duration: 800,
          offset: 80,
        });
      }
      this.canclick = true;
      this.judgefollow();
    },
    async lookdata() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/lookUserData",
        portType: {
          process: "8793",
        },
        data: {
          user_id: this.userid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.userdata = res.data;
      if (res.code != 1) {
        this.$router.go(-1);
      }
    },
    async judgefollow() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/JudgeIsFollow",
        portType: {
          process: "8793",
        },
        data: {
          user_id: this.userid,
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
        this.isfollow = true;
      } else {
        this.isfollow = false;
      }
    },
    async lookarticle() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadUserHomeArticle",
        portType: {
          process: "8792",
        },
        data: {
          user_id: this.userid,
          article_id: this.userarticle.length
            ? this.userarticle[this.userarticle.length - 1].id
            : 0,
          limit: 10,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.data == false) {
        this.showfoot = false;
      } else {
        this.showfoot = true;
        this.userarticle.push(...res.data);
      }
    },

    async followuser() {
      this.canclick = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/addFollow",
        portType: {
          process: "8793",
        },
        data: {
          follow_id: this.userid,
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
        this.userdata.fans++;
        if (this.userid == this.getMyId()) {
          this.userdata.follow = Math.max(0, ++this.userdata.follow);
        }
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
          duration: 600,
          offset: 80,
        });
      }
      this.canclick = true;
      this.judgefollow();
    },
  },
};
</script>


<style  lang="less" scoped>
@import "../../../public/md/markdown/github-markdown.min.css";

.clear {
  clear: both;
}

/**
鼠标放上，图片变大
*/
.animate {
  -webkit-transition: all 1s ease; /* Safari and Chrome */
  -moz-transition: all 1s ease; /* Firefox */
  -o-transition: all 1s ease; /* Opera */
  -ms-transition: all 1s ease; /* IE 9 */
  transition: all 1s ease;
}

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