<!-- 首页侧边栏照片管理 -->
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select shadow"
  >
    <div style="color: azure; height: auto; width: 100%">
      <div>
        <div style="text-align: left">
          <el-button
            type="text"
            @click="deleteall()"
            class="el-icon-delete pulse-enter-active"
            style="
              font-size: 1.06rem;
              color: deeppink;
              font-weight: bold;
              margin: 1.6rem 1rem;
              float: left;
            "
            size="mini"
            >清空首页侧边栏图片</el-button
          >
        </div>

        <div>
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="photo_list"
            style="width: 100%"
          >
            <el-table-column label="图片名称" width="auto">
              <template slot-scope="scope">
                <p
                  @click="lookimg(scope.row)"
                  style="font-weight: bold; font-size: 1.06rem; color: #409eff"
                  type="success"
                >
                  {{ scope.row.substr(0, 40) }}
                </p>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="160" align="center">
              <template slot-scope="scope">
                <el-button
                  class="pulse-enter-active"
                  @click="
                    photo_path = scope.row;
                    deletephoto();
                  "
                  style="
                    margin: 0rem 2rem 0rem 0rem;
                    font-size: 1.06rem;
                    font-weight: bold;
                    color: red;
                  "
                  type="text"
                  >删除
                </el-button>
              </template>
            </el-table-column>
          </el-table>

          <div style="height: 4.6rem"></div>
          <el-upload
            class="upload-demo"
            style="width: 100%; text-align: center"
            :headers="head"
            :auto-upload="true"
            :on-success="upSuccess"
            drag
            ref="upload"
            :action="backurl"
            multiple
          >
            <i class="el-icon-upload"></i>
            <div class="el-upload__text">
              将图片拖到此处，或<em>点击上传</em>
            </div>
            <div class="el-upload__tip" slot="tip">
              仅允许上传jpg/png/jpeg文件
            </div>
          </el-upload>

          <div style="height: 4rem"></div>
        </div>
      </div>
    </div>

    <div style="height: 8rem"></div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "photomanage",
  async activated() {
    this.head = {
      Authorization: "Bearer " + window.localStorage.getItem("authorization"),
      Key: window.localStorage.getItem("key"),
      Requestid : this.Base64Encode(new Date().getTime())
    };
    this.requestid_timer = setInterval(() => {
        this.head.Requestid = this.Base64Encode(new Date().getTime())
    }, 10000);
    this.linuxurl = window.sessionStorage.getItem("linuxurl");
    this.backurl = window.sessionStorage.getItem("linuxurl");
    if (!this.linuxurl) {
      await this.getlinuxurl();
    } else {
      this.backurl += "/Photo/addphoto";
    }
    this.photo_list = [];
    await this.getlist();
  },
  deactivated() {
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
  },
  data() {
    return {
      requestid_timer: null, 
      isadd: false,
      linuxurl: "",
      head: {},
      photo_list: [],
      backurl: "",
      photo_path: "",
    };
  },
  methods: {
    lookimg(name) {
      this.$router.push({
        path: "/staticfile",
        query: {
          path: urlencode(this.linuxurl + name, "gbk"),
        },
      });
    },
    cellStyle({ rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background =
          "rgba(var(--ltpp-main-bk-color), 0.06) !important";
      }
      return styleRes;
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.backurl = res + "/Photo/addphoto";
    },
    async deleteall() {
      this.$confirm("确定清空全部图片吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Photo/deleteAll",
            portType: {
              process: "8797",
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.getlist();
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
            message: "取消清空",
          });
        });
    },
    upSuccess(response, file, file_list) {
      if (response && response.code && response.code != 1) {
        this.$msg({
          type: "error",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "success",
          message: response.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.deleteOneFileHistoryFromUpList(file, file_list);
      this.getlist();
    },
    //获取图片列表
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Photo/loadPhoto",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.photo_list = res?.data;
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
    async deletephoto() {
      this.$confirm("确定删除该图片吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Photo/deletePhoto",
            portType: {
              process: "8797",
            },
            data: {
              path: this.photo_path,
            },
          })
            .then((res) => {
              if (res?.data.code == 1) {
                this.getlist();
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