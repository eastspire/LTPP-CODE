/*
所有文章路由

*/

<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div class="ltpp-list-box">
        <div class="search shadow">
          <el-input
            style="font-size: 1rem"
            placeholder="请输入需要搜索的文章名称"
            v-model.lazy="key"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div style="height: 1rem"></div>
      <div>
        <div style="width: 100%; margin-left: auto; margin-right: auto">
          <div
            v-for="temtable in tableData"
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
                              .replace($store.state.html_reg, "")
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
            <div style="height: 1%"></div>
          </div>
        </div>
      </div>
      <div style="clear: both"></div>
      <!-- 清除浮动 -->
      <div class="clear"></div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "allarticle",
  async activated() {
    this.isseetip = true;
    this.istobottom = false;
    this.disabledscroll = false;
    this.scrolltimer = setInterval(() => {
      this.disabledscroll = false;
    }, 600);
    window.addEventListener("scroll", this.addlist);
  },
  deactivated() {
    this.disabledscroll = true;
    this.isseetip = false;
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    window.removeEventListener("scroll", this.addlist);
  },
  destroyed() {
    this.disabledscroll = true;
    this.isseetip = false;
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    window.removeEventListener("scroll", this.addlist);
  },
  async created() {
    this.isseetip = true;
    this.limit = 18;
    this.islock = false;
    this.issearch = false;
    this.disabledscroll = false;
    this.initData();
    await this.getlist();
    this.$nextTick(() => {
      this.totop();
    });
  },
  methods: {
    initData() {
      this.tableData = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.article_list_data);
      }
      this.tableData = tem_list;
    },
    tableRowClassName({ row, rowIndex }) {
      if (rowIndex === 1) {
        return "warning-row";
      } else if (rowIndex === 3) {
        return "success-row";
      }
      return "";
    },

    async keysearch() {
      if (this.scrolllock) {
        return;
      }
      this.lastkey = this.key;
      this.scrolllock = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/allArticleKeySearch",
        portType: {
          process: "8792",
        },
        data: {
          key: this.key,
          article_id: 0,
          limit: this.limit,
          do: "down",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.disabledscroll = false;
        this.scrolllock = false;
      });
      this.tableData = res?.data;
      this.scrolllock = false;
    },
    search() {
      this.initData();
      this.scrolllock = false;
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        this.getlist();
        return;
      }
      this.issearch = true;
      this.keysearch();
    },

    async addlist() {
      if (this.disabledscroll || this.scrolllock) {
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
        this.istobottom = false;
        this.disabledscroll = true;
        this.scrolllock = true;

        const { data: res } = await this.$ajax({
          method: "post",
          url: this.issearch
            ? "/Article/allArticleKeySearch"
            : "/Article/loadAllArticleList",
          portType: {
            process: "8792",
          },
          data: {
            article_id: this.tableData.length > 0 ? this.tableData[0].id : 0,
            limit: this.limit,
            do: "up",
            key: this.key,
          },
        }).catch((t) => {
          this.disabledscroll = false;
          this.scrolllock = false;
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
        if (!res?.data || !res?.data.length) {
          this.scrolllock = false;
          return;
        }
        this.tableData = [...res?.data, ...this.tableData];
        if (this.tableData.length > this.limit * 2) {
          this.tableData = this.tableData.slice(0, this.limit * 2);
          window.scrollTo(0, document.body.clientHeight / 2 - 100.8);
        }
        this.scrolllock = false;
        return;
      }
      //滚动条到底部的条件
      if (!(scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100)) {
        return;
      }
      if (this.istobottom) {
        return;
      }
      this.scrolllock = true;
      this.disabledscroll = true;

      const { data: res } = await this.$ajax({
        method: "post",
        url: this.issearch
          ? "/Article/allArticleKeySearch"
          : "/Article/loadAllArticleList",
        portType: {
          process: "8792",
        },
        data: {
          article_id:
            this.tableData.length > 0
              ? this.tableData[this.tableData.length - 1].id
              : 0,
          limit: this.limit,
          do: "down",
          key: this.key,
        },
      }).catch((t) => {
        this.disabledscroll = false;
        this.scrolllock = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data.length <= 0) {
        this.istobottom = true;
        this.$msg({
          type: "success",
          message: "没有更多了！",
          duration: 1600,
          offset: 80,
        });
        this.scrolllock = false;
        return;
      }

      res?.data.forEach((tem) => {
        this.tableData.push(tem);
      });
      if (this.tableData.length > this.limit * 2) {
        this.tableData = this.tableData.slice(
          this.tableData.length - this.limit * 2,
          this.tableData.length
        );
        window.scrollTo(
          0,
          document.body.scrollHeight / 2 - window.innerHeight + 100.8
        );
      }
      this.scrolllock = false;
    },
    async getlist() {
      if (this.scrolllock) {
        return;
      }
      this.scrolllock = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadAllArticleList",
        portType: {
          process: "8792",
        },
        data: {
          article_id: 0,
          limit: this.limit,
          do: "down",
        },
      }).catch((t) => {
        this.disabledscroll = false;
        this.scrolllock = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.tableData = res?.data;
      this.scrolllock = false;
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
  },
  data() {
    return {
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
      istobottom: false,
      scrolllock: false,
      limit: 18,
      scrolltimer: null,
      disabledscroll: false,
      lastkey: "",
      isseetip: true,
      daymonth: new Date(),
      issearch: false,
      key: "",
      tableData: [],
    };
  },
};
</script>

<style scoped>
.text {
  font-size: 1rem;
  color: deepskyblue;
}

.el-carousel__item h3 {
  color: #475669;
  font-size: 14px;
  opacity: 0.75;
  line-height: 150px;
  margin: 0;
}

.el-carousel__item:nth-child(2n) {
  background-color: #99a9bf;
}

.el-carousel__item:nth-child(2n + 1) {
  background-color: #d3dce6;
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