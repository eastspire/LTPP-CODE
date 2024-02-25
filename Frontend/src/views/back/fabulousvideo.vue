/* 
点赞视频
 */

<template>
  <div
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow ltpp-list-box">
      <div class="search shadow">
        <el-input
          id="search-input"
          style="font-size: 1.06rem"
          placeholder="请输入需要搜索的视频名称"
          @keyup.enter.native="search()"
          v-model.lazy="key"
          class="input-with-select"
        >
          <el-button slot="append" icon="el-icon-search" @click="search()"
            >搜索</el-button
          >
        </el-input>
      </div>
    </div>
    <div style="height: 1rem"></div>
    <div>
      <div>
        <div>
          <div class="my-span-parent">
            <span
              class="my-span"
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                color: rgba(248, 249, 250, 0.88);
              "
              >{{ onevideo.name }}</span
            >
          </div>
          <div style="height: 0.5rem"></div>
          <div
            v-loading.lock="!onevideo.url || !reg.test(onevideo.url)"
            element-loading-text="拼命加载中"
            element-loading-spinner="el-icon-loading"
            element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"
            class="shadow ltpp-list-box"
            style="
              color: azure;
              backdrop-filter: blur(0.46rem);
              border-width: 0rem;
              text-align: center;
            "
          >
            <div class="video-box search">
              <video
                id="nowvideo"
                :src="onevideo.url"
                :style="`height:${
                  $store.state.no_scroll_height * 0.8
                }vh; width: 100%`"
                preload
                controls
                controlslist="nodownload"
              ></video>
              <span
                v-show="page > 1"
                class="dec el-icon-caret-left my-span"
                @click="pageChange(false)"
              ></span>
              <span
                v-show="page < total"
                class="inc el-icon-caret-right my-span"
                @click="pageChange(true)"
              ></span>

              <span
                v-show="isfabulous"
                class="fabulous el-icon-success my-span"
                @click="DelFabulousVideo(true)"
              ></span>
              <span
                v-show="islove"
                class="love el-icon-star-on my-span"
                @click="DelLoveVideo(true)"
              ></span>

              <span
                v-show="!isfabulous"
                class="no-fabulous el-icon-circle-check my-span"
                @click="FabulousVideo(true)"
              ></span>
              <span
                v-show="!islove"
                class="no-love el-icon-star-off my-span"
                @click="LoveVideo()"
              ></span>

              <span
                class="comment el-icon-s-comment my-span"
                @click="
                  comment_load_all_finish = false;
                  userComment = [];
                  isSeeComment = true;
                  loadUserComment();
                "
              ></span>

              <span
                class="share el-icon-share my-span"
                @click="share(true)"
              ></span>
            </div>
          </div>
        </div>
      </div>

      <el-drawer
        :title="`评论:【视频${onevideo.fabulous}人点赞】【视频${onevideo.love}人收藏】`"
        :size="drawer_size"
        @contextmenu.prevent=""
        :visible.sync="isSeeComment"
        direction="rtl"
        ref="drawer"
      >
        <div id="scroll-comment">
          <el-input
            v-model.lazy="mysay"
            placeholder="请友善评论~"
            style="width: 88%; margin-left: 1%"
            maxlength="1000"
            @keyup.enter.native="SendMyComment(0)"
          ></el-input>
          <el-button
            type="text"
            class="el-icon-s-promotion pulse-enter-active"
            style="
              font-size: 1.06rem;
              color: deeppink;
              margin-left: 1%;
              width: 10%;
            "
            @click="SendMyComment(0)"
          ></el-button>
          <div style="height: 1rem"></div>
          <div v-show="userComment.length <= 0" style="text-align: center">
            <p
              style="
                font-size: 1rem;
                font-weight: bold;
                text-align: center;
                color: var(--ltpp-box-text-color);
              "
            >
              暂无评论
            </p>
          </div>
          <div v-show="userComment.length > 0">
            <div>
              <div
                v-for="temcomment in userComment"
                :key="temcomment.index"
                style="
                  margin-bottom: 0rem;
                  margin-left: 0.4rem;
                  color: aliceblue;
                "
              >
                <el-avatar
                  :src="temcomment.userheadimg"
                  style="height: 2.6rem; width: 2.6rem; float: left"
                ></el-avatar>
                <el-button
                  class="no-select"
                  type="text"
                  style="
                    font-size: 1.06rem;
                    color: deeppink;
                    float: left;
                    margin-left: 0.4rem;
                  "
                  @click="touserpage(temcomment.userid)"
                  >{{ temcomment.username }}</el-button
                >
                <el-button
                  class="no-select"
                  type="text"
                  style="
                    font-size: 1rem;
                    color: greenyellow;
                    cursor: auto;
                    float: left;
                  "
                  >({{ temcomment.time }})</el-button
                >
                <div style="clear: both"></div>
                <pre class="comment-pre">{{ temcomment.text }}</pre>
                <div style="clear: both"></div>
                <div style="text-align: right; margin: 0.2rem 1rem">
                  <el-button
                    v-show="
                      ($store.state.root && $store.state.my_name === 'root') ||
                      temcomment.userid == $store.state.my_id
                    "
                    size="medium"
                    type="text"
                    style="font-size: 1.06rem; margin-right: 1rem"
                    class="el-icon-delete"
                    @click="DeleteComment(temcomment.id)"
                  ></el-button>
                  <el-button
                    size="medium"
                    type="text"
                    style="color: red; font-size: 1.06rem"
                    class="el-icon-s-comment"
                    @click="
                      isSeeComment = false;
                      tolooksay(temcomment.id, temcomment.userid);
                    "
                  ></el-button>
                </div>
                <div
                  style="margin-left: 1rem"
                  v-for="tem in temcomment.touserarray"
                  :key="tem.index"
                >
                  <el-avatar
                    :src="tem.userheadimg"
                    style="height: 2.6rem; width: 2.6rem; float: left"
                  ></el-avatar>
                  <el-button
                    class="no-select"
                    type="text"
                    style="
                      font-size: 1.06rem;
                      color: deeppink;
                      float: left;
                      margin-left: 0.4rem;
                    "
                    @click="touserpage(tem.userid)"
                    >{{ tem.username }}</el-button
                  >
                  <el-button
                    class="no-select"
                    type="text"
                    style="
                      font-size: 1.06rem;
                      color: skydeepblue;
                      float: left;
                      margin-left: 0.4rem;
                      cursor: auto;
                    "
                    >回复</el-button
                  >
                  <el-avatar
                    :src="tem.touserheadimg"
                    style="
                      height: 2.6rem;
                      width: 2.6rem;
                      float: left;
                      margin-left: 0.4rem;
                    "
                  ></el-avatar>
                  <el-button
                    class="no-select"
                    type="text"
                    style="
                      font-size: 1.06rem;
                      color: deeppink;
                      float: left;
                      margin-left: 0.4rem;
                    "
                    @click="touserpage(tem.touserid)"
                    >{{ tem.tousername }}</el-button
                  >
                  <el-button
                    class="no-select"
                    type="text"
                    style="
                      font-size: 1.06rem;
                      color: skydeepblue;
                      cursor: auto;
                      float: left;
                    "
                    >({{ tem.time }})</el-button
                  >
                  <div style="clear: both"></div>
                  <pre class="comment-pre">{{ tem.text }}</pre>
                  <div style="clear: both"></div>
                  <div style="text-align: right; margin: 0.2rem 1rem">
                    <el-button
                      v-show="
                        ($store.state.root &&
                          $store.state.my_name === 'root') ||
                        tem.userid == $store.state.my_id
                      "
                      size="medium"
                      type="text"
                      style="font-size: 1.06rem; margin-right: 1rem"
                      class="el-icon-delete"
                      @click="DeleteComment(tem.id)"
                    ></el-button>
                    <el-button
                      size="medium"
                      type="text"
                      style="color: red; font-size: 1.06rem"
                      class="el-icon-s-comment"
                      @click="
                        isSeeComment = false;
                        tolooksay(tem.maincommentid, tem.userid);
                      "
                    ></el-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div style="clear: both; height: 2rem"></div>
        </div>
        <div
          style="text-align: center; font-size: 1.06rem"
          v-show="!comment_load_all_finish"
        >
          <el-button
            type="text"
            class="pulse-enter-active"
            @click="loadUserComment()"
            >加载更多</el-button
          >
          <div style="height: 1rem"></div>
        </div>
      </el-drawer>
    </div>
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      class="no-select"
      :visible.sync="isseeDia"
      width="36%"
      title="回复评论"
    >
      <div style="text-align: center">
        <el-input
          style="width: 98%"
          type="textarea"
          :autosize="{ minRows: 8, maxRows: 10000000 }"
          v-model.lazy="diamysay"
          placeholder="友善评论~"
        >
        </el-input>
        <div style="text-align: right">
          <el-button
            type="text"
            width="auto"
            class="el-icon-s-promotion pulse-enter-active"
            style="
              font-size: 1.06rem;
              color: deeppink;
              margin-right: 1rem;
              margin-top: 0.6rem;
            "
            @click="SendMyComment(mainid)"
            >发表评论</el-button
          >
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "fabulousvideo",
  created() {
    this.issearch = false;
    this.showone = false;
    this.total = 0;
  },
  async activated() {
    this.isSeeComment = false;
    this.onevideo = {};
    this.page = 1;

    this.comment_load_all_finish = false;
    this.userComment = [];
    this.$store.commit("updateObj", { my_id: this.getMyId() });
    if (this.total != 0) {
      if (this.issearch) {
        await this.search();
      } else {
        await this.getlist();
      }
    } else {
      await this.getlist();
    }
    await this.IsFabulous();
    await this.IsLove();

    this.comment_load_all_finish = false;
    this.video = document.getElementById("nowvideo");
    this.video && this.video.addEventListener("ended", this.videoEnd);
  },
  deactivated() {
    this.onevideo = {};
    this.isSeeComment = false;
    this.userComment = [];
    this.comment_load_all_finish = false;
    this.video && this.video.removeEventListener("ended", this.videoEnd);
  },
  data() {
    return {
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      drawer_size: "460px",
      comment_load_all_finish: false,
      isSeeComment: false,
      isfabulous: false,
      islove: false,
      comment_load_all_finish: false,
      touserid: 0,
      mainid: 0,
      isseeDia: false,
      commentLock: false,
      mysay: "",
      diamysay: "",
      userComment: [],
      video: null,
      lastkey: "",
      onevideo: {},
      key: "",
      page: 1,
      issearch: false,
      showone: false,
      total: 0,
    };
  },
  methods: {
    async videoPlay() {
      let deep = 0;
      while (deep < this.$SqsGlobal.max_video_retry_times) {
        try {
          this.video && (await this.video.play());
          break;
        } catch (err) {
          ++deep;
        }
      }
    },
    videoEnd() {
      this.video && this.videoPlay();
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
    tolooksay(mainid, touserid) {
      this.touserid = touserid;
      this.mainid = mainid;
      this.isseeDia = true;
    },
    async IsFabulous() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/judgeIsFabulous",
        data: {
          video_id: this.onevideo.id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data == 1) {
        this.isfabulous = true;
      } else {
        this.isfabulous = false;
      }
    },
    async IsLove() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/judgeIsLove",
        data: {
          video_id: this.onevideo.id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data == 1) {
        this.islove = true;
      } else {
        this.islove = false;
      }
    },
    // 收藏视频
    async LoveVideo() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/loveVideo",
        data: {
          video_id: this.onevideo.id,
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
        this.islove = true;
        this.onevideo.love = Math.max(this.onevideo.love + 1, 0);
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 点赞视频
    async FabulousVideo() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/fabulousVideo",
        data: {
          video_id: this.onevideo.id,
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
        this.isfabulous = true;
        this.onevideo.fabulous = Math.max(this.onevideo.fabulous + 1, 0);
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 取消收藏
    async DelLoveVideo() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/deleteLoveVideo",
        data: {
          video_id: this.onevideo.id,
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
        this.islove = false;
        this.onevideo.love = Math.max(this.onevideo.love - 1, 0);
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 取消点赞
    async DelFabulousVideo() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/deleteFabulousVideo",
        data: {
          video_id: this.onevideo.id,
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
        this.isfabulous = false;
        this.onevideo.fabulous = Math.max(this.onevideo.fabulous - 1, 0);
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 发表评论
    async SendMyComment(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/sendMyComment",
        data: {
          video_id: this.onevideo.id,
          maincomment_id: id,
          touser_id: this.touserid,
          text: this.touserid == 0 ? this.mysay : this.diamysay,
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
        this.comment_load_all_finish = false;
        this.isseeDia = false;
        this.mysay = "";
        this.diamysay = "";
        this.touserid = 0;
        this.userComment = [];
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }

      this.comment_load_all_finish = false;
      this.userComment = [];

      this.isSeeComment = true;
      this.loadUserComment();
    },
    // 加载评论
    async loadUserComment() {
      if (this.comment_load_all_finish) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/loadUserComment",
        data: {
          comment_id:
            this.userComment?.length > 0
              ? this.userComment[this.userComment.length - 1].id
              : 0,
          video_id: this.onevideo.id,
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
        await res?.data.forEach(async (tem) => {
          await this.userComment.push(tem);
        });

        if (res.is_end) {
          this.comment_load_all_finish = true;
          setTimeout(() => {}, 360);
          return;
        }
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async DeleteComment(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/DeleteComment",
        data: {
          video_id: this.onevideo.id,
          delete_id: id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.comment_load_all_finish = false;

      if (res?.code == 1) {
        this.userComment = [];
        this.loadUserComment();
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/LoadFabulousVideo",
        data: {
          page: this.page,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data?.url) {
        this.onevideo = res?.data;
      }
      this.total = res?.allnum;
      if (!this.total || res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async pageChange(isInc = true) {
      isInc
        ? (this.page = Math.min(this.page + 1, this.total))
        : (this.page = Math.max(0, this.page - 1));
      this.comment_load_all_finish = false;
      this.userComment = [];
      if (this.issearch) {
        await this.keysearch();
      } else {
        await this.getlist();
      }
      await this.IsFabulous();
      await this.IsLove();
      this.$nextTick(() => {
        this.video = document.getElementById("nowvideo");
        this.video && this.videoPlay();
      });
    },
    async share() {
      let front_url = await this.getFronturl();
      let url =
        front_url + "/onevideo?path=" + urlencode(this.onevideo.id, "gbk");
      this.copy(url);
    },
    async keysearch() {
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Video/FindFabulousVideo",
        data: {
          key: this.key,
          page: this.page,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data?.url) {
        this.onevideo = res?.data;
      }
      this.total = res?.allnum;
      if (!this.total || res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async search() {
      this.issearch = false;
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.page = 1;
        await this.getlist();
        await this.IsFabulous();
        await this.IsLove();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.issearch = true;
      await this.keysearch();
      await this.IsFabulous();
      await this.IsLove();
    },
  },
};
</script>
<style scoped>
.demo-drawer__content {
  padding: 2rem;
  text-align: center;
}

.comment-pre {
  font-size: 1.06rem;
  padding: 0.4rem;
}

.video-box {
  position: relative;
}

.video-box .dec {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 50%;
  left: 2%;
  transform: translateY(-50%);
}

.video-box .inc {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 50%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .love {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: deeppink;
  top: 40%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .no-love {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 40%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .fabulous {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: deeppink;
  top: 30%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .no-fabulous {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 30%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .comment {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 60%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box .share {
  position: absolute;
  cursor: pointer;
  font-size: 2rem;
  color: rgba(248, 249, 250, 0.6);
  top: 70%;
  right: 2%;
  transform: translateY(-50%);
}

.video-box span:hover {
  color: deeppink;
  font-size: 2.6rem;
  animation-name: txt-to-big;
  animation-duration: 0.6s;
}

@keyframes txt-to-big {
  0% {
    font-size: 2rem;
  }

  100% {
    font-size: 2.6rem;
  }
}
</style>