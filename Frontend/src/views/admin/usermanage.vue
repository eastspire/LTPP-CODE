/* 
    用户管理

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
      <div
        class="search shadow"
        v-if="$store.state.root && $store.state.my_name === 'root'"
      >
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

    <div
      style="height: 1rem"
      v-if="$store.state.root && $store.state.my_name === 'root'"
    ></div>

    <el-button
      type="text"
      @click="
        addclick = true;
        userdata = [];
        dialogFormVisible = false;
      "
      class="el-icon-plus pulse-enter-active"
      style="font-size: 1.06rem; font-weight: bold; color: chartreuse"
      >添加用户</el-button
    >
    <div style="height: 1rem"></div>
    <div :style="`min-height:${$store.state.no_scroll_height * 0.76}vh;`">
      <div v-if="$store.state.root && $store.state.my_name === 'root'">
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
                  alt=""
                ></el-avatar>
                <div class="tagdiv">
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
                    {{ temtable.name }}
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
                    {{ temtable.name }}
                  </p>
                </div>
                <div class="tagdiv">
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
                      color: #67c23a;
                      font-weight: bold;
                    "
                  >
                    {{ temtable.sex }}
                  </p>
                </div>

                <div class="tagdiv">
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

                <div class="tagdiv">
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
                <div class="tagdiv">
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
                      color: #e6a23c;
                      font-weight: bold;
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
    </div>
    <div style="height: 3.4rem"></div>
    <el-pagination
      background
      v-if="total && $store.state.root && $store.state.my_name === 'root'"
      style="text-align: center"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
      :current-page="page"
      :page-sizes="[1, 2, 6, 10, 20, 50]"
      :page-size="limit"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
    ></el-pagination>
    <div style="height: 3.4rem"></div>
    <!-- 对话框 -->
    <el-dialog
      :title="`更新用户${
        userdata.user_aid ? '【用户ID:' + userdata.user_aid + '】' : ''
      }`"
      :visible.sync="dialogFormVisible"
      class="Mdialog"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
    >
      <el-descriptions class="margin-top" title="更新用户" :column="3" border>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-picture"></i>
            头像
          </template>
          <el-input
            v-model.lazy="userdata.headimage"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            账号
          </template>
          <el-input
            v-model.lazy="userdata.name"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-opportunity"></i>
            密码
          </template>
          <el-input
            v-model.lazy="userdata.password"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-user-solid"></i>
            性别
          </template>
          <el-select
            :popper-append-to-body="false"
            v-model.lazy="userdata.sex"
            placeholder="请选择性别"
            style="font-size: 1.06rem"
          >
            <el-option
              label="男"
              value="男"
              style="font-size: 1.06rem"
            ></el-option>
            <el-option
              label="女"
              value="女"
              style="font-size: 1.06rem"
            ></el-option>
          </el-select>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-comment"></i>
            邮箱
          </template>
          <el-input
            v-model.lazy="userdata.email"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-warning"></i>
            权限
          </template>
          <el-input
            v-model.lazy="userdata.grade"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-order"></i>
            学号
          </template>
          <el-input
            v-model.lazy="userdata.student_number"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-order"></i>
            入学年份
          </template>
          <el-input
            v-model.lazy="userdata.enrollment_year"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-home"></i>
            学校
          </template>
          <el-input
            v-model.lazy="userdata.school"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-home"></i>
            学院
          </template>
          <el-input
            v-model.lazy="userdata.college"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-home"></i>
            专业
          </template>
          <el-input
            v-model.lazy="userdata.subject"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-home"></i>
            班级
          </template>
          <el-input
            v-model.lazy="userdata.class"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            网易云uid
          </template>
          <el-input
            v-model.lazy="userdata.musicuid"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-order"></i>
            网易云喜欢列表id
          </template>
          <el-input
            v-model.lazy="userdata.musiclovelistid"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-ticket"></i>
            余额
          </template>
          <el-input
            v-model.lazy="userdata.money"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
      </el-descriptions>
      <p
        style="
          font-size: 1.06rem;
          text-align: left;
          font-weight: bold;
          margin: 1rem 0rem 0.5rem 0rem;
        "
      >
        个性签名
      </p>
      <el-input
        type="textarea"
        :autosize="{ minRows: 2, maxRows: 400000 }"
        v-model.lazy="userdata.mysay"
        style="font-size: 1.06rem; overflow: hidden"
      ></el-input>
      <div style="height: 1.6rem"></div>
      <div slot="footer" class="dialog-footer">
        <div style="float: left">
          <el-button
            v-if="userdata.name != 'root' && $store.state.root"
            type="danger"
            class="el-icon-delete"
            style="font-size: 1.06rem; margin-left: 2rem; font-weight: bold"
            @click="
              dialogFormVisible = false;
              deleteuser();
            "
            >删除</el-button
          >
        </div>
        <div style="text-align: right">
          <el-button
            v-if="$store.state.root && $store.state.my_name === 'root'"
            type="warning"
            @click="
              dialogFormVisible = false;
              touserpage(userdata.id);
            "
            width="auto"
            class="el-icon-user-solid"
            style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
            >主页</el-button
          >

          <el-button
            v-if="
              userdata.name != 'root' &&
              $store.state.root &&
              $store.state.my_name === 'root'
            "
            type="danger"
            @click="
              dialogFormVisible = false;
              addblack(userdata.id);
            "
            width="auto"
            class="el-icon-warning-outline"
            style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
            >拉黑</el-button
          >

          <el-button
            v-if="
              userdata.name != 'root' &&
              $store.state.root &&
              $store.state.my_name === 'root'
            "
            type="danger"
            @click="
              dialogFormVisible = false;
              unonline(userdata.id);
            "
            width="auto"
            class="el-icon-warning-outline"
            style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
            >下线</el-button
          >
          <el-button
            v-if="$store.state.root && $store.state.my_name === 'root'"
            type="success"
            @click="
              dialogFormVisible = false;
              updateuser();
            "
            width="auto"
            class="el-icon-upload2"
            style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
            >更新</el-button
          >

          <el-button
            type="primary"
            @click="
              dialogFormVisible = false;
              addclick = false;
            "
            width="auto"
            style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
            class="el-icon-s-unfold"
            >返回</el-button
          >
        </div>
      </div>
    </el-dialog>

    <el-dialog
      title="新增用户"
      :visible.sync="addclick"
      class="Mdialog"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
    >
      <el-descriptions class="margin-top" title="新增用户" :column="3" border>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-picture"></i>
            头像
          </template>
          <el-input
            v-model.lazy="headimage"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            账号
          </template>
          <el-input v-model.lazy="name" style="font-size: 1.06rem"></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-warning"></i>
            密码
          </template>
          <el-input
            v-model.lazy="password"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-user-solid"></i>
            性别
          </template>
          <el-select
            :popper-append-to-body="false"
            v-model.lazy="sex"
            placeholder="请选择性别"
            style="font-size: 1.06rem"
          >
            <el-option
              label="男"
              value="男"
              style="font-size: 1.06rem"
            ></el-option>
            <el-option
              label="女"
              value="女"
              style="font-size: 1.06rem"
            ></el-option>
          </el-select>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-comment"></i>
            邮箱
          </template>
          <el-input v-model.lazy="email" style="font-size: 1.06rem"></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-warning"></i>
            权限
          </template>
          <el-input
            v-model.lazy="grade"
            :disabled="
              ($store.state.root && $store.state.my_name === 'root') === true
                ? false
                : true
            "
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
      </el-descriptions>
      <div style="height: 1.6rem"></div>
      <div style="text-align: right">
        <el-button
          type="success"
          @click="
            adduser();
            addclick = false;
            dialogFormVisible = false;
          "
          style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
          width="auto"
          class="el-icon-upload2"
          >添加用户</el-button
        >
        <el-button
          type="primary"
          @click="
            addclick = false;
            dialogFormVisible = false;
          "
          width="auto"
          style="
            font-size: 1.06rem;
            margin-right:: 2rem;
            font-weight: bold;
          "
          class="el-icon-s-unfold"
          >返回</el-button
        >
      </div>
    </el-dialog>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "usermanage",
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
    this.page = 1;
    this.limit = 50;
    this.total = 0;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.user_list_data);
    }
    this.tableData = tem_list;
    this.issearch = false;
    this.showone = false;
  },

  methods: {
    async addblack(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/addBlackIpUser",
        portType: {
          process: "8797",
        },
        data: {
          ip: "0.0.0.0",
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
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async unonline(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/unOnline",
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
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
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
    async adduser() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/addUser",
        portType: {
          process: "8793",
        },
        data: {
          data: {
            headimage: this.headimage,
            name: this.name,
            password: this.password,
            sex: this.sex,
            grade: this.grade,
            email: this.email,
          },
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
        this.page = 1;
        this.limit = 50;
        this.headimage = "";
        this.name = "";
        this.password = "";
        this.sex = "";
        this.grade = "";
        this.email = "";
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }

      this.getlist();
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
      if (!this.$store.state.root) {
        return;
      }
      this.issearch = false;
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
        this.showone = false;
      }
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

    async passdata(id) {
      this.userdata = {};
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

    async getlist() {
      if (!this.$store.state.root) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/loadUserList",
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
    },

    async updateuser() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/User/rootUpdateUser",
        portType: {
          process: "8793",
        },
        data: {
          data: this.userdata,
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

    async deleteuser() {
      this.$confirm("确定删除该用户吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/User/deleteUser",
            portType: {
              process: "8793",
            },
            data: {
              delete_id: this.userdata.id,
            },
          })
            .then((res) => {
              if (res.data.code == 1) {
                this.$msg({
                  type: "success",
                  message: res.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: "error",
                  message: res.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              }
              if (this.issearch) {
                this.keysearch();
              } else {
                this.getlist();
              }
            })
            .catch((t) => {
              this.$msg({
                type: "error",
                message: t,
                duration: 1600,
                offset: 80,
              });
            });
        })
        .catch(() => {
          this.$msg({
            type: "info",
            duration: 1600,
            offset: 80,
            message: "取消删除",
          });
        });
    },
  },

  data() {
    return {
      lastkey: "",
      issearch: false,
      showone: false,
      addclick: false,
      headimage: "",
      name: "",
      password: "",
      sex: "",
      grade: 1,
      email: "",
      dialogTableVisible: false,
      dialogFormVisible: false,
      total: 0,
      limit: 50,
      page: 1,
      key: "",
      tableData: [],
      userdata: {},
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
  box-shadow: 0 0 6px rgba(117, 63, 178, 0.12);
  text-align: center;
  line-height: 40px;
  color: #008bb6;
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
  margin-left: auto;
  margin-right: auto;
  overflow: hidden;
}
</style>
