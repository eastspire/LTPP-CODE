<!-- OJ题目管理 -->
<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow ltpp-list-box">
      <div class="ltpp-list-box">
        <div class="search shadow">
          <el-input
            placeholder="请输入需要搜索的题目名称"
            style="font-size: 1.06rem"
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
        <div>
          <div style="text-align: left">
            <el-button
              type="text"
              @click="addid()"
              class="el-icon-plus pulse-enter-active"
              style="
                font-size: 1.06rem;
                color: deeppink;
                font-weight: bold;
                margin-top: 1rem;
                margin-left: 1rem;
              "
            >
              添加题目</el-button
            >
          </div>

          <div :style="`min-height:${$store.state.no_scroll_height * 0.76}vh;`">
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
                    class="my-span"
                    style="text-align: left; margin-left: 1rem"
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
                      @click="updateid(scope.row.id)"
                      >{{ scope.row.problemName.substr(0, 17) }}</span
                    >
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
                        cursor: pointer;
                        font-weight: bold;
                        font-size: 1.06rem;
                        color: #409eff;
                      "
                      @click="
                        key = scope.row.problemLabe;
                        search();
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
                      'AC/ALL：' +
                      scope.row.ACNum +
                      '/' +
                      scope.row.ALLSubmitNum
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
                        cursor: pointer;
                        font-weight: bold;
                        font-size: 1.06rem;
                        color: #e6a23c;
                      "
                      @click="
                        key = scope.row.problemFrom;
                        search();
                      "
                    >
                      {{ scope.row.problemFrom.substr(0, 20) }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="200" align="center">
                <template slot-scope="scope">
                  <el-button
                    class="pulse-enter-active"
                    v-if="
                      $store.state.root &&
                      $store.state.my_name === $SqsGlobal.root_name
                    "
                    @click="deleteid(scope.row.id)"
                    style="font-size: 1.06rem; font-weight: bold; color: red"
                    type="text"
                    >删除
                  </el-button>
                  <el-button
                    class="pulse-enter-active"
                    @click="updateid(scope.row.id)"
                    style="
                      margin-left: 3.6rem;
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >更新
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
import urlencode from "../../../updateCompoents/urlencode";

export default {
  name: "problemmanage",
  async created() {
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
  },
  async activated() {
    this.isseetip = true;
    this.search();
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
      linuxurl: window?.location?.href,
      passparam: {
        id: "",
      },
      problemList: [],
      total: 0,
      page: 1,
      limit: 50,
      key: "",
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
      let acpoint = (row.ACpoint * 100).toFixed(0);

      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }

      if (acpoint <= 30) {
        /* 正确率低于30*/
        styleRes.color = "red";
        return styleRes;
      } else if (acpoint <= 80) {
        /* 正确率低于80大于30 */
        styleRes.color = "#F2F6FC";
        return styleRes;
      } else {
        /* 正确率低于100大于80 */
        styleRes.color = "chartreuse";
        return styleRes;
      }
    },
    handleCurrentChange(val) {
      this.page = val;
      this.search();
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      this.search();
    },
    //获取题目列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/backGetProblemList",
        portType: {
          process: "8794",
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
      this.problemList = res?.data;
    },
    //删除
    async deleteid(id) {
      if (!id || id == this.$SqsGlobal.loading_tips) {
        return;
      }
      this.$confirm("确定删除该题目吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Oj/deleteProblem",
            portType: {
              process: "8794",
            },
            data: {
              delete_id: id,
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.$msg({
                  type: "success",
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: "error",
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              }
              this.search();
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
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/backSearchProblem",
        portType: {
          process: "8794",
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
      this.problemList = res?.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      if (!this.key) {
        this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.keysearch();
    },

    //更新
    updateid(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: "/oneproblemmanage",
          query: {
            path: urlencode(id, "gbk"),
            contestid: urlencode("", "gbk"),
          },
        });
    },
    //添加
    addid() {
      this.$router.push({
        path: "/oneproblemmanage",
        query: {
          path: urlencode("", "gbk"),
          contestid: urlencode("", "gbk"),
        },
      });
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