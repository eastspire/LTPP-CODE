/* 

用户关注

 */

<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
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
    <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
      <div v-for="temtable in tableData" :key="temtable.index">
        <div
          @click="
            passdata(temtable.id);
            dialogFormVisible = true;
          "
          class="pulse-enter-active shadow"
          style="
            background-color: rgba(228, 147, 208, 0.06);
            color: azure;
            border-width: 0rem;
            border-color: rgba(228, 147, 208, 0.06)
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
                alt="空"
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
                <p
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
                </p>
                <p
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
                </p>
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
                <p
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
                </p>
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
                <p
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
                </p>
                <p
                  v-show="temtable.online != 1"
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    width: 4rem;
                    height: 2rem;
                    overflow: hidden;
                    color: #ffffffe6;
                    font-weight: bold;
                  "
                >
                  离线
                </p>
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
                <p
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
                </p>
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
                <p
                  style="
                    float: left;
                    margin: 0.26rem 0.6rem 0rem 0.6rem;
                    height: 2rem;
                    overflow: hidden;
                    color: #ffffffe6;
                    font-weight: bold;
                    color: #e6a23c;
                  "
                >
                  {{ temtable.fans }}
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
    <!-- 对话框 -->
    <el-dialog
      title="确定不再关注该用户吗？"
      :visible.sync="deletesure"
      width="26rem"
      class="Mdialog"
      style="text-align: center"
    >
      <el-button
        type="danger"
        @click="
          deleteuser();
          deletesure = false;
        "
        style="margin: 0.2rem 1rem; font-weight: bold"
        width="auto"
        class="el-icon-check pulse-enter-active"
        >确定</el-button
      >

      <el-button
        type="primary"
        @click="deletesure = false"
        style="margin: 0.2rem 1rem; font-weight: bold"
        class="el-icon-s-unfold pulse-enter-active"
        width="auto"
        >取消</el-button
      >
    </el-dialog>
    <el-dialog
      :visible.sync="dialogFormVisible"
      class="Mdialog"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
    >
      <el-descriptions class="margin-top" title="博主信息" :column="3" border>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            账号
          </template>
          <el-input
            v-model.lazy="name"
            disabled
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>

        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-user-solid"></i>
            性别
          </template>
          <el-input
            v-model.lazy="sex"
            disabled
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-comment"></i>
            邮箱
          </template>
          <el-input
            v-model.lazy="email"
            disabled
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
      </el-descriptions>
      <el-row></el-row>
      <div style="margin: 1.06rem 0"></div>
      <el-row></el-row>

      <div slot="footer" class="dialog-footer">
        <div style="float: left; text-align: left">
          <el-button
            type="danger"
            @click="
              deletesure = true;
              dialogFormVisible = false;
            "
            width="auto"
            style="width: auto; margin: 0.4rem 1rem; font-weight: bold"
            class="el-icon-delete pulse-enter-active"
            >取消关注</el-button
          >
        </div>
        <div style="text-align: right">
          <el-button
            type="success"
            @click="
              dialogFormVisible = false;
              touserpage(followid);
            "
            width="auto"
            style="width: auto; margin: 0.4rem 4rem; font-weight: bold"
            class="el-icon-user-solid pulse-enter-active"
            >主页</el-button
          >
          <el-button
            type="primary"
            @click="dialogFormVisible = false"
            width="auto"
            style="width: auto; margin: 0.4rem 1rem; font-weight: bold"
            class="el-icon-s-unfold pulse-enter-active"
            >返回</el-button
          >
        </div>
      </div>
    </el-dialog>
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
  name: "follow",
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
  async created() {
    this.issearch = false;
    this.showone = false;
    this.page = 1;
    this.limit = 50;
    this.total = 0;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.user_list_data);
    }
    this.tableData = tem_list;
  },

  methods: {
    touserpage(id) {
      id &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
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
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/searchFollow",
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
      this.issearch = false;

      if (this.key == "" || this.key == null || this.key == undefined) {
        this.getlist();
        return;
      } else {
        if (this.lastkey != this.key) {
          this.page = 1;
        }
        this.showone = false;

        this.issearch = true;
        this.keysearch();
      }
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
      this.followid = id;
      this.name = res.data["name"];
      this.sex = res.data["sex"];
      this.email = res.data["email"];
    },

    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/loadFollow",
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
      this.tableData = res.data;
      this.total = res.allnum; //总条数

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

    async deleteuser() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/deleteFollow",
        portType: {
          process: "8793",
        },
        data: {
          delete_id: this.followid,
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
      } else
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      this.getlist();
    },
  },

  data() {
    return {
      lastkey: "",
      showone: false,
      issearch: false,
      followid: "",
      name: "",
      sex: "",
      email: "",
      dialogTableVisible: false,
      dialogFormVisible: false,
      deletesure: false,
      total: 0,
      limit: 50,
      page: 1,
      key: "",

      tableData: [],
    };
  },
};
</script>
