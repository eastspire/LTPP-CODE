/* 
  个人文章管理

 */

<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div>
      <div
        style="
          background-color: rgba(248, 249, 250, 0.2);
          color: azure;
          border-width: 0rem;
          border-color: rgba(248, 249, 250, 0.2);
          height: auto;
          width: 100%;
        "
      >
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
      <el-row :gutter="12">
        <el-col :span="24">
          <div v-for="temtable in tableData" :key="temtable.index">
            <div
              @click="toload(temtable.id)"
              class="pulse-enter-active shadow"
              style="
                background-color: rgba(41, 50, 56, 0.28);
                color: azure;
                border-width: 0rem;
                border-color: rgba(41, 50, 56, 0.28);
                height: 8rem;
                overflow: hidden;
                width: 100%;
              "
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
                    <el-tag effect="dark" type="success" class="tag"
                      >发布者</el-tag
                    >
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
                    <el-tag effect="dark" type="warning" class="tag"
                      >点赞数</el-tag
                    >
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
                    <el-tag effect="dark" type="warning" class="tag"
                      >收藏数</el-tag
                    >
                    <p
                      style="
                        float: left;
                        margin: 0.26rem 0.6rem 0rem 0.6rem;
                        height: 2rem;
                        color: #ffffffe6;
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
          <div style="height: 3.4rem"></div>
          <el-pagination
            background
            v-show="total"
            style="text-align: center"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :current-page="page"
            :page-sizes="[1, 2, 6, 10, 20, 50]"
            :page-size="limit"
            layout="total, sizes, prev, pager, next, jumper"
            :total="total"
          ></el-pagination>
        </el-col>
      </el-row>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";

export default {
  name: "myarticlemanage",
  async activated() {
    this.isseetip = true;
    if (this.total != 0) {
      if (this.issearch) {
        await this.search();
      } else {
        await this.getlist();
      }
    } else {
      await this.getlist();
    }
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  async created() {
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
    this.showone = false;
    this.issearch = false;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.article_list_data);
    }
    this.tableData = tem_list;
  },
  methods: {
    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.search();
      } else {
        this.getlist();
      }
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (this.issearch) {
        this.search();
      } else {
        this.getlist();
      }
    },

    //获取用户文章列表
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadMyArticleList",
        portType: {
          process: "8792",
        },
        data: {
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });

      this.total = res.allnum;
      this.tableData = res.data;
    },

    //加载文章
    toload(id) {
      id &&
        this.$router.push({
          path: "/onearticle",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },

    //查找
    async keysearch() {
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/myArticleKeySearch",
        portType: {
          process: "8792",
        },
        data: {
          key: this.key,
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.tableData = res.data;
      this.total = res.allnum;

      if (!this.showone) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        this.showone = true;
      }
    },
    search() {
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = true;
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.showone = false;
      this.issearch = true;
      this.keysearch();
    },
  },

  data() {
    return {
      lastkey: "",
      isseetip: true,
      ispublic: 1,
      name: "",
      issearch: false,
      showone: false,
      writer: "",
      article: "",
      articleid: "",
      fabulous: "",
      collection: "",
      image: "",
      releasetime: "",
      lastchangetime: "",
      total: 0,
      limit: 50,
      page: 1,
      key: "",
      //文章数据
      tableData: [],
    };
  },
};
</script>


<style scoped>
.up {
  border-radius: 1rem;
  height: 100%;
  width: 100%;
  background-color: #cecfd1;
  box-shadow: 0 0 6px rgba(0, 0, 0, 0.12);
  text-align: center;
  line-height: 40px;
  color: #008bb6;
}
.CardHeader {
  text-align: center;
  background-color: rgb(188, 199, 199);
}
.el-table .warning-row {
  background: oldlace;
}

.el-table .success-row {
  background: #f0f9eb;
}
/* .Mdialog {
} */
.dialog-footer {
  text-align: center;
}

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
