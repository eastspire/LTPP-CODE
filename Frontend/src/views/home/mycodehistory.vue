<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow">
      <div style="color: azure; border-width: 0rem; height: auto; width: 100%">
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <div>
            <el-table
              :cell-style="cellStyle"
              :header-cell-style="{
                color: '#FFFFFF',
                'font-size': '1.06rem',
              }"
              :data="allcodelist"
              style="width: 100%"
              :row-class-name="tableRowClassName"
            >
              <el-table-column label="提交时间" width="240" align="center">
                <template slot-scope="scope">
                  <span
                    style="font-weight: bold; font-size: 1.06rem"
                    class="my-span"
                  >
                    {{ scope.row.time.substr(0, 20) }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="用户" width="220" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'用户名：' + scope.row.user"
                    placement="right"
                  >
                    <span
                      class="my-span"
                      style="
                        font-size: 1.06rem;
                        font-weight: bold;
                        color: #f56c6c;
                        cursor: pointer;
                      "
                      @click="touserpage(scope.row.userid)"
                    >
                      {{ scope.row.user.substr(0, 10) }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="语言" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.language.substr(0, 10) }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="状态" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    v-show="
                      scope.row.status == 'AC' ||
                      scope.row.status == '加载中' ||
                      scope.row.status == '等待中' ||
                      scope.row.status == '运行中'
                    "
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.status.substr(0, 10) }}
                  </span>
                  <span
                    class="my-span"
                    v-show="scope.row.status == '答案错误'"
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #f56c6c;
                    "
                  >
                    {{ scope.row.status.substr(0, 10) }}
                  </span>
                  <span
                    class="my-span"
                    v-show="
                      scope.row.status != 'AC' &&
                      scope.row.status != '加载中' &&
                      scope.row.status != '答案错误' &&
                      scope.row.status != '等待中' &&
                      scope.row.status != '运行中'
                    "
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #e6a23c;
                    "
                  >
                    {{ scope.row.status.substr(0, 10) }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="时间消耗" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #409eff;
                    "
                  >
                    {{ scope.row.usetime }}MS
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="内存消耗" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.usememory }}KB
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="auto" align="center">
                <template slot-scope="scope">
                  <el-button
                    type="text"
                    class="pulse-enter-active"
                    style="
                      color: deeppink;
                      font-weight: bold;
                      font-size: 1.06rem;
                    "
                    @click="
                      changecodelanguage(scope.row.language);
                      lookcode(scope.row.id);
                    "
                    >查看代码
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
            <div>
              <el-dialog
                @contextmenu.prevent.native="isseecode = false"
                title="代码详情"
                :visible.sync="isseecode"
                :width="
                  ($store.state.max_width / $store.state.now_width) * 100 + '%'
                "
                :append-to-body="true"
              >
                <ShowCode
                  v-if="isseecode"
                  :code="code"
                  :language="codelanguage"
                ></ShowCode>
              </el-dialog>
            </div>
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
import ShowCode from "../../components/showcode.vue";

export default {
  name: "mycodehistory",
  components: {
    ShowCode,
  },
  deactivated() {
    this.isseecode = false;
    this.isseetip = false;
  },
  destroyed() {
    this.isseecode = false;
    this.isseetip = false;
  },
  activated() {
    this.isseecode = false;
    this.isseetip = true;
    this.getlist();
  },
  async created() {
    this.allcodelist = [];
    this.isseetip = true;
    this.page = 1;
    this.limit = 50;
    this.total = 0;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.codehistory_data);
    }
    this.allcodelist = tem_list;
    this.isseecode = false;
  },
  data() {
    return {
      isseetip: true,
      usertheme: "monokai",
      allcodelist: [],
      code: "",
      page: 1,
      limit: 50,
      total: 0,
      isseecode: false,
      codelanguage: "cpp",
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
      if (row.status === "AC" || row.status === "加载中") {
        styleRes.color = "chartreuse";
        return styleRes;
      } else {
        styleRes.color = "red";
        return styleRes;
      }
    },
    changecodelanguage(language) {
      if (language == "C") {
        this.codelanguage = "c";
      } else if (language == "C++") {
        this.codelanguage = "cpp";
      } else if (language == "Go") {
        this.codelanguage = "go";
      } else if (language == "Java") {
        this.codelanguage = "java";
      } else if (language == "PHP") {
        this.codelanguage = "php";
      } else if (language == "JavaScript") {
        this.codelanguage = "javascript";
      } else if (language == "Python3") {
        this.codelanguage = "python";
      } else if (language == "Rust") {
        this.codelanguage = "rust";
      } else if (language == "C#") {
        this.codelanguage = "csharp";
      } else if (language == "TypeScript") {
        this.codelanguage = "typescript";
      } else if (language == "Ruby") {
        this.codelanguage = "ruby";
      } else {
        this.codelanguage = "cpp";
      }
    },

    tableRowClassName({ row }) {
      if (row.status === "加载中" || row.status === "AC") {
        return "success-row";
      } else return "warning-row";
    },
    async lookcode(id) {
      this.code = "加载中";
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/lookOneCode",
        portType: {
          process: "8795",
        },
        data: {
          code_id: id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res.code == 1) {
        this.code = res.data.code;
      } else {
        this.code = res.msg;
      }
      this.isseecode = true;
    },
    touserpage(id) {
      id &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: id,
          },
        });
    },

    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/getMyCodeList",
        portType: {
          process: "8795",
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
      this.allcodelist = res.data;
      this.total = res.total;
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