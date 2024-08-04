<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto; width: 88%"
  >
    <div class="shadow ltpp-list-box">
      <div style="color: azure; height: auto; width: 100%">
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="problemList"
            style="width: 100%"
          >
            <el-table-column width="360">
              <template slot="header">
                <span
                  style="text-align: left; margin-left: 1rem"
                  class="my-span"
                  >标题</span
                >
              </template>
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'标题：' + scope.row.problemName"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      cursor: pointer;
                      font-weight: bold;
                      font-size: 1.06rem;
                      margin-left: 1rem;
                    "
                    @click="toonepro(scope.row.id)"
                    >{{ scope.row.problemName.substr(0, 17) }}</span
                  >
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="日期" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'日期：' + scope.row.time"
                  placement="right"
                >
                  <span
                    class="my-span"
                    effect="dark"
                    type="warning"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.time }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="标签" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'标签：' + scope.row.problemLabe"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #409eff;
                    "
                  >
                    {{ scope.row.problemLabe.substr(0, 20) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="通过率" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="
                    'AC/ALL：' + scope.row.ACNum + '/' + scope.row.ALLSubmitNum
                  "
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ (scope.row.ACpoint * 100).toFixed(0) }}%
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="题目来源" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'题目来源：' + scope.row.problemFrom"
                  placement="right"
                >
                  <span
                    class="my-span"
                    effect="dark"
                    type="warning"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #e6a23c;
                    "
                  >
                    {{ scope.row.problemFrom.substr(0, 20) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>

            <el-table-column label="操作" width="auto" align="center">
              <template slot-scope="scope">
                <el-button
                  type="text"
                  class="pulse-enter-active my-span"
                  style="color: deeppink; font-size: 1.06rem; font-weight: bold"
                  @click="toonepro(scope.row.id)"
                  >查看题目</el-button
                >
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
import urlencode from '../../../updateCompoents/urlencode';
export default {
  name: 'dayproblem',
  activated() {
    this.isseetip = true;
    this.getlist();
  },
  async created() {
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
    this.total = 0;
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
      isseetip: true,
      problemList: [],
      page: 1,
      limitL: 50,
      total: 0,
    };
  },
  methods: {
    initData() {
      this.problemList = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.oj_problem_list_data);
      }
      this.problemList = tem_list;
    },
    // 表体字体颜色设置
    /***
     * row为某一行的除操作外的全部数据
     * column为某一列的属性
     * rowIndex为某一行（从0开始数起）
     * columnIndex为某一列（从0开始数起）
     */
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: 'rgba(var(--ltpp-light-color), 0.16) !important',
        height: '3.6rem !important',
        color: 'chartreuse',
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          'rgba(var(--ltpp-main-bk-color), 0.06) !important';
      }
      if (row.hassolve == 0) {
        /* 未通过*/
        styleRes.color = 'red';
        return styleRes;
      } else {
        /* 通过*/
        styleRes.color = 'chartreuse';
        return styleRes;
      }
    },
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Dayproblem/getDayproblemList',
        portType: {
          process: '8794',
        },
        data: {
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.problemList = res?.data;
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
    toonepro(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: '/oneproblem',
          query: {
            path: urlencode(id, 'gbk'),
            contest: urlencode('', 'gbk'),
          },
        });
    },
  },
};
</script>

<style scoped>
.oneday {
  background-color: antiquewhite;
}
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
