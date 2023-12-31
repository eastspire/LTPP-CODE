<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow">
      <div
        class="shadow"
        style="
          background-color: rgba(248, 249, 250, 0.2);
          color: azure;
          border-width: 0rem;
          border-color: rgba(41, 50, 56, 0.06)
          height: auto;
          width: 100%;
        "
      >
        <div class="search shadow">
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入需要搜索的商品名称"
            v-model.lazy="key"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div style="height: 1rem; clear: both"></div>
      <el-button
        type="text"
        @click="
          goods_data = [];
          see_add_dialog = true;
        "
        class="el-icon-plus pulse-enter-active"
        style="
          font-size: 1.06rem;
          font-weight: bold;
          color: chartreuse;
          float: left;
          margin-left: 0.6rem;
        "
        >添加商品</el-button
      >
      <el-button
        type="text"
        @click="see_more_add_dialog = true"
        class="el-icon-plus pulse-enter-active"
        style="
          font-size: 1.06rem;
          font-weight: bold;
          color: chartreuse;
          float: right;
          margin-right: 0.6rem;
        "
        >批量添加</el-button
      >

      <div
        style="color: azure; height: auto; width: 100%"
        :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`"
      >
        <div>
          <div>
            <el-table
              :cell-style="cellStyle"
              :header-cell-style="{
                color: '#FFFFFF',
                'font-size': '1.06rem',
              }"
              :data="goodsList"
              style="width: 100%"
            >
              <el-table-column width="360">
                <template slot="header">
                  <span
                    style="text-align: left; margin-left: 1rem"
                    class="my-span"
                    >名称</span
                  >
                </template>
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'名称：' + scope.row.name"
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
                      @click="lookOne(scope.row.id)"
                      >{{ scope.row.name.substr(0, 17) }}</span
                    >
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="价格(学虫币)" width="160" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'价格：' + scope.row.money"
                    placement="right"
                  >
                    <span
                      class="my-span"
                      style="
                        font-weight: bold;
                        font-size: 1.06rem;
                        color: deeppink;
                      "
                    >
                      {{ scope.row.money }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="类型" width="auto" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'类型：' + scope.row.type"
                    placement="right"
                  >
                    <span
                      class="my-span"
                      style="
                        font-weight: bold;
                        font-size: 1.06rem;
                        color: #20a53a;
                      "
                    >
                      {{ scope.row.type }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="文件大小" width="160" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'文件大小：' + scope.row.size"
                    placement="right"
                  >
                    <span
                      class="my-span"
                      style="
                        font-weight: bold;
                        font-size: 1.06rem;
                        color: #f7e065;
                      "
                    >
                      {{ scope.row.size }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="下载次数" width="140" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'下载次数：' + scope.row.times"
                    placement="right"
                  >
                    <span
                      class="my-span"
                      style="font-weight: bold; font-size: 1.06rem; color: red"
                    >
                      {{
                        !isNaN(parseFloat(scope.row.times)) &&
                        isFinite(scope.row.times)
                          ? scope.row.times + "次"
                          : scope.row.times
                      }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="更新时间" width="210" align="center">
                <template slot-scope="scope">
                  <el-tooltip
                    class="item"
                    effect="dark"
                    :content="'更新时间：' + scope.row.time"
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
                      {{ scope.row.time }}
                    </span>
                  </el-tooltip>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="100" align="center">
                <template slot-scope="scope">
                  <el-button
                    class="pulse-enter-active"
                    type="text"
                    style="
                      color: deeppink;
                      font-size: 1.06rem;
                      font-weight: bold;
                    "
                    @click="lookOne(scope.row.id)"
                    >查看详情</el-button
                  >
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

    <el-dialog
      title="新增商品"
      :visible.sync="see_add_dialog"
      class="Mdialog"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
    >
      <el-descriptions class="margin-top" title="新增商品" :column="1" border>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-picture"></i>
            商品名称
          </template>
          <el-input
            v-model.lazy="goods_data.name"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            价格（学虫币）
          </template>
          <el-input
            v-model.lazy="goods_data.money"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            文件大小
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.size"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            下载次数
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.times"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            更新时间
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.time"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            商品简介
          </template>
          <el-input
            v-model.lazy="goods_data.blurb"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
      </el-descriptions>
      <div style="height: 1rem"></div>
      <el-upload
        v-show="goods_data.id"
        class="upload-demo"
        style="width: 100%;margin-left=auto;margin-right:auto;text-align:center"
        :headers="head"
        drag
        ref="upload_add"
        :auto-upload="true"
        :data="passparam"
        :action="save_file_url"
        :on-success="upSuccess"
        multiple
      >
        <i class="el-icon-upload"></i>
        <div class="el-upload__text" style="font-size: 1.06rem">
          将文件拖到此处，或<em>点击上传</em>
        </div>
      </el-upload>
      <div style="height: 1rem"></div>
      <div style="text-align: right">
        <el-button
          type="success"
          size="small"
          @click="addGoods()"
          style="
            font-size: 1.06rem;
            margin-right: 2rem;
            font-weight: bold;
            background-color: deeppink;
          "
          width="auto"
          class="el-icon-upload2"
          >添加</el-button
        >
        <el-button
          type="primary"
          size="small"
          @click="see_add_dialog = false"
          width="auto"
          style="          
            font-size: 1.06rem;            
            margin-right:: 2rem;
            font-weight: bold;
          "
          class="el-icon-s-unfold"
          >返回</el-button
        >
      </div>
    </el-dialog>

    <el-dialog
      title="更新商品"
      :visible.sync="see_update_dialog"
      class="Mdialog"
      :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
    >
      <el-descriptions class="margin-top" title="更新商品" :column="1" border>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-picture"></i>
            商品名称
          </template>
          <el-input
            v-model.lazy="goods_data.name"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            价格（学虫币）
          </template>
          <el-input
            v-model.lazy="goods_data.money"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            文件大小
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.size"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            下载次数
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.times"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            更新时间
          </template>
          <el-input
            disabled
            v-model.lazy="goods_data.time"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
        <el-descriptions-item labelStyle="font-size:1.06rem;">
          <template slot="label">
            <i class="el-icon-s-custom"></i>
            商品简介
          </template>
          <el-input
            v-model.lazy="goods_data.blurb"
            style="font-size: 1.06rem"
          ></el-input>
        </el-descriptions-item>
      </el-descriptions>
      <div style="height: 1rem"></div>
      <el-upload
        v-show="goods_data.id"
        class="upload-demo"
        style="width: 100%;margin-left=auto;margin-right:auto;text-align:center"
        :headers="head"
        drag
        ref="upload_update"
        :auto-upload="true"
        :data="passparam"
        :action="save_file_url"
        :on-success="upSuccess"
        multiple
      >
        <i class="el-icon-upload"></i>
        <div class="el-upload__text" style="font-size: 1.06rem">
          将文件拖到此处，或<em>点击上传</em>
        </div>
      </el-upload>
      <div style="height: 1rem"></div>
      <div style="text-align: right">
        <el-button
          size="small"
          type="success"
          @click="downloadGoods()"
          style="font-size: 1.06rem; margin-right: 2rem; font-weight: bold"
          width="auto"
          class="el-icon-download"
          >下载</el-button
        >
        <el-button
          size="small"
          type="success"
          @click="updateGoods()"
          style="
            font-size: 1.06rem;
            margin-right: 2rem;
            font-weight: bold;
            background-color: deeppink;
            border: 0;
          "
          width="auto"
          class="el-icon-upload2"
          >更新</el-button
        >
        <el-button
          size="small"
          type="primary"
          @click="see_update_dialog = false"
          width="auto"
          style="          
            font-size: 1.06rem;
            margin-right:: 2rem;
            font-weight: bold;
          "
          class="el-icon-s-unfold"
          >返回</el-button
        >
      </div>
    </el-dialog>

    <el-dialog
      title="批量新增商品"
      :visible.sync="see_more_add_dialog"
      class="Mdialog"
      width="30%"
    >
      <el-upload
        class="upload-demo"
        style="width: 100%;margin-left=auto;margin-right:auto;text-align:center"
        :headers="head"
        drag
        ref="upload_large_num"
        :auto-upload="true"
        :action="save_more_file_url"
        :on-success="upSuccess"
        multiple
      >
        <i class="el-icon-upload"></i>
        <div class="el-upload__text" style="font-size: 1.06rem">
          将文件拖到此处，或<em>点击上传</em>
        </div>
      </el-upload>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: "goodsmanage",
  async created() {
    this.goodsList = [];
    this.issearch = false; //判断是否搜索，从而进行分页查找
    this.page = 1;
    this.limit = 50;
    let tem_list = [];
    for (let i = 0; i < this.limit; ++i) {
      tem_list.push(this.$SqsGlobal.goods_list_data);
    }
    this.goodsList = tem_list;
    await this.getlinuxurl();
  },
  async activated() {
    this.isseetip = true;
    if (this.total != 0) {
      if (this.issearch) {
        await this.search();
      } else {
        await this.getlist();
      }
    } else {
      await this.getlist();
    }
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  data() {
    return {
      head: {
        authorization: "Bearer " + window.localStorage.getItem("authorization"),
        key: window.localStorage.getItem("key"),
      },
      passparam: {
        id: 0,
      },
      isseetip: false,
      linuxurl: "",
      save_file_url: "",
      save_more_file_url: "",
      see_add_dialog: false,
      see_update_dialog: false,
      see_more_add_dialog: false,
      lastkey: "",
      issearch: false,
      goodsList: [],
      goodsId: 0,
      goodsLabe: "", //商品标签
      goodsName: "",
      goodsAc: 0,
      limit: 50,
      total: 0,
      page: 1,
      key: "",
      goods_data: {},
    };
  },
  methods: {
    upSuccess(response, file, file_list) {
      this.see_update_dialog = false;
      this.see_add_dialog = false;
      this.goods_data = [];
      if (response.code == 1) {
        this.$msg({
          type: "success",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.deleteOneFileHistoryFromUpList(file, file_list);
      this.getlist();
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.linuxurl = res;
      this.save_file_url = res + "/Goods/uploadFile";
      this.save_more_file_url = res + "/Goods/uploadMoreFile";
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
        background: "rgba(26, 26, 26, 0.46) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background = "rgba(41, 50, 56, 0.46) !important";
      }
      if (row.has_buy) {
        styleRes.color = "chartreuse";
        return styleRes;
      } else {
        styleRes.color = "aliceblue";
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
    async downloadGoods() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/judgeCanBuy",
        portType: {
          process: "8797",
        },
        data: {
          id: this.goods_data.id,
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
        this.$notice({
          title: "通知",
          dangerouslyUseHTMLString: true,
          message: res.msg,
          duration: 3600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        return;
      }
      this.$msg({
        type: "success",
        message: "开始下载",
        duration: 1600,
        offset: 80,
      });
      await this.$ajax({
        method: "post",
        url: "/Goods/downloadOne",
        responseType: "blob",
        headers: {
          "Content-Type": "application/json; application/octet-stream;",
        },
        data: {
          id: this.goods_data.id,
        },
      })
        .then((res) => {
          this.see_add_dialog = false;
          this.see_more_add_dialog = false;
          this.see_update_dialog = false;
          this.$msg({
            type: "success",
            message: "下载完成",
            duration: 1600,
            offset: 80,
          });
          let Name = "";
          if (this.goods_data.name.indexOf("." + this.goods_data.type) != -1) {
            Name = this.goods_data.name;
          } else {
            Name = this.goods_data.name + "." + this.goods_data.type;
          }
          if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            const blob = new Blob([res.data], {
              type: "application/octet-stream;application/zip",
            });
            window.navigator.msSaveOrOpenBlob(blob, Name);
          } else {
            /* 火狐谷歌的文件下载方式 */
            const blob = new Blob([res.data], {
              type: "application/octet-stream;application/zip",
            });
            let url = window.URL.createObjectURL(blob);
            const link = document.createElement("a"); // 创建a标签
            link.href = url;
            link.download = Name; // 重命名文件
            link.click();
            URL.revokeObjectURL(url); // 释放内存
          }
          if (this.issearch) {
            this.search();
          } else {
            this.getlist();
          }
        })
        .catch((t) => {
          this.$msg({
            type: "error",
            message: t,
            duration: 1600,
            offset: 80,
          });
        });
    },
    //获取商品列表
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/getList",
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
      if (res.code == 1) {
        this.total = res.allnum;
        this.goodsList = res.data;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async lookOne(id) {
      this.goods_data = [];
      this.passparam.id = id;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/lookOne",
        portType: {
          process: "8794",
        },
        data: {
          id: id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.goods_data = res.data;
        this.see_update_dialog = true;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },

    async addGoods() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/saveFileToDb",
        portType: {
          process: "8794",
        },
        data: {
          goods: {
            name: this.goods_data.name,
            money: this.goods_data.money,
            blurb: this.goods_data.blurb,
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
      if (res.code == 1) {
        this.goods_data.id = res.data;
        this.passparam.id = res.data;
        this.see_update_dialog = false;
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        if (this.issearch) {
          this.search();
        } else {
          this.getlist();
        }
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },

    async updateGoods() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/updateOne",
        portType: {
          process: "8794",
        },
        data: {
          goods: this.goods_data,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.goods_data = [];
        this.see_update_dialog = false;
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        if (this.issearch) {
          this.search();
        } else {
          this.getlist();
        }
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Goods/keySearch",
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
      if (res.code == 1) {
        this.goodsList = res.data;
        this.total = res.allnum;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
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