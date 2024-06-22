<!-- 视频管理 -->
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select shadow ltpp-list-box"
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
      <div style="height: 1rem"></div>
      <div>
        <div style="text-align: left; float: left; margin-left: 1rem">
          <el-button
            type="text"
            @click="
              id = '';
              url = '';
              name = '';
              tag = '';
              isadd = true;
              isupdate = false;
            "
            class="el-icon-plus pulse-enter-active"
            style="font-size: 1.06rem; font-weight: bold; color: chartreuse"
            >添加视频</el-button
          >
        </div>
        <div style="text-align: right; float: right; margin-right: 1rem">
          <el-button
            type="text"
            @click="
              url = '';
              name = '';
              isadd = false;
              isupdate = false;
              deleteallvideo();
            "
            class="el-icon-delete pulse-enter-active"
            style="font-size: 1.06rem; font-weight: bold; color: deeppink"
            >清空视频</el-button
          >
        </div>
        <div :style="`min-height:${$store.state.no_scroll_height * 0.82}vh;`">
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="videoList"
            style="width: 100%"
          >
            <el-table-column label="视频名称" width="360">
              <template slot-scope="scope">
                <el-tooltip
                  class="item;"
                  effect="dark"
                  :content="scope.row.name"
                  placement="right"
                >
                  <span
                    class="my-span"
                    @click="lookvideo(scope.row.url)"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #409eff;
                      cursor: pointer;
                    "
                    type="success"
                  >
                    {{ scope.row.name.substr(0, 20) }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
            <el-table-column label="视频url" width="auto" align="center">
              <template slot-scope="scope">
                <p
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.url.substr(0, 15) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="标签" width="auto" align="center">
              <template slot-scope="scope">
                <p
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.tag.substr(0, 10) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="时间" width="210" align="center">
              <template slot-scope="scope">
                <p
                  style="font-weight: bold; font-size: 1.06rem; color: #67c23a"
                >
                  {{ scope.row.time }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="260" align="center">
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
                    url = scope.row.url;
                    name = scope.row.name;
                    tag = scope.row.tag;
                    isadd = false;
                    isupdate = true;
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
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            视频
          </p>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频关键字/名称"
            v-model.lazy="name"
            @keyup.enter.native="updateid()"
          ></el-input>
          <div style="height: 1.06rem"></div>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频url"
            v-model.lazy="url"
            @keyup.enter.native="updateid()"
          ></el-input>
          <div style="height: 1.06rem"></div>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频标签"
            v-model.lazy="tag"
            @keyup.enter.native="updateid()"
          ></el-input>
        </div>
        <div style="float: left; text-align: left" v-show="isupdate">
          <el-button
            width="auto"
            size="small"
            style="font-size: 1.06rem; margin: 1rem 2rem 0rem 2rem"
            class="pulse-enter-active el-icon-success"
            type="primary"
            @click="updateid()"
          >
            更新</el-button
          >
        </div>

        <div style="text-align: right">
          <el-button
            type="success"
            width="auto"
            size="small"
            style="margin: 1rem 2rem 0rem 2rem; font-size: 1.06rem"
            class="pulse-enter-active el-icon-warning"
            @click="
              isadd = false;
              isupdate = false;
            "
          >
            取消</el-button
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
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 0rem 0rem 1rem 0rem;
            "
          >
            视频（可选择嵌入外站视频或者上传视频）
          </p>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频关键字/名称"
            v-model.lazy="name"
            @keyup.enter.native="addid()"
          ></el-input>
          <div style="height: 1.06rem"></div>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频url"
            v-model.lazy="url"
            @keyup.enter.native="addid()"
          ></el-input>
          <div style="height: 1.06rem"></div>
          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入视频标签"
            v-model.lazy="tag"
            @keyup.enter.native="addid()"
          ></el-input>
        </div>
        <div style="float: left; text-align: left" v-show="isadd">
          <el-button
            width="auto"
            size="small"
            style="font-size: 1.06rem; margin: 1rem 2rem 0rem 2rem"
            class="pulse-enter-active el-icon-success"
            type="primary"
            @click="addid()"
          >
            添加</el-button
          >
        </div>
        <div style="text-align: right">
          <el-button
            type="success"
            width="auto"
            size="small"
            style="margin: 1rem 2rem 0rem 2rem; font-size: 1.06rem"
            class="pulse-enter-active el-icon-warning"
            @click="
              isadd = false;
              isupdate = false;
            "
          >
            取消</el-button
          >
        </div>
        <el-divider content-position="center">推荐使用下方方式</el-divider>
        <div style="margin-top: 2rem">
          <el-upload
            class="upload-demo"
            style="
              width: 100%;
              margin-left: auto;
              margin-right: auto;
              text-align: center;
            "
            :headers="head"
            drag
            ref="upload"
            :auto-upload="true"
            :action="bkvideourl"
            :on-success="getupres"
            multiple
          >
            <i class="el-icon-upload"></i>
            <div class="el-upload__text" style="font-size: 1.06rem">
              将视频拖到此处，或<em>点击上传</em>
            </div>
            <div class="el-upload__tip" slot="tip" style="font-size: 1.06rem">
              (拖放到此处或者选择文件后自动提交视频)
            </div>
          </el-upload>
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
</template>

<script>
import urlencode from '../../../updateCompoents/urlencode';

export default {
  name: 'videomanage',
  created() {
    this.issearch = false; //判断是否搜索，从而进行分页查找
  },
  async activated() {
    this.head = {
      Authorization: 'Bearer ' + window.localStorage.getItem('authorization'),
      Key: window.localStorage.getItem('key'),
      Requestid: this.Base64Encode(new Date().getTime()),
    };
    this.requestid_timer = setInterval(() => {
      this.head.Requestid = this.Base64Encode(new Date().getTime());
    }, 1000);
    const tem_bkvideourl = window.sessionStorage.getItem('linuxurl');
    if (!tem_bkvideourl) {
      await this.getlinuxurl();
    } else {
      this.bkvideourl = tem_bkvideourl + '/Video/uploadvideo';
    }
    this.page = 1;
    this.limit = 50;
    this.search();
  },
  deactivated() {
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
  },
  data() {
    return {
      reg: /^(https?:\/\/(([a-zA-Z0-9]+-?)+[a-zA-Z0-9]+\.)+[a-zA-Z]+)(:\d+)?(\/.*)?(\?.*)?(#.*)?$/,
      requestid_timer: null,
      tag: '',
      lastkey: '',
      bkvideourl: window?.location?.href,
      head: {
        authorization: 'Bearer ' + window.localStorage.getItem('authorization'),
        key: window.localStorage.getItem('key'),
      },
      isupdate: false,
      isadd: false,
      issearch: false,
      videoList: [],
      total: 0,
      page: 1,
      limit: 50,
      key: '',
      id: 0,
      name: '',
      url: window?.location?.href,
    };
  },
  methods: {
    initData() {
      this.videoList = [];
      let tem_list = [];
      for (let i = 0; i < this.limit; ++i) {
        tem_list.push(this.$SqsGlobal.video_list_data);
      }
      this.videoList = tem_list;
    },
    lookvideo(url) {
      if (this.reg.test(url)) {
        this.$router.push({
          path: '/staticfile',
          query: {
            path: urlencode(url, 'gbk'),
          },
        });
      } else {
        this.openOuterUrl(url);
      }
    },
    cellStyle({ rowIndex }) {
      let styleRes = {
        background: 'rgba(var(--ltpp-light-color), 0.16) !important',
        color: 'chartreuse',
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          'rgba(var(--ltpp-main-bk-color), 0.06) !important';
      }
      return styleRes;
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.bkvideourl = res + '/Video/uploadVideo';
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
    getupres(response, file, file_list) {
      if (response && response.code && response.code != 1) {
        this.$msg({
          type: 'error',
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'success',
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.deleteOneFileHistoryFromUpList(file, file_list);
      this.getlist();
    },
    //获取视频列表
    async getlist() {
      this.initData();
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Video/backLoadVideo',
        portType: {
          process: '8797',
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
      this.total = res.allnum;
      this.videoList = res?.data;
    },
    async deleteallvideo() {
      this.$confirm('确定清空全部视频吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          this.$ajax({
            method: 'post',
            url: '/Video/deleteAllVideo',
            portType: {
              process: '8797',
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.getlist();
                this.$msg({
                  type: 'success',
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: 'error',
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              }
            })
            .catch((t) => {
              this.$msg({
                type: 'error',
                message: t,
                duration: 1600,
                offset: 80,
              });
            });
        })
        .catch(() => {
          this.$msg({
            type: 'info',
            duration: 1600,
            offset: 80,
            message: '取消清空',
          });
        });
    },
    //删除
    async deleteid() {
      this.$confirm('确定删除该视频吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      })
        .then(() => {
          this.$ajax({
            method: 'post',
            url: '/Video/deleteVideo',
            portType: {
              process: '8797',
            },
            data: {
              video_id: this.id,
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.getlist();
                this.$msg({
                  type: 'success',
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: 'error',
                  message: res?.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              }
            })
            .catch((t) => {
              this.$msg({
                type: 'error',
                message: t,
                duration: 1600,
                offset: 80,
              });
            });
        })
        .catch(() => {
          this.$msg({
            type: 'info',
            duration: 1600,
            offset: 80,
            message: '取消删除',
          });
        });
    },
    //搜索
    async keysearch() {
      this.lastkey = this.key;
      this.initData();
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Video/backFindVideo',
        portType: {
          process: '8797',
        },
        data: {
          key: this.key,
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
      this.videoList = res?.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      if (this.key == '' || this.key == null || this.key == undefined) {
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

    //更新
    async updateid() {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Video/updateVideo',
        portType: {
          process: '8797',
        },
        data: {
          tabledata: {
            id: this.id,
            name: this.name,
            url: this.url,
            tag: this.tag,
          },
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.isadd = false;
        this.isupdate = false;
        this.id = '';
        this.name = '';
        this.url = '';
        this.tag = '';
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.id = '';
      this.name = '';
      this.url = '';
      this.tag = '';
      this.getlist();
    },
    //添加
    async addid() {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Video/addVideo',
        portType: {
          process: '8797',
        },
        data: {
          tabledata: {
            name: this.name,
            url: this.url,
            tag: this.tag,
          },
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.isupdate = false;
        this.isadd = false;
        this.id = '';
        this.name = '';
        this.url = '';
        this.tag = '';
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.id = '';
      this.name = '';
      this.url = '';
      this.tag = '';
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
