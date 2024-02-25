/* 
    用户博客管理
 */
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select"
  >
    <div class="ltpp-list-box">
      <div class="search shadow">
        <el-input
          style="font-size: 1.06rem"
          placeholder="请输入内容"
          v-model.lazy="key"
          @keyup.enter.native="search()"
        >
          <el-button slot="append" icon="el-icon-search" @click="search()"
            >搜索</el-button
          >
        </el-input>
      </div>
    </div>
    <div style="height: 2rem"></div>
    <div style="will-change: transform">
      <div v-for="temtable in tableData" :key="temtable.index">
        <div
          @click="passid(temtable.id)"
          class="pulse-enter-active ltpp-list-box"
          style="border-width: 0rem; height: 8rem; overflow: hidden"
        >
          <div>
            <div
              style="
                float: left;
                height: 8rem;
                overflow: hidden;
                white-space: nowrap;
              "
            >
              <div class="tagdiv">
                <el-tag effect="dark" type="danger" class="tag"
                  >文章名称</el-tag
                >
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 14rem;
                    height: 2rem;
                    color: deeppink;
                    font-weight: bold;
                    overflow: hidden;
                  "
                >
                  {{ temtable.name }}
                </p>
              </div>

              <div class="tagdiv">
                <el-tag effect="dark" type="success" class="tag">发布者</el-tag>
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 10rem;
                    height: 2rem;
                    color: #67c23a;
                    font-weight: bold;
                  "
                >
                  {{ temtable.writer.substr(0, 10) }}
                </p>
              </div>

              <div class="tagdiv">
                <el-tag effect="dark" type="danger" class="tag"
                  >发布时间</el-tag
                >
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 12rem;
                    height: 2rem;
                    color: deeppink;
                    font-weight: bold;
                  "
                >
                  {{ temtable.releasetime }}
                </p>
              </div>

              <div class="tagdiv">
                <el-tag effect="dark" type="warning" class="tag">点赞数</el-tag>
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 4rem;
                    height: 2rem;
                    color: #e6a23c;
                    font-weight: bold;
                  "
                >
                  {{ temtable.fabulous }}
                </p>
              </div>
              <div class="tagdiv">
                <el-tag effect="dark" type="warning" class="tag">收藏数</el-tag>
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    height: 2rem;
                    color: var(--ltpp-box-text-color);
                  "
                >
                  {{ temtable.collection }}
                </p>
              </div>
            </div>
          </div>
          <!-- 清除浮动 -->
          <div class="clear"></div>
        </div>
        <div style="height: 2rem"></div>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "userarticle",
  async activated() {
    this.disabledscroll = false;
    this.istobottom = false;
    this.scrolllock = false;
    this.limit = 18;
    if (this.total != 0) {
      if (this.issearch) {
        await this.search();
      } else {
        await this.getlist();
      }
    } else {
      await this.getlist();
    }
    this.scrolltimer = setInterval(() => {
      this.disabledscroll = false;
    }, 600);

    window.addEventListener("scroll", this.addlist);
  },
  mounted() {
    // 切换页面时滚动条自动滚动到顶部
    this.totop();
  },
  deactivated() {
    this.tableData = [];
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    window.removeEventListener("scroll", this.addlist);
  },
  destroyed() {
    this.tableData = [];
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    window.removeEventListener("scroll", this.addlist);
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
      const article_id =
        this.tableData.length > 0
          ? this.tableData[this.tableData.length - 1].id
          : 0;
      const { data: res } = await this.$ajax({
        method: "post",
        url: this.issearch
          ? "/Article/allArticleKeySearch"
          : "/Article/loadAllArticleList",
        portType: {
          process: "8792",
        },
        data: {
          article_id: article_id,
          limit: this.limit,
          do: "down",
          key: this.key,
        },
      }).catch((t) => {
        this.loadfinish = true;
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
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadAllArticleList",
        portType: {
          process: "8792",
        },
        data: {
          article_id: "",
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
      });
      this.tableData = res?.data;
      this.scrolllock = false;
    },

    passid(id) {
      //加载修改博客
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/updateonearticle",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },

    async keysearch() {
      if (this.scrolllock) return;
      this.lastkey = this.key;
      this.scrolllock = true;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/allArticleKeySearch",
        portType: {
          process: "8792",
        },
        data: {
          key: this.key,
          article_id: "",
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
      });
      this.tableData = res?.data;
      this.scrolllock = false;
    },

    search() {
      this.scrolllock = false;
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        this.initData();
        this.getlist();
        return;
      }
      this.issearch = true;
      this.keysearch();
    },
  },
  data() {
    return {
      istobottom: false,
      scrolllock: false,
      limit: 18,
      scrolltimer: null,
      disabledscroll: false,
      lastkey: "",
      issearch: false,
      articlename: "",
      writer: "",
      image: "",
      article: "",
      articleid: "",
      fabulous: "",
      collection: "",
      releasetime: "",
      lastchangetime: "",
      key: "",
      tableData: [],
    };
  },
};
</script>

<style scoped>
.tag {
  float: left;
  font-size: 1.06rem;
  font-weight: bold;
  overflow: hidden;
}
.tagdiv {
  float: left;
  margin-top: 3rem;
  margin-left: 2rem;
  margin-right: auto;
  overflow: hidden;
}
</style>
