<!-- 短句管理 -->
<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select shadow"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="ltpp-list-box">
      <div class="search shadow">
        <el-input
          style="font-size: 1.06rem"
          placeholder="请输入关键字"
          v-model.lazy="key"
          @keyup.enter.native="search()"
          ><el-button slot="append" icon="el-icon-search" @click="search()"
            >搜索</el-button
          ></el-input
        >
      </div>
    </div>
    <div style="color: azure; height: auto; width: 100%">
      <div>
        <div>
          <div>
            <el-button
              type="text"
              @click="
                hitokoto = '';
                from = '';
                isadd = true;
                isupdate = false;
              "
              class="el-icon-plus pulse-enter-active"
              style="
                font-size: 1.06rem;
                font-weight: bold;
                color: chartreuse;
                margin: 1rem 1rem;
              "
              >添加短句</el-button
            >
          </div>
        </div>
        <div>
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="shortsentenceList"
            style="width: 100%"
          >
            <el-table-column label="短句" width="560">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'短句内容：' + scope.row.hitokoto"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #409eff;
                    "
                    type="success"
                  >
                    {{ scope.row.hitokoto.substr(0, 60) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>

            <el-table-column label="短句来源" width="auto" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'短句来源：' + scope.row.from"
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
                    {{ scope.row.from.substr(0, 20) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>

            <el-table-column label="操作" width="240" align="center">
              <template slot-scope="scope">
                <el-button
                  class="pulse-enter-active"
                  @click="
                    id = scope.row.id;
                    deleteid();
                  "
                  style="
                    margin: 0rem 4rem 0rem 0rem;
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: red;
                  "
                  type="text"
                  >删除
                </el-button>
                <el-button
                  class="pulse-enter-active"
                  @click="
                    id = scope.row.id;
                    hitokoto = scope.row.hitokoto;
                    from = scope.row.from;
                    isadd = false;
                    id ? (isupdate = true) : (isupdate = false);
                  "
                  style="
                    margin: 0rem 2rem 0rem 0rem;
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
    <!-- 更新对话框 -->
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      style="text-align: center; font-size: 2rem; font-weight: bold"
      title=""
      :visible.sync="isupdate"
    >
      <div>
        <div style="text-align: left">
          <p
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            短句
          </p>
          <el-input
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 400000000 }"
            style="font-size: 1.06rem"
            placeholder="请输入短句关键字/名称"
            v-model.lazy="hitokoto"
          ></el-input>
          <p
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            短句来源
          </p>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入短句来源"
            v-model.lazy="from"
          ></el-input>
          <div style="height: 1.06rem"></div>
        </div>
        <div style="float: left; text-align: left" v-show="isupdate">
          <el-button
            width="auto"
            style="font-size: 1.06rem; margin: 1.6rem 2.6rem"
            class="pulse-enter-active"
            type="danger"
            @click="
              updateid();
              isadd = false;
              isupdate = false;
            "
            >更新</el-button
          >
        </div>

        <div style="text-align: right">
          <el-button
            type="success"
            width="auto"
            style="margin: 1.6rem 2.6rem; font-size: 1.06rem"
            class="pulse-enter-active"
            @click="
              isadd = false;
              isupdate = false;
            "
            >取消</el-button
          >
        </div>
      </div>
    </el-dialog>
    <!-- 添加对话框 -->
    <el-dialog
      :close-on-click-modal="false"
      :append-to-body="true"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
      style="text-align: center; font-size: 2rem; font-weight: bold"
      title=""
      :visible.sync="isadd"
    >
      <div>
        <div style="text-align: left">
          <p
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            短句
          </p>
          <el-input
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 400000000 }"
            style="font-size: 1.06rem"
            placeholder="请输入短句内容"
            v-model.lazy="hitokoto"
          ></el-input>
          <p
            class="my-span"
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            短句来源
          </p>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入短句来源"
            v-model.lazy="from"
          ></el-input>
          <div style="height: 1.06rem"></div>
        </div>
        <div style="float: left; text-align: left" v-show="isadd">
          <el-button
            width="auto"
            style="font-size: 1.06rem; margin: 1.6rem 2.6rem"
            class="pulse-enter-active"
            type="danger"
            @click="
              addid();
              isupdate = false;
              isadd = false;
            "
            >添加</el-button
          >
        </div>
        <div style="text-align: right">
          <el-button
            type="success"
            width="auto"
            style="margin: 1.6rem 2.6rem; font-size: 1.06rem"
            class="pulse-enter-active"
            @click="
              isadd = false;
              isupdate = false;
            "
            >取消</el-button
          >
        </div>
      </div>
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
      :page-size="blogpagesize"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
    ></el-pagination>
  </div>
</template>

<script>
export default {
  name: "shortsentencemanage",
  async activated() {
    this.isseetip = true;
    this.page = 1;
    this.blogpagesize = 50;
    await this.search();
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
      isupdate: false,
      isadd: false,
      shortsentenceList: [],
      total: 0,
      page: 1,
      blogpagesize: 50,
      key: "",
      id: 0,
      hitokoto: "",
      from: "",
    };
  },
  methods: {
    initData() {
      this.shortsentenceList = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.short_sentence_list);
      }
      this.shortsentenceList = tem_list;
    },
    handleCurrentChange(val) {
      this.page = val;
      this.search();
    },
    handleSizeChange(val) {
      this.page = 1;
      this.blogpagesize = val;
      this.search();
    },
    cellStyle({ rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      return styleRes;
    },
    //获取短句列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Shortsentence/loadAllShortSentence",
        portType: {
          process: "8797",
        },
        data: {
          page: this.page,
          limit: this.blogpagesize,
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
      this.shortsentenceList = res?.data;

      if (res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //删除
    async deleteid() {
      if (!this.id) {
        return;
      }
      this.$confirm("确定删除该短句吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Shortsentence/deleteShortSentence",
            portType: {
              process: "8797",
            },
            data: {
              delete_id: this.id,
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
        url: "/Shortsentence/findShortSentenceList",
        portType: {
          process: "8797",
        },
        data: {
          key: this.key,
          page: this.page,
          limit: this.blogpagesize,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.shortsentenceList = res?.data;
      this.total = res.allnum;

      if (res?.code != 1) {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
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
    async updateid() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Shortsentence/updateShortSentence",
        portType: {
          process: "8797",
        },
        data: {
          tabledata: {
            id: this.id,
            hitokoto: this.hitokoto,
            from: this.from,
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
      this.id = "";
      this.hitokoto = "";
      this.search();
    },
    //添加
    async addid() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Shortsentence/addShortSentence",
        portType: {
          process: "8797",
        },
        data: {
          tabledata: {
            hitokoto: this.hitokoto,
            from: this.from,
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
      this.id = 0;
      this.hitokoto = "";
      this.search();
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