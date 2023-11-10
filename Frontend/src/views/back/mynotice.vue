<!-- 我的消息管理 -->
<template>
  <div
    @contextmenu.prevent=""
    class="shadow"
    style="
      background-color: rgba(41, 50, 56, 0.28);
      color: azure;
      border-width: 0rem;
      border-color: rgba(41, 50, 56, 0.28)
      height: auto;
      width: 100%;
    "
  >
    <div>
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 0.8rem"></div>
        <div
          style="
            color: aliceblue;
            font-size: 1.66rem;
            text-align: center;
            margin: 1rem 1rem 0rem 1rem;
          "
        >
          我的消息
        </div>
        <div>
          <el-button
            type="text"
            @click="clearall()"
            style="
              color: deeppink;
              font-size: 1.06rem;
              margin: 0rem 0rem 0.6rem 0rem;
              font-weight: bold;
            "
            class="pulse-enter-active"
            >清空消息和通知</el-button
          >
        </div>
        <div
          v-show="total > 0"
          :style="`min-height:${
            $store.state.no_scroll_height * 0.74
          }vh;will-change: transform;`"
        >
          <div v-for="tem in noticeList" :key="tem.index">
            <div @dblclick="toPage(tem)">
              <el-alert
                style="cursor: pointer; margin-top: 0.66rem"
                @close="deletemynotice(tem.id)"
                :title="tem.time"
                type="success"
                effect="dark"
                :description="tem.notice"
                show-icon
              >
              </el-alert>
            </div>
          </div>
        </div>
        <div style="min-height: 46vh" v-show="total == 0">
          <p style="text-align: center; color: aliceblue; font-size: 1.06rem">
            暂无消息
          </p>
        </div>
        <div style="height: 3.4rem"></div>
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
    <div style="height: 3.4rem"></div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";

export default {
  name: "mynotice",
  created() {
    this.page = 1;
    this.limit = 50;
    this.noticeList = [];
    this.getlist();
  },
  activated() {
    this.getlist();
  },
  data() {
    return {
      noticeList: [],
      total: 0,
      page: 1,
      limit: 50,
      id: 0,
    };
  },
  methods: {
    async clearall() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Mynotice/deleteAllMyNotice",
        portType: {
          process: "8793",
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
      this.getlist();
    },
    async deletemynotice(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Mynotice/deleteMyNotice",
        portType: {
          process: "8793",
        },
        data: {
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
      this.getlist();
    },
    toPage(data) {
      if (data.articleid && data.articleid != 0) {
        this.$router.push({
          path: "/onearticle",
          query: {
            path: urlencode(data.articleid, "gbk"),
          },
        });
      } else if (data.questionid && data.questionid != 0) {
        this.$router.push({
          path: "/onequestion",
          query: {
            path: urlencode(data.questionid, "gbk"),
          },
        });
      } else if (data.videoid && data.videoid != 0) {
        this.$router.push({
          path: "/onevideo",
          query: {
            path: urlencode(data.videoid, "gbk"),
          },
        });
      } else if (data.fanuserid && data.fanuserid != 0) {
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(data.fanuserid, "gbk"),
          },
        });
      }
    },
    handleCurrentChange(val) {
      this.page = val;
      this.getlist();
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      this.getlist();
    },

    //获取通知列表
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Mynotice/loadMyNotice",
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
      this.noticeList = res.data;

      if (res.code != 1) {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
  },
};
</script>
<style scoped>
::v-deep .el-table,
::v-deep .el-table__expanded-cell {
  background-color: transparent !important;
}
/* 表格内背景颜色 */
::v-deep .el-table th,
::v-deep .el-table tr,
::v-deep .el-table td {
  background-color: transparent !important;
}
</style>