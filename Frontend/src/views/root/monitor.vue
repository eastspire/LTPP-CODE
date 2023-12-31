<!-- 监控管理 -->
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select shadow"
    v-loading.fullscreen.lock="!loadfinish"
    element-loading-text="拼命加载中"
    element-loading-spinner="el-icon-loading"
    element-loading-background="rgba(0, 0, 0, 0.8)"
  >
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
          placeholder="请输入待查询的方法名"
          v-model.lazy="key"
          @keyup.enter.native="search()"
          ><el-button slot="append" icon="el-icon-search" @click="search()"
            >搜索</el-button
          ></el-input
        >
      </div>
    </div>
    <div
      style="
        color: rgb(255, 255, 255) !important;
        background-color: rgb(30, 30, 30);
        text-align: center;
      "
    >
      <el-date-picker
        @change="
          last_id = '';
          monitorList = [];
          search();
        "
        v-model.lazy="time"
        value-format="timestamp"
        type="datetimerange"
        range-separator="至"
        start-placeholder="开始时间"
        end-placeholder="结束时间"
      >
      </el-date-picker>
    </div>
    <div style="color: azure; height: auto; width: 100%">
      <div style="height: 1rem"></div>
      <div>
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="monitorList"
            style="width: 100%"
          >
            <el-table-column label="名称" width="260">
              <template slot-scope="scope">
                <p
                  @click="lookvideo(scope.row.function)"
                  style="
                    font-weight: bold;
                    font-size: 1.06rem;
                    color: deeppink;
                    cursor: pointer;
                  "
                  type="success"
                >
                  {{ scope.row.function.substr(0, 60) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="路径" width="auto">
              <template slot-scope="scope">
                <p
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.path.substr(0, 60) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="用户" width="88">
              <template slot-scope="scope">
                <p
                  style="
                    font-weight: bold;
                    font-size: 1.06rem;
                    color: #409eff;
                    overflow: hidden;
                    cursor: pointer;
                  "
                  @click="touserpage(scope.row.userid)"
                >
                  {{ scope.row.name }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="权限" width="136">
              <template slot-scope="scope">
                <p
                  style="
                    font-weight: bold;
                    font-size: 1.06rem;
                    color: deepskyblue;
                  "
                >
                  {{
                    scope.row.grade == 3
                      ? "超级管理员"
                      : scope.row.grade == 2
                      ? "管理员"
                      : "用户"
                  }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="用户ID" width="160">
              <template slot-scope="scope">
                <p
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.user_aid }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="时间" width="210">
              <template slot-scope="scope">
                <p style="font-weight: bold; font-size: 1.06rem; color: red">
                  {{ scope.row.time }}
                </p>
              </template>
            </el-table-column>
          </el-table>
        </div>
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
  name: "monitor",
  async activated() {
    this.issearch = false; //判断是否搜索，从而进行分页查找
    this.page = 1;
    this.limit = 50;
    this.monitorList = [];
    if (this.total != 0) {
      if (this.issearch) {
        await this.keysearch();
      } else {
        await this.getlist();
      }
    } else {
      await this.getlist();
    }
  },

  data() {
    return {
      issearch: false,
      loadfinish: false,
      monitorList: [],
      total: 0,
      page: 1,
      limit: 50,
      lastkey: "",
      key: "",
      time: [],
      last_id: "",
    };
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
    cellStyle({ rowIndex }) {
      let styleRes = {
        background: "rgba(26, 26, 26, 0.46) !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background = "rgba(41, 50, 56, 0.46) !important";
      }
      return styleRes;
    },
    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.keysearch();
      } else {
        this.getlist();
      }
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (this.issearch) {
        this.keysearch();
      } else {
        this.getlist();
      }
    },
    //获取监控列表
    async getlist() {
      this.loadfinish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/monitor/getData",
        portType: {
          process: "8797",
        },
        data: {
          id: this.last_id,
          key: this.key,
          time: this.time,
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
        this.loadfinish = true;
        return;
      });
      this.loadfinish = true;
      if (!this.monitorList?.length) {
        this.last_id = res.data?.length ? res.data[0].id : "";
      }
      this.total = res.allnum;
      this.monitorList = res.data;
    },
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      this.loadfinish = false;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/monitor/getData",
        portType: {
          process: "8797",
        },
        data: {
          id: this.last_id,
          key: this.key,
          time: this.time,
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
        this.loadfinish = true;
        return;
      });
      this.loadfinish = true;
      if (!this.monitorList?.length) {
        this.last_id = res.data?.length ? res.data[0].id : "";
      }
      this.monitorList = res.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        this.last_id = "";
        this.getlist();
        return;
      }
      this.monitorList = [];
      if (this.lastkey != this.key) {
        this.last_id = "";
        this.page = 1;
      }
      this.issearch = true;
      this.keysearch();
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
::v-deep .el-table__row > td {
  border: none !important;
}
::v-deep .el-table::before {
  height: 0px !important;
}
::v-deep .el-table__cell,
.is-leaf {
  border: none !important;
}
</style>