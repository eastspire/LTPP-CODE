<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div class="ltpp-list-box">
      <div class="search shadow">
        <el-input
          style="font-size: 1.06rem"
          placeholder="请输入需要搜索的用户名"
          @keyup.enter.native="
            showone = false;
            searchuser();
          "
          v-model.lazy="key"
          class="input-with-select"
        >
          <el-button
            slot="append"
            icon="el-icon-search"
            @click="
              showone = false;
              searchuser();
            "
          ></el-button>
        </el-input>
      </div>
    </div>

    <div style="height: 2rem"></div>
    <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
      <div v-for="temtable in tableData" :key="temtable.index">
        <div
          class="pulse-enter-active shadow ltpp-list-box"
          @click="touserpage(temtable.id)"
          style="border-width: 0rem; height: 8rem; overflow: hidden"
        >
          <div>
            <div
              style="
                float: left;
                height: 8rem;
                white-space: nowrap;
                overflow: hidden;
              "
            >
              <el-avatar
                style="
                  height: 6rem;
                  width: 6rem;
                  float: left;
                  margin: 1rem 4rem 0rem 1rem;
                "
                :src="temtable.headimage"
                alt=""
              ></el-avatar>
              <div
                style="
                  float: left;
                  margin-top: 3rem;
                  margin-left: auto;
                  margin-right: auto;
                "
              >
                <el-tag
                  effect="dark"
                  style="float: left; font-size: 1.06rem; font-weight: bold"
                  >用户名/账号</el-tag
                >
                <span
                  class="my-span"
                  v-show="temtable.online == 1"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 11rem;
                    height: 2rem;
                    overflow: hidden;
                    color: deeppink;
                    font-weight: bold;
                  "
                >
                  {{ temtable.name.substr(0, 10) }}
                </span>
                <span
                  class="my-span"
                  v-show="temtable.online != 1"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 11rem;
                    height: 2rem;
                    overflow: hidden;
                    color: #67c23a;
                    font-weight: bold;
                  "
                >
                  {{ temtable.name.substr(0, 10) }}
                </span>
              </div>

              <div
                style="
                  float: left;
                  margin-top: 3rem;
                  margin-left: 2rem;
                  margin-left: auto;
                  margin-right: auto;
                "
              >
                <el-tag
                  effect="dark"
                  type="success"
                  style="float: left; font-size: 1.06rem; font-weight: bold"
                  >性别</el-tag
                >
                <span
                  class="my-span"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 3.6rem;
                    height: 2rem;
                    overflow: hidden;
                    font-weight: bold;
                    color: #67c23a;
                  "
                >
                  {{ temtable.sex.substr(0, 20) }}
                </span>
              </div>

              <div
                style="
                  float: left;
                  margin-top: 3rem;
                  margin-left: auto;
                  margin-right: auto;
                "
              >
                <el-tag
                  effect="dark"
                  style="float: left; font-size: 1.06rem; font-weight: bold"
                  >状态</el-tag
                >
                <span
                  class="my-span"
                  v-show="temtable.online == 1"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 4rem;
                    height: 2rem;
                    overflow: hidden;
                    color: deeppink;
                    font-weight: bold;
                  "
                >
                  在线
                </span>
                <span
                  class="my-span"
                  v-show="temtable.online != 1"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 4rem;
                    height: 2rem;
                    overflow: hidden;
                    color: var(--ltpp-box-text-color);
                    font-weight: bold;
                  "
                >
                  离线
                </span>
              </div>

              <div
                style="
                  float: left;
                  margin-top: 3rem;
                  margin-left: auto;
                  margin-right: auto;
                "
              >
                <el-tag
                  effect="dark"
                  type="success"
                  style="float: left; font-size: 1.06rem; font-weight: bold"
                  >竞赛积分</el-tag
                >
                <span
                  class="my-span"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 8rem;
                    height: 2rem;
                    overflow: hidden;
                    color: #67c23a;
                    font-weight: bold;
                  "
                >
                  {{ temtable.acnum }}
                </span>
              </div>
              <div
                style="
                  float: left;
                  margin-top: 3rem;
                  margin-left: auto;
                  margin-right: auto;
                "
              >
                <el-tag
                  effect="dark"
                  type="warning"
                  style="float: left; font-size: 1.06rem; font-weight: bold"
                  >粉丝数</el-tag
                >
                <span
                  class="my-span"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    height: 2rem;
                    overflow: hidden;
                    color: var(--ltpp-box-text-color);
                    font-weight: bold;
                    color: #e6a23c;
                  "
                >
                  {{ temtable.fans }}
                </span>
              </div>
            </div>
          </div>
          <!-- 清除浮动 -->
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
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

    <div style="height: 3rem"></div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "user",
  activated() {
    if (this.total != 0) {
      this.searchuser();
    }
  },
  async created() {
    this.page = 1;
    this.limit = 50;
    this.issearch = true;
    this.showone = false;
    await this.looklist();
  },
  data() {
    return {
      lastkey: "",
      showone: false,
      key: "",
      issearch: false,
      tableData: [],
      userdata: [],
      allimage: [],
      page: 1,
      limit: 50,
      total: 0,
      dialogFormVisible: false,
    };
  },
  methods: {
    initData() {
      this.tableData = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.user_list_data);
      }
      this.tableData = tem_list;
    },
    async looklist() {
      this.initData();
      const { data: res } = await this.$ajax({
        url: "/User/userList",
        method: "post",
        portType: {
          process: "8793",
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
      if (res.allnum == 0) {
        this.issearch = false;
      }
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

    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.searchuser();
      } else {
        this.looklist();
      }
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (this.issearch) {
        this.searchuser();
      } else {
        this.looklist();
      }
    },
    async searchuser() {
      this.initData();
      if (!this.key) {
        this.issearch = false;
        this.looklist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.issearch = true;
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/findUser",
        portType: {
          process: "8793",
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
      this.total = res.allnum;
      this.tableData = res.data;
      if (res.allnum == 0) {
        this.issearch = false;
      }
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

    async passdata(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/lookUserData",
        portType: {
          process: "8793",
        },
        data: {
          user_id: id,
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
    },

    async followuser() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/addFollow",
        portType: {
          process: "8793",
        },
        data: {
          follow_id: this.userdata.id,
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
        this.passdata(this.userdata.id);
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
    },
    touserpage(id) {
      id &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    async loadimage() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Image/getImage",
        portType: {
          process: "8793",
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
      this.allimage = res.image;
      this.total = res.allnum;
    },
  },
};
</script>

<style scoped>
p {
  font-size: 1.06rem;
}
</style>