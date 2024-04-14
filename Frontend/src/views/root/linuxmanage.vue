<!-- 管理全站服务器 -->
<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow ltpp-list-box">
      <div style="border-width: 0rem">
        <div class="search shadow">
          <el-input
            style="font-size: 1rem"
            placeholder="请输入需要搜索的服务器名称/服务器购买用户/服务器端口"
            v-model.lazy="key"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div style="color: azure; height: auto; width: 100%">
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <div style="height: 0.8rem"></div>
          <div>
            <el-table
              :data="linux_list"
              style="width: 100%"
              :cell-style="cellStyle"
              :header-cell-style="{
                color: '#FFFFFF',
                'font-size': '1.06rem',
              }"
            >
              <el-table-column width="340">
                <template slot="header">
                  <span
                    style="text-align: left; margin-left: 1rem"
                    class="my-span"
                    >服务器名称</span
                  >
                </template>
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'服务器名称：' + scope.row.name"
                    placement="right"
                  >
                    <span
                      :class="`my-span ${
                        scope.row.password ? 'el-icon-lock' : 'el-icon-trophy'
                      }`"
                      style="
                        font-weight: bold;
                        font-size: 1.06rem;
                        cursor: pointer;
                        margin-left: 1rem;
                      "
                      @click="tojoinpage(scope.row.id)"
                      >{{ scope.row.name.substr(0, 17) }}</span
                    >
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="购买时间" width="210" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.buy_time }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="购买用户" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #e6a23c;
                    "
                  >
                    {{ scope.row.user_name }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="端口" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.begin_port }}- {{ scope.row.end_port }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="密码" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #e6a23c;
                    "
                  >
                    {{ scope.row.password }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="400" align="center">
                <template slot-scope="scope">
                  <el-button
                    class="pulse-enter-active"
                    v-if="$store.state.root && $store.state.my_name === 'root'"
                    @click="
                      iscreat = false;
                      deleteid(scope.row.name);
                    "
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >删除
                  </el-button>
                  <el-button
                    class="pulse-enter-active"
                    @click="reboot(scope.row.name)"
                    style="
                      margin-left: 3.6rem;
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >重启
                  </el-button>
                  <el-button
                    class="pulse-enter-active"
                    @click="shutdown(scope.row.name)"
                    style="
                      margin-left: 3.6rem;
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >关机
                  </el-button>
                  <el-button
                    class="pulse-enter-active"
                    @click="poweron(scope.row.name)"
                    style="
                      margin-left: 3.6rem;
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >开机
                  </el-button>
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
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode/lib/urlencode";
export default {
  name: "linuxmanage",
  activated() {
    this.isseetip = true;
    if (this.total != 0) {
      this.search();
    }
  },
  async created() {
    this.isseetip = true;
    this.issearch = false; //判断是否搜索，从而进行分页查找
    this.page = 1;
    this.limit = 50;
    await this.getlist();
  },

  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  data() {
    return {
      lastkey: "",
      isseetip: true,
      issearch: false,
      linux_list: [],
      total: 0,
      page: 1,
      limit: 50,
      key: "",
    };
  },
  methods: {
    toadd() {},
    initData() {
      this.linux_list = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.linux_list_data);
      }
      this.linux_list = tem_list;
    },
    // 表体字体颜色设置
    /***
     * row为某一行的除操作外的全部数据
     * column为某一列的属性
     * rowIndex为某一行（从0开始数起）
     * columnIndex为某一列（从0开始数起）
     */
    cellStyle({ row, rowIndex }) {
      let begintime = Date.parse(row.begin);
      let endtime = Date.parse(row.end);
      let now = Date.parse(new Date());
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      // 状态列字体颜色
      if (endtime <= now) {
        /* 服务器结束 */
        styleRes.color = "chartreuse";
        return styleRes;
      } else if (begintime <= now && now <= endtime) {
        /* 服务器进行中 */
        styleRes.color = "red";
        return styleRes;
      } else {
        /* 服务器未开始 */
        styleRes.color = "#409EFF";
        return styleRes;
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
    //删除
    async deleteid(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      this.$confirm("确定删除该服务器吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.delete(name);
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
    async shutdown(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/shutdown",
        portType: {
          process: "8796",
        },
        data: {
          name: name,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async poweron(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/poweron",
        portType: {
          process: "8796",
        },
        data: {
          name: name,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async reboot(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/reboot",
        portType: {
          process: "8796",
        },
        data: {
          name: name,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async delete(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/delete",
        portType: {
          process: "8796",
        },
        data: {
          name: name,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.search();
    },
    //获取列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/getList",
        portType: {
          process: "8796",
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
      this.linux_list = res?.data;
    },
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/searchList",
        portType: {
          process: "8796",
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
      this.linux_list = res?.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
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