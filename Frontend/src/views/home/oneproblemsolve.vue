<template>
  <div v-show="isseetip" class="no-select">
    <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
      <div
        class="shadow ltpp-list-box"
        style="
          color: azure;
          border-width: 0rem;
          border-color: rgba(var(--ltpp-main-bk-color), 0);
          height: auto;
          width: 100%;
        "
      >
        <div>
          <p
            class="shadow ltpp-list-box"
            style="
              font-size: 1.66rem;
              text-align: center;
              font-weight: bold;
              padding: 1rem 0rem;
              background-color: rgba(var(--ltpp-main-bk-color), 0.66);
              color: var(--ltpp-box-text-color);
            "
          >
            题 解 社 区
          </p>
          <div style="margin-left: auto; margin-right: auto">
            <div
              v-for="temtable in userarticle"
              :key="temtable.index"
              style="width: 31.33333%; float: left; padding: 1%"
            >
              <div
                v-loading.lock="!temtable || !temtable.id"
                element-loading-text="拼命加载中"
                element-loading-spinner="el-icon-loading"
                element-loading-background="background-color:rgba(var(--ltpp-main-bk-color),var(--ltpp-list-box-bk-opacity))"
                @click="toOneArticle(temtable.id)"
                style="cursor: pointer"
                class="shadow ltpp-list-box"
              >
                <div style="height: 22rem; width: 100%">
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
                        v-if="temtable.image && reg.test(temtable.image)"
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
                                  <div
                                    style="height: 0.4rem; clear: both"
                                  ></div>
                                  <p style="float: left; font-weight: bold">
                                    发布时间：
                                  </p>

                                  <p style="float: left; font-weight: bold">
                                    {{ temtable.releasetime }}
                                  </p>

                                  <div
                                    style="height: 0.4rem; clear: both"
                                  ></div>
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
              <div style="height: 1rem"></div>
            </div>
          </div>
          <!-- 清除浮动 -->
          <div class="clear"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "oneproblemsolve",
  created() {
    this.isseetip = true;
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
    this.problemid = urlencode.decode(this.$route.query.path, "gbk");
  },
  async activated() {
    this.isseetip = true;
    this.limit = 10;
    window.addEventListener("scroll", this.scrollBottom);
    this.page = 1;
    this.isinit = false;
    this.initData();
    await this.lookarticle();
    this.$nextTick(() => {
      this.totop();
    });
  },
  deactivated() {
    this.isseetip = false;
    this.isinit = false;
    this.userarticle = [];
    this.disabledscroll = false;
    this.page = 1;
    window.removeEventListener("scroll", this.scrollBottom);
  },
  destroyed() {
    this.isseetip = false;
    this.isinit = false;
    this.userarticle = [];
    this.page = 1;
    window.addEventListener("scroll", this.scrollBottom);
  },
  data() {
    return {
      isinit: false,
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      limit: 10,
      disabledscroll: false,
      isseetip: true,
      aclist: [],
      canclick: true,
      problemid: 0,
      page: 1,
      userdata: [],
      userarticle: [],
      isfollow: false,
    };
  },
  methods: {
    initData() {
      this.userarticle = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.article_list_data);
      }
      this.userarticle = tem_list;
    },
    async scrollBottom() {
      if (this.disabledscroll) {
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
      if (scrollTop === 0) {
        this.disabledscroll = true;
        if (this.page <= 1) {
          this.page = 1;
          this.userarticle.splice(this.limit, this.limit);
          return;
        }
        this.page--;
        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Article/problemSolveArticleList",
          portType: {
            process: "8792",
          },
          data: {
            problem_id: this.problemid,
            limit: this.limit,
            page: this.page,
          },
        }).catch((t) => {
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
          this.isinit = true;
        });
        if (res?.data?.length <= 0) {
          if (!this.isinit) {
            this.userarticle = [];
          }
          this.userarticle.push(...res?.data);
          return;
        }
        this.userarticle.splice(this.limit, this.limit);
        for (let i = res?.data.length - 1; i >= 0; --i) {
          this.userarticle.unshift(res?.data[i]);
        }
        window.scrollTo(0, document.body.clientHeight / 2);
        this.isinit = true;
        return;
      }
      //滚动条到底部的条件
      if (!(scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100)) {
        return;
      }
      this.disabledscroll = true;
      this.lookarticle();
    },
    toback() {
      this.$router.go(-1);
    },
    toOneArticle(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/onearticle",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },

    async lookarticle() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/problemSolveArticleList",
        portType: {
          process: "8792",
        },
        data: {
          problem_id: this.problemid,
          limit: this.limit,
          page: this.page++,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.page--;
        this.isinit = true;
      });
      if (res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        setTimeout(() => {
          this.$router.go(-1);
        }, 1000);
      } else if (
        res?.data &&
        res?.data.length <= 0 &&
        this.userarticle.length <= 0
      ) {
        this.$msg({
          type: "success",
          message: "暂无题解！即将返回！",
          duration: 1600,
          offset: 80,
        });
        setTimeout(() => {
          this.$router.go(-1);
        }, 1000);
      } else if (res?.data && res?.data.length <= 0) {
        this.$msg({
          type: "success",
          message: "没有更多内容啦！",
          duration: 1600,
          offset: 80,
        });
      }
      if (!this.isinit) {
        this.userarticle = [];
      }
      if (res?.data.length) {
        this.userarticle.push(...res?.data);
      }
      this.isinit = true;
    },
  },
};
</script>

<style scoped>
.clear {
  clear: both;
}

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