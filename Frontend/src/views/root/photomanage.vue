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
            :data="photoList"
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
            <el-table-column label="操作" width="160">
              <template slot-scope="scope">
                <el-button
                  class="pulse-enter-active"
                  @click="
                    photoname = scope.row;
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
            :on-success="getlist"
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
      authorization: "Bearer " + window.localStorage.getItem("authorization"),
      key: window.localStorage.getItem("key"),
    };
    this.linuxurl = window.sessionStorage.getItem("linuxurl");
    this.backurl = window.sessionStorage.getItem("linuxurl");
    if (!this.linuxurl) {
      await this.getlinuxurl();
    } else {
      this.linuxurl += "/static/homephoto/";
      this.backurl += "/Photo/addphoto";
    }
    this.photoList = [];
    await this.getlist();
    this.head = {
      authorization: "Bearer " + window.localStorage.getItem("authorization"),
      key: window.localStorage.getItem("key"),
    };
  },
  deactivated() {
    this.resetFileList();
  },
  data() {
    return {
      isadd: false,
      linuxurl: "",
      head: {
        authorization: "Bearer " + window.localStorage.getItem("authorization"),
        key: window.localStorage.getItem("key"),
      },
      photoList: [],
      backurl: "",
      head: {},
      photoname: "",
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
        background: "rgba(26, 26, 26,0.68) !important",
        color: "chartreuse",
      };
      if (rowIndex % 2 != 0) {
        styleRes.background = "rgba(41, 50, 56,0.68) !important";
      }
      return styleRes;
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.backurl = res + "/Photo/addphoto";
      this.linuxurl = res + "/static/homephoto/";
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
              if (res.data.code == 1) {
                this.getlist();
                this.$msg({
                  type: "success",
                  message: res.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: "error",
                  message: res.data.msg,
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
    resetFileList() {
      try {
        this.$refs.upload.clearFiles();
      } catch (err) {}
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
      this.photoList = res.data;
      if (res.code != 1) {
        this.$msg({
          type: "error",
          message: res.msg,
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
              name: this.photoname,
            },
          })
            .then((res) => {
              if (res.data.code == 1) {
                this.getlist();
                this.$msg({
                  type: "success",
                  message: res.data.msg,
                  duration: 1600,
                  offset: 80,
                });
              } else {
                this.$msg({
                  type: "error",
                  message: res.data.msg,
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