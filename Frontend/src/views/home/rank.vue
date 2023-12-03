<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto; width: 88%"
  >
    <div class="shadow">
      <div style="color: azure; border-width: 0rem; height: auto; width: 100%">
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="tableData"
            style="width: 100%"
          >
            <el-table-column label="排名" width="80" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'排名：' + scope.row.index"
                  placement="right"
                >
                  <span
                    style="font-weight: bold; font-size: 1.06rem"
                    class="my-span"
                    >{{ scope.row.index }}</span
                  >
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="用户名" width="220" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="'用户名：' + scope.row.name"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      cursor: pointer;
                      font-weight: bold;
                      font-size: 1.06rem;
                    "
                    @click="touserpage(scope.row.id)"
                  >
                    {{ scope.row.name.substr(0, 10) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="状态" width="auto" align="center">
              <template slot-scope="scope">
                <el-tag
                  size="small"
                  v-if="scope.row.online == 1"
                  effect="dark"
                  type="danger"
                  style="cursor: pointer; font-weight: bold; font-size: 1.06rem"
                  >在线
                </el-tag>
                <el-tag
                  size="small"
                  v-if="scope.row.online != 1"
                  effect="dark"
                  type="info"
                  style="cursor: pointer; font-weight: bold; font-size: 1.06rem"
                  >离线
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="竞赛积分" width="auto" align="center">
              <template slot-scope="scope">
                <span
                  style="font-weight: bold; font-size: 1.06rem"
                  class="my-span"
                >
                  {{ scope.row.acnum }}
                </span>
              </template>
            </el-table-column>
            <el-table-column label="粉丝数" width="auto" align="center">
              <template slot-scope="scope">
                <span
                  style="font-weight: bold; font-size: 1.06rem"
                  class="my-span"
                >
                  {{ scope.row.fans }}
                </span>
              </template>
            </el-table-column>
            <el-table-column label="最近在线" width="240" align="center">
              <template slot-scope="scope">
                <span
                  style="font-weight: bold; font-size: 1.06rem"
                  class="my-span"
                >
                  {{ scope.row.lastlogin.substr(0, 20) }}
                </span>
              </template>
            </el-table-column>
          </el-table>
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
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "rank",
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  activated() {
    this.isseetip = true;
    if (this.total != 0) {
      this.getlist();
    }
  },
  async created() {
    this.tableData = [];
    this.total = 0;
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.user_rank_list_data);
    }
    this.tableData = tem_list;

    this.issearch = false;
    await this.getlist();
  },
  data() {
    return {
      isseetip: true,
      tableData: [],
      page: 1,
      limitL: 50,
      issearch: false,
      total: 0,
    };
  },
  methods: {
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(26, 26, 26, 0.46) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background = "rgba(41, 50, 56, 0.46) !important";
      }
      if (row.index <= 3) {
        styleRes.color = "red";
        return styleRes;
      } else {
        styleRes.color = "chartreuse";
        return styleRes;
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
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Scorerank/getRankList",
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
      this.total = res.allnum;
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