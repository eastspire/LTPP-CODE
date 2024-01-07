<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow">
      <div>
        <div
          style="
            background-color: rgba(248, 249, 250, 0.2);
            color: azure;
            border-width: 0rem;
            height: auto;
            width: 100%;
          "
        >
          <div class="search shadow">
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入需要搜索的关键字"
              v-model.lazy="key"
              @keyup.enter.native="search()"
            >
              <el-button slot="append" icon="el-icon-search" @click="search()"
                >搜索</el-button
              >
            </el-input>
          </div>
        </div>
        <div style="height: 1.6rem"></div>
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="tableData"
            style="width: 100%"
            :row-class-name="tableRowClassName"
          >
            <el-table-column label="提交时间" width="220">
              <template slot-scope="scope">
                <p style="font-weight: bold; font-size: 1.06rem">
                  {{ scope.row.time.substr(0, 20) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="用户" width="220" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="scope.row.user"
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
            <el-table-column label="语言" width="130" align="center">
              <template slot-scope="scope">
                <p
                  style="
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: #67c23a;
                    cursor: pointer;
                  "
                  @click="
                    key = scope.row.language;
                    search();
                  "
                >
                  {{ scope.row.language.substr(0, 10) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="状态" width="100" align="center">
              <template slot-scope="scope">
                <p
                  v-show="
                    scope.row.status == 'AC' ||
                    scope.row.status == '正常运行' ||
                    scope.row.status == '等待中' ||
                    scope.row.status == '运行中'
                  "
                  @click="
                    key = scope.row.status;
                    search();
                  "
                  style="
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: #67c23a;
                    cursor: pointer;
                  "
                >
                  {{ scope.row.status.substr(0, 10) }}
                </p>
                <p
                  @click="
                    key = scope.row.status;
                    search();
                  "
                  v-show="scope.row.status == '答案错误'"
                  style="
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: #f56c6c;
                    cursor: pointer;
                  "
                >
                  {{ scope.row.status.substr(0, 10) }}
                </p>
                <p
                  @click="
                    key = scope.row.status;
                    search();
                  "
                  v-show="
                    scope.row.status != 'AC' &&
                    scope.row.status != '正常运行' &&
                    scope.row.status != '答案错误' &&
                    scope.row.status != '等待中' &&
                    scope.row.status != '运行中'
                  "
                  style="
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: #e6a23c;
                    cursor: pointer;
                  "
                >
                  {{ scope.row.status.substr(0, 10) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="时间消耗" width="auto" align="center">
              <template slot-scope="scope">
                <p
                  style="font-size: 1.06rem; font-weight: bold; color: #409eff"
                >
                  {{ scope.row.usetime }}MS
                </p>
              </template>
            </el-table-column>
            <el-table-column label="内存消耗" width="auto" align="center">
              <template slot-scope="scope">
                <p
                  style="font-size: 1.06rem; font-weight: bold; color: #67c23a"
                >
                  {{ scope.row.usememory }}KB
                </p>
              </template>
            </el-table-column>

            <el-table-column
              label="操作"
              width="200"
              style="font-size: 1rem"
              align="center"
            >
              <template slot-scope="scope">
                <el-button
                  type="text"
                  class="pulse-enter-active"
                  style="color: deeppink; font-weight: bold; font-size: 1.06rem"
                  @click="
                    changecodelanguage(scope.row.language);
                    lookcode(scope.row.id, scope.row.problemid);
                  "
                  >查看代码
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </div>
        <el-dialog
          @contextmenu.prevent.native="isseecode = false"
          title="代码详情"
          :visible.sync="isseecode"
          :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
          :append-to-body="true"
        >
          <ShowCode
            v-if="isseecode"
            :code="code"
            :language="codelanguage"
          ></ShowCode>
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
        <div style="height: 3.4rem"></div>
      </div>
    </div>
  </div>
</template>
<script>
import ShowCode from "../../components/showcode.vue";
import urlencode from "../../../updateCompoents/urlencode";

export default {
  name: "problemcode",
  components: {
    ShowCode,
  },
  activated() {
    this.isseetip = true;
    if (this.total) {
      this.search();
    } else {
      this.getlist();
    }
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  async created() {
    this.isseetip = true;
    if (
      !(
        this.$route &&
        this.$route.query &&
        this.$route.query.path &&
        this.$route.query.path != undefined &&
        this.$route.query.path != null
      )
    ) {
      this.$router.go(-1);
      return;
    }
    this.problemid = urlencode.decode(this.$route.query.path, "gbk");
    this.page = 1;
    this.limit = 50;
    this.total = 0;
    this.isseecode = false;
  },

  data() {
    return {
      lastkey: "",
      isseetip: true,
      issearch: false,
      problemid: 0,
      usertheme: "monokai",
      tableData: [],
      key: "",
      code: "",
      page: 1,
      limit: 50,
      total: 0,
      isseecode: false,
      codelanguage: "cpp",
    };
  },
  methods: {
    initData() {
      this.tableData = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.codehistory_data);
      }
      this.tableData = tem_list;
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
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(228, 147, 208, 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      if (row.status === "AC" || row.status === "正常运行") {
        styleRes.color = "chartreuse";
        return styleRes;
      } else {
        styleRes.color = "red";
        return styleRes;
      }
    },
    tableRowClassName({ row, rowIndex }) {
      if (row.status === "正常运行" || row.status === "AC") {
        return "success-row";
      } else return "warning-row";
    },

    //搜索
    async keysearch() {
      this.initData();
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/searchOneProblemCodeList",
        portType: {
          process: "8795",
        },
        data: {
          problem_id: this.problemid,
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
    },
    //搜索预处理
    async search() {
      if (this.key == "" || this.key == null || this.key == undefined) {
        this.issearch = false;
        await this.getlist();
        return;
      }
      if (this.lastkey != this.key) {
        this.page = 1;
      }
      this.issearch = true;
      this.keysearch();
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
          problem_id: this.problemid,
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
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/getOneProblemCodelist",
        portType: {
          process: "8795",
        },
        data: {
          problem_id: this.problemid,
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
      this.total = res.total;
      if (res.total == 0) {
        this.$msg({
          type: "success",
          message: "暂时没有该题目的提交记录，即将返回！",
          duration: 1600,
          offset: 80,
        });
        setTimeout(() => {
          this.$router.go(-1);
        }, 1000);
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
  },
};
</script>
<style scoped>
.onelist {
  min-height: 2rem;
  color: rgb(0, 132, 255);
  border-bottom: 1rem;
  border-bottom-color: aqua;
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