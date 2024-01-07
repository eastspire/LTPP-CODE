/* 
收藏的博客


 */


<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
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

    <div v-for="temtable in tableData" :key="temtable.index">
      <div>
        <div
          @click="passdata(temtable.id)"
          class="pulse-enter-active shadow ltpp-list-box"
          style="
            border-width: 0rem;
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
              <!-- <el-avatar
                style="
                  height: 6rem;
                  width: 6rem;
                  float: left;
                  margin: 1rem 4rem 0rem 1rem;
                "
                :src="temtable.image"
                alt="空"
              ></el-avatar> -->
              <div class="tagdiv">
                <el-tag effect="dark" type="danger" class="tag"
                  >文章名称</el-tag
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
                  {{ temtable.name.substr(0, 13) }}
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
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "lovearticle",
  async activated() {
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
  created() {
    this.page = 1;
    this.limit = 50;
    this.showone = false;
    this.issearch = false;
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
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/findLoveArticle",
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
      this.tableData = res?.data;

      this.total = res.allnum;

      if (!this.showone) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.showone = true;
      }
    },
    search() {
      this.issearch = false;
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
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

    passdata(id) {
      id &&
        this.$router.push({
          path: "/onearticle",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },

    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadLoveArticleList",
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
      this.tableData = res?.data;

      this.total = res.allnum; //总条数
      if (!this.showone) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.showone = true;
      }
    },
  },
  data() {
    return {
      lastkey: "",
      showone: false,
      issearch: false,
      total: 0,
      limit: 50,
      page: 1,
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