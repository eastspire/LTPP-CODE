<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow">
      <div style="color: azure; height: auto; width: 100%">
        <div>
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
              <el-table-column label="提交时间" width="240">
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
              <el-table-column
                v-if="$store.state.root && $store.state.my_name === 'root'"
                label="操作"
                width="auto"
                align="center"
              >
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
    </div>
  </div>
</template>

<script>
import ShowCode from "../../components/showcode.vue";

export default {
  name: "allcodehistory",
  components: {
    ShowCode,
  },
  activated() {
    this.isseetip = true;
    this.istobottom = false;
    this.disabledscroll = false;
    this.allcodelist = [];
    this.getlist();
    this.scrolltimer = setInterval(() => {
      this.disabledscroll = false;
    }, 600);
    window.addEventListener("scroll", this.addlist);
  },
  async created() {
    this.isseecode = false;
    this.isseetip = true;
    this.limit = 50;
  },
  mounted() {
    // 切换页面时滚动条自动滚动到顶部
    this.totop();
  },
  deactivated() {
    this.isseecode = false;
    this.isseetip = false;
    this.allcodelist = [];
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    this.disabledscroll = true;
    window.removeEventListener("scroll", this.addlist);
  },
  destroyed() {
    this.isseetip = false;
    this.allcodelist = [];
    clearInterval(this.scrolltimer);
    this.scrolltimer = null;
    this.disabledscroll = true;
    window.removeEventListener("scroll", this.addlist);
  },
  data() {
    return {
      limit: 50,
      codelanguage: "cpp",
      istobottom: false,
      disabledscroll: false,
      scrolltimer: null,
      isseetip: true,
      allcodelist: [],
      code: "",
      isseecode: false,
    };
  },
  methods: {
    initData() {
      this.allcodelist = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.codehistory_data);
      }
      this.allcodelist = tem_list;
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
      if (res?.code == 1) {
        this.code = res?.data.code;
      } else {
        this.code = res?.msg;
      }
      this.isseecode = true;
    },
    async addlist() {
      if (this.disabledscroll) {
        return;
      }
      //加载更多
      let scrollTop =
        document.documentElement.scrollTop || document.body.scrollTop;
      //变量windowHeight是可视区的高度
      let windowHeight =
        document.documentElement.clientHeight || document.body.clientHeight;
      //变量scrollHeight是滚动条的总高度
      let scrollHeight =
        document.documentElement.scrollHeight || document.body.scrollHeight;
      if (scrollTop === 0) {
        this.disabledscroll = true;
        this.istobottom = false;

        const { data: res } = await this.$ajax({
          method: "post",
          url: "/Codehistory/getAllCodeList",
          portType: {
            process: "8795",
          },
          data: {
            code_id: this.allcodelist.length > 0 ? this.allcodelist[0].id : 0,
            limit: this.limit,
            do: "up",
          },
        }).catch((t) => {
          this.disabledscroll = false;
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
        if (!res?.data || !res?.data.length) {
          this.disabledscroll = false;
          return;
        }
        this.allcodelist = [...res?.data, ...this.allcodelist];
        if (this.allcodelist.length > this.limit * 2) {
          this.allcodelist = this.allcodelist.slice(0, this.limit * 2);
          window.scrollTo(0, document.body.clientHeight / 2 - 140);
        }
        this.disabledscroll = false;
        return;
      }
      //滚动条到底部的条件
      if (!(scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100)) {
        return;
      }
      if (this.istobottom) {
        return;
      }
      this.disabledscroll = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/getAllCodeList",
        portType: {
          process: "8795",
        },
        data: {
          code_id:
            this.allcodelist.length > 0
              ? this.allcodelist[this.allcodelist.length - 1].id
              : 0,
          limit: this.limit,
          do: "down",
        },
      }).catch((t) => {
        this.disabledscroll = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.data.length <= 0) {
        this.istobottom = true;
        this.$msg({
          type: "success",
          message: "没有更多了！",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      res?.data.forEach((tem) => {
        this.allcodelist.push(tem);
      });
      if (this.allcodelist.length > this.limit * 2) {
        this.allcodelist = this.allcodelist.slice(
          this.allcodelist.length - this.limit * 2,
          this.allcodelist.length
        );
        window.scrollTo(
          0,
          document.body.scrollHeight / 2 - window.innerHeight + 140
        );
      }
      this.disabledscroll = false;
    },
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      if (row.status === "AC" || row.status === "加载中") {
        styleRes.color = "chartreuse";
        return styleRes;
      } else {
        styleRes.color = "red";
        return styleRes;
      }
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
    tableRowClassName({ row, rowIndex }) {
      if (row.status === "加载中" || row.status === "AC") {
        return "success-row";
      } else return "warning-row";
    },

    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Codehistory/getAllCodeList",
        portType: {
          process: "8795",
        },
        data: {
          code_id: "",
          limit: this.limit,
          do: "down",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });

      this.allcodelist = res?.data;
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