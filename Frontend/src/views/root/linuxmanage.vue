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
            placeholder="请输入需要搜索的服务器名称/购买服务器的用户名/服务器端口"
            v-model.lazy="key"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div
        v-if="
          $store.state.root && $store.state.my_name === $SqsGlobal.root_name
        "
        style="text-align: left"
      >
        <el-button
          type="text"
          @click="is_show_buy_dialog = true"
          class="el-icon-plus pulse-enter-active"
          style="
            font-size: 1.06rem;
            font-weight: bold;
            color: deeppink;
            margin-top: 1rem;
            margin-left: 1rem;
          "
        >
          购买服务器</el-button
        >
      </div>
      <div
        v-if="
          $store.state.root && $store.state.my_name === $SqsGlobal.root_name
        "
        style="height: 1rem"
      ></div>
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
              <el-table-column width="380">
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
                      class="my-span el-icon-lock"
                      style="
                        font-weight: bold;
                        font-size: 1.06rem;
                        cursor: pointer;
                        margin-left: 1rem;
                      "
                      @click="
                        now_linux = scope.row;
                        isseedialog = true;
                      "
                      >{{ scope.row.name }}</span
                    >
                  </el-tooltip>
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
                    {{ scope.row.begin_port }} - {{ scope.row.end_port }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="端口数" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{
                      isNaN(scope.row.end_port - scope.row.begin_port + 1)
                        ? $SqsGlobal.loading_tips
                        : scope.row.end_port - scope.row.begin_port + 1
                    }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column
                label="CPU（核心数）"
                width="auto"
                align="center"
              >
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.cpu }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="内存（GB）" width="auto" align="center">
                <template slot-scope="scope">
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #67c23a;
                    "
                  >
                    {{ scope.row.memory }}
                  </span>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="100" align="center">
                <template slot-scope="scope">
                  <el-button
                    class="pulse-enter-active"
                    @click="
                      now_linux = scope.row;
                      isseedialog = true;
                    "
                    style="
                      font-size: 1.06rem;
                      font-weight: bold;
                      color: deeppink;
                    "
                    type="text"
                    >管理
                  </el-button>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
      <el-dialog
        :close-on-click-modal="false"
        :append-to-body="true"
        :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        title="购买服务器（提交信息服务器创建成功后信息禁止修改）"
        :visible.sync="is_show_buy_dialog"
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
              服务器默认用户密码
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入服务器默认用户密码"
              v-model.lazy="buy_data.password"
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
              CPU（核心数）
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入CPU（核心数）"
              v-model.lazy="buy_data.cpu"
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
              内存大小（GB）
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入内存大小（GB）"
              v-model.lazy="buy_data.memory"
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
              公网端口数（不得低于2个公网端口）
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入公网端口数（不得低于2个公网端口）"
              v-model.lazy="buy_data.port_num"
            ></el-input>
            <div style="height: 1.06rem"></div>
          </div>
          <div style="float: left; text-align: left">
            <el-button
              width="auto"
              style="font-size: 1.06rem; margin: 1.6rem 2.6rem"
              class="pulse-enter-active"
              type="danger"
              @click="
                buyLinux();
                is_show_buy_dialog = false;
              "
              >购买</el-button
            >
          </div>
          <div style="text-align: right">
            <el-button
              type="success"
              width="auto"
              style="margin: 1.6rem 2.6rem; font-size: 1.06rem"
              class="pulse-enter-active"
              @click="is_show_buy_dialog = false"
              >取消</el-button
            >
          </div>
        </div>
      </el-dialog>
      <el-dialog
        :close-on-click-modal="false"
        :append-to-body="true"
        :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        title="服务器管理"
        :visible.sync="isseedialog"
      >
        <div
          style="
            font-size: 1rem;
            text-align: left;
            font-weight: bold;
            margin: 0rem 0rem 1rem 1rem;
          "
        >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器名称： {{ now_linux.name }}
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器购买用户：{{ now_linux.user_name }}
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器端口：{{ now_linux.begin_port }} - {{ now_linux.end_port }}
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器端口数：{{
              isNaN(now_linux.end_port - now_linux.begin_port + 1)
                ? $SqsGlobal.loading_tips
                : now_linux.end_port - now_linux.begin_port + 1
            }}
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器CPU核心数：{{ now_linux.cpu }} 核
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器内存：{{ now_linux.memory }} GB
          </p>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 0.5rem 0rem;
            "
          >
            服务器购买时间：{{ now_linux.buy_time }}
          </p>
          <div style="display: flex; justify-content: center; flex-wrap: wrap">
            <el-button
              class="pulse-enter-active"
              @click="copy(now_linux.password)"
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="success"
              >密码
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                poweron(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="success"
              >开机
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                shutdown(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >关机
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                reboot(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >重启
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                isCreatImage(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >快照
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                isBackLastImage(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >回滚
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                isResetImage(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >重置
            </el-button>
            <el-button
              class="pulse-enter-active"
              @click="
                deleteid(now_linux.name);
                isseedialog = false;
              "
              style="
                font-size: 1.06rem;
                font-weight: bold;
                margin: 1rem 1rem 0rem 1rem;
              "
              type="danger"
              >删除
            </el-button>
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
        :page-size="limit"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
      ></el-pagination>
      <div style="height: 3.4rem"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: "linuxmanage",
  activated() {
    this.is_show_buy_dialog = false;
    this.isseedialog = false;
    this.now_linux = {};
    this.buy_data = {};
    this.isseetip = true;
    this.search();
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
      is_show_buy_dialog: false,
      buy_data: {},
      now_linux: {},
      isseedialog: false,
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
    // 购买服务器
    async buyLinux() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/buyLinux",
        portType: {
          process: "8796",
        },
        data: this.buy_data,
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
        this.buy_data = {};
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    // 制作镜像
    async creatImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/creatImage",
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
    // 制作镜像
    async isCreatImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      this.$confirm("确定制作快照吗（此操作会覆盖旧的快照）？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.creatImage(name);
        })
        .catch(() => {
          this.$msg({
            type: "info",
            duration: 1600,
            offset: 80,
            message: "取消制作快照",
          });
        });
    },
    // 回滚镜像
    async backLastImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/backLastImage",
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
    // 回滚镜像
    async isBackLastImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      this.$confirm(
        "确定回滚到最新的快照吗（此操作会覆盖当前数据）？",
        "提示",
        {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          type: "warning",
        }
      )
        .then(() => {
          this.backLastImage(name);
        })
        .catch(() => {
          this.$msg({
            type: "info",
            duration: 1600,
            offset: 80,
            message: "取消回滚快照",
          });
        });
    },
    // 重置镜像
    async resetImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Linux/resetImage",
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
    // 重置镜像
    async isResetImage(name) {
      if (!name || name == this.$SqsGlobal.loading_tips) {
        return;
      }
      this.$confirm("确定重置快照吗（此操作会恢复出厂设置）？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.resetImage(name);
        })
        .catch(() => {
          this.$msg({
            type: "info",
            duration: 1600,
            offset: 80,
            message: "取消重置快照",
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