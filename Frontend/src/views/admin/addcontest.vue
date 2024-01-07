<template>
  <div
    v-show="isseetip"
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow main-center-box-content">
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 0.8rem"></div>
        <!-- 竞赛名称 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          竞赛名称
        </p>
        <el-input
          placeholder="请输入竞赛名称"
          style="font-size: 1.06rem"
          v-model.lazy="onedata.name"
        >
        </el-input>
        <!-- 竞赛详情介绍 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          竞赛详情介绍
        </p>
        <div class="markdown-body">
          <mavon-editor
            ref="md"
            @imgAdd="$imgAdd"
            @imgDel="$imgDel"
            class="md"
            v-model.lazy="onedata.content"
            :toolbars="toolbars"
            :subfield="prop.subfield"
            :defaultOpen="prop.defaultOpen"
            :toolbarsFlag="prop.toolbarsFlag"
            :editable="prop.editable"
            :scrollStyle="prop.scrollStyle"
            :codeStyle="prop.codeStyle"
            :boxShadow="prop.boxShadow"
            :tabSize="prop.tabSize"
            :toolbarsBackground="prop.toolbarsBackground"
            :previewBackground="prop.previewBackground"
            :editorBackground="prop.editorBackground"
            :fontSize="prop.fontSize"
            :externalLink="externalLink"
            :xssOptions="whiteList"
            style="min-height: 16rem; height: auto; border-width: 0rem"
          >
            <!-- 引用视频链接的自定义按钮 -->
            <template v-slot:left-toolbar-after>
              <!--点击按钮触发的事件是打开表单对话框-->
              <el-button
                type="text"
                @click="
                  form.region = 'url';
                  dialogFormVisible = true;
                "
                aria-hidden="true"
                class="op-icon fa"
                title="插入视频资源"
              >
                <i class="el-icon-video-camera-solid" />
              </el-button>
            </template>
          </mavon-editor>
        </div>

        <!-- 竞赛时间设置 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          竞赛时间设置
        </p>

        <div class="block">
          <el-date-picker
            v-model.lazy="contesttime"
            value-format="timestamp"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
          >
          </el-date-picker>
        </div>

        <!-- 赛制类型 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          赛制类型
        </p>

        <el-select
          :popper-append-to-body="false"
          v-model.lazy="mytype"
          placeholder="请选择赛制类型"
          @change="passtype()"
        >
          <el-option
            v-for="item in options"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          >
          </el-option>
        </el-select>
        <div v-if="$store.state.root && $store.state.my_name === 'root'">
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 0.5rem 0rem;
            "
          >
            默认参赛人数
          </p>
          <el-input
            placeholder="默认参赛人数"
            style="font-size: 1.06rem"
            v-model.lazy="defaultnum"
          ></el-input>
        </div>
        <!-- 竞赛题目 -->
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          竞赛题目（后端限制一场竞赛里不可重复添加同一道题目）
        </p>
        <div>
          <div v-for="tem in addproblem" :key="tem.index">
            <el-button
              class="el-icon-delete-solid pulse-enter-active"
              type="text"
              style="
                font-size: 1.02rem;
                color: rgba(var(--ltpp-main-bk-color), 1);
              "
              @click="DeleteContestProblem(tem.id)"
              >点击移除题目：{{ "|标题|：" + tem.problemName }}
              {{ "|标签|:" + tem.problemLabe }}</el-button
            >
          </div>
        </div>
        <div style="height: 0.66rem"></div>
        <div>
          <el-input
            @keyup.enter.native="search()"
            v-model.lazy="keyvalue"
            placeholder="输入题目关键字搜索"
            ><el-button
              size="mini"
              slot="append"
              icon="el-icon-search"
              @click="search()"
            ></el-button
          ></el-input>
        </div>
        <div>
          <el-table
            :cell-style="cellStyle"
            :header-cell-style="{
              color: '#FFFFFF',
              'font-size': '1.06rem',
            }"
            :data="problemlist"
            style="width: 100%"
          >
            <el-table-column width="55">
              <template slot-scope="scope">
                <el-button
                  type="text"
                  style="color: rgba(var(--ltpp-main-bk-color), 1)"
                  class="el-icon-circle-plus"
                  @click="changechoose(scope.row)"
                ></el-button>
              </template>
            </el-table-column>
            <el-table-column label="标题" width="auto">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'标题：' + scope.row.problemName"
                  placement="right"
                >
                  <span
                    @click="toonepro(scope.row.id)"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      cursor: pointer;
                    "
                    class="my-span"
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
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #409eff;
                    "
                  >
                    {{ scope.row.problemLabe }}
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
                    'AC/ALL：' + scope.row.ACNum + '/' + scope.row.ALLSubmitNum
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
            <el-table-column width="auto" label="题目来源" align="center">
              <template slot-scope="scope">
                <el-tooltip
                  class="item"
                  effect="dark"
                  :content="'题目来源：' + scope.row.problemFrom"
                  placement="right"
                >
                  <span
                    class="my-span"
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: #e6a23c;
                    "
                  >
                    {{ scope.row.problemFrom }}
                  </span>
                </el-tooltip>
              </template>
            </el-table-column>
          </el-table>
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
          :page-size="blogpagesize"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
        ></el-pagination>

        <div style="height: 2rem"></div>
        <div style="text-align: right">
          <el-button
            width="auto"
            round
            type="success"
            style="margin: 1rem 2rem 0rem 1rem; font-size: 1.06rem"
            class="el-icon-upload pulse-enter-active"
            @click="AddContestProblem()"
            :loading="isup"
            >创建竞赛</el-button
          >

          <el-button
            type="primary"
            round
            width="auto"
            class="el-icon-s-unfold pulse-enter-active"
            style="margin: 1rem 0.4rem 0rem 1rem; font-size: 1.06rem"
            @click="backlast()"
            >返回</el-button
          >
        </div>
        <div style="height: 3.6rem"></div>
        <!-- 插入视频链接的dialog提示框，表单对话框 -->
        <el-dialog
          :close-on-click-modal="false"
          title="插入视频资源"
          :append-to-body="true"
          :visible.sync="dialogFormVisible"
          :width="($store.state.max_width / $store.state.now_width) * 100 + '%'"
        >
          <el-form :model="form">
            <el-form-item label="视频链接" :label-width="formLabelWidth">
              <el-input v-model.lazy="form.link" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="链接类型" :label-width="formLabelWidth">
              <el-select
                v-model.lazy="form.region"
                placeholder="请选择链接类型"
              >
                <el-option label="iframe标签" value="iframe"></el-option>
                <el-option label="url链接" value="url"></el-option>
              </el-select>
            </el-form-item>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <!--单机确定按钮后触发 videoLink事件函数，开始格式化链接格式并插入到文本域-->
            <el-button type="primary" @click="videoLink">确 定</el-button>
            <el-button
              style="margin-left: 2rem"
              type="warning"
              @click="dialogFormVisible = false"
              >取 消</el-button
            >
          </div>
        </el-dialog>

        <!-- 错误提示框 -->
        <el-dialog
          :close-on-click-modal="false"
          title="提示"
          :visible.sync="dialogVisible"
          :append-to-body="true"
          width="30%"
          id="link-error"
        >
          <span class="my-span">视频链接格式错误，请重新确认后再输入！</span>
          <span slot="footer" class="dialog-footer my-span">
            <el-button type="primary" @click="dialogVisible = false"
              >确 定</el-button
            >
          </span>
        </el-dialog>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";
export default {
  name: "addcontest",
  activated() {},
  async activated() {
    this.isseetip = true;
    this.isup = false;
    this.page = 1;
    this.keyvalue = "";
    this.total = 0;
    this.blogpagesize = 10;
    this.issearch = false;
    this.onedata.type = "ACM";
    this.form.region = "url";
    await this.getlist();
    this.$nextTick(() => {
      this.totop();
    });
  },
  deactivated() {
    this.isseetip = false;
  },
  destroyed() {
    this.isseetip = false;
  },
  data() {
    return {
      isup: false,
      defaultnum: 0,
      isseetip: true,
      whiteList: false,
      dialogFormVisible: false, // 用于控制表单对话框的开启和关闭
      dialogVisible: false, // 用于控制错误提示对话框的开启和关闭
      formLabelWidth: "5rem", // 设定表单对话框内表单是宽度
      form: {
        // 表单对话框内表单的数据
        link: "",
        region: "",
      },
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => "md/highlightjs/highlight.min.js",
        hljs_css: (css) => "md/highlightjs/styles/" + css + ".min.css",
        hljs_lang: (lang) => "md/highlightjs/languages/" + lang + ".min.js",
        katex_css: () => "md/katex/katex.min.css",
        katex_js: () => "md/katex/katex.min.js",
      },
      keyvalue: "",
      contesttime: "",
      onedata: {},
      problemlist: [],
      total: 0,
      page: 1,
      blogpagesize: 10,
      addproblem: [],
      issearch: false,
      mytype: "ACM",
      /* context:  '',//输入的数据 */
      toolbars: {
        bold: true, // 粗体
        italic: true, // 斜体
        header: true, // 标题
        underline: true, // 下划线
        mark: true, // 标记
        superscript: true, // 上角标
        quote: true, // 引用
        ol: true, // 有序列表
        link: true, // 链接
        imagelink: true, // 图片链接

        code: true, // code
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: false, // 沉浸式阅读
        htmlcode: false, // 展示html源码
        /* 1.3.5 */
        undo: true, // 上一步
        trash: true, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
      options: [
        {
          value: "ACM",
          label: "ACM赛制",
        },
        {
          value: "OI",
          label: "OI赛制",
        },
        {
          value: "IOI",
          label: "IOI赛制",
        },
        {
          value: "SQS",
          label: "SQS赛制（可看错误样例，排名，无罚时）",
        },
      ],
    };
  },
  computed: {
    prop() {
      let data = {
        subfield: false, // 单双栏模式
        defaultOpen: "edit", //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: true,
        toolbarsFlag: true, //工具栏
        scrollStyle: true,
        codeStyle: "atom-one-dark",
        boxShadow: false,
        ishljs: true,
        tabSize: 4,
        toolbarsBackground: "rgba(0,0,0,0)",
        editorBackground: "rgba(0,0,0,0)",
        previewBackground: "rgba(0,0,0,0)",
        fontSize: "1.06rem",
        navigation: false,
      };
      return data;
    },
  },
  methods: {
    toonepro(id) {
      id &&
        this.$router.push({
          path: "/oneproblem",
          query: {
            path: urlencode(id, "gbk"),
            contest: urlencode("", "gbk"),
          },
        });
    },
    videoLink() {
      // 准备链接模板
      let linkFrame = "";
      if (this.form.region == "") {
        this.form.region = "url";
      }
      // 创建一个div盒子，为提取src做准备
      let box = document.createElement("div");
      // 将原始链接插入到盒子中

      box.innerHTML = this.form.link;
      // 判断不同的视频原链接类型
      if (this.form.region == "url") {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><video style="height: ${
          (window.innerHeight / 4) * 3 + "px"
        }; width: 100%" controls controlslist="nodownload"><source src="`;
        let linkFrameEnd = `" type="video/mp4" /></video></div>`;

        linkFrame = linkFrameStart + this.form.link + linkFrameEnd;
      } else if (
        this.form.region == "iframe" &&
        box.getElementsByTagName("iframe").length > 0
      ) {
        let linkFrameStart = `<div align="center" width="100%" style="border-width:0rem"><iframe height="${
          (window.innerHeight - 240) / 2 + "px"
        }" width="80%" src="`;
        let linkFrameEnd = `" allowfullscreen="true" scrolling="no" border="0" frameborder="no" framespacing="0" style="border-width: 0rem; min-height: 31.2rem;"></iframe></div>`;

        // 从iframe标签中提取src属性
        linkFrame =
          linkFrameStart +
          box.getElementsByTagName("iframe")[0].getAttribute("src") +
          linkFrameEnd;
      } else {
        // 原始链接格式错误时弹出错误提示
        this.dialogFormVisible = false;
        this.dialogVisible = true;
      }
      // 复原表单文本框内容
      this.form.link = "";

      // 获取文本域中当前光标起始位置、结束位置以及滚动条位置（滚动条位置我认为没有必要，如有需要可以自己取消注释）
      let textarea = document.getElementsByClassName("auto-textarea-input")[0];
      let posStart = textarea.selectionStart;
      let posEnd = textarea.selectionEnd;
      // let posScroll = document.getElementsByClassName("v-note-edit")[0].scrollTop;
      // 获取文本域中未选中的的前半部分和后半部分，以被选中内容起始和结束位置做分割点
      let subStart = this.$refs.md.d_value.substring(0, posStart);
      let subEnd = this.$refs.md.d_value.substring(
        posEnd,
        this.$refs.md.d_value.length
      );
      // 拼接并替换文本域内容
      this.$refs.md.d_value = subStart + "\n" + linkFrame + "\n" + subEnd;
      // document.getElementsByClassName("v-note-edit")[0].scrollTop = posScroll;

      // 关闭对话框
      this.dialogFormVisible = false;
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
        styleRes.color = "#chartreuse";
        return styleRes;
      }
    },
    passtype() {
      this.onedata.type = this.mytype;
    },
    backlast() {
      this.$router.go(-1);
    },

    // 绑定@imgAdd event
    $imgAdd(pos, $file) {
      // 第一步.将图片上传到服务器.
      let formdata = new FormData();
      formdata.append("image", $file);
      this.$ajax({
        url: "/Contestimage/saveImage",
        method: "post",
        data: formdata,
        headers: { "Content-Type": "multipart/form-data" },
      })
        .then((url) => {
          // 第二步.将返回的url替换到文本原位置![...](0) -> ![...](url)
          // $vm.$img2Url 详情见本页末尾
          this.$refs.md.$img2Url(pos, url.data.url);
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
    async $imgDel(pos) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contestimage/deleteImage",
        data: {
          path: pos[0],
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
    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.keysearch();
      } else {
        this.getlist();
      }
    },

    handleSizeChange(val) {
      this.page = 1;
      this.blogpagesize = val;
      if (this.issearch) {
        this.keysearch();
      } else {
        this.getlist();
      }
    },
    //获取题目列表
    async getlist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Oj/getProblemList",
        portType: {
          process: "8794",
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
      this.problemlist = res?.data;
      this.total = res.allnum;
    },

    DeleteContestProblem(id) {
      for (let i = 0; i < this.addproblem.length; i++) {
        if (this.addproblem[i].id == id) {
          this.addproblem.splice(i, 1); //删除
          return;
        }
      }
    },
    async AddContestProblem() {
      if (this.isup) {
        this.$msg({
          type: "error",
          message: "正在创建竞赛，请耐心等待！",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      this.isup = true;
      let resproblem = new Array(); //创建一个新的空数组
      this.addproblem.forEach((val) => {
        resproblem.push(val.id);
      });
      if (!(this.$store.state.root && this.$store.state.my_name === "root")) {
        this.defaultnum = 0;
      }
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/addContest",
        portType: {
          process: "8796",
        },
        data: {
          data: this.onedata,
          time: this.contesttime,
          problemdata: resproblem,
          defaultnum: this.defaultnum,
        },
      }).catch((t) => {
        this.isup = false;
        this.defaultnum = 0;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      if (res?.code == 1) {
        this.defaultnum = 0;
        this.onedata = {};
        this.problemlist = [];
        resproblem = [];
        this.addproblem = [];
        this.page = 1;
        this.keyvalue = "";
        this.total = 0;
        this.blogpagesize = 10;
        this.issearch = false;
        this.form.region = "url";
        this.form = {
          // 表单对话框内表单的数据
          link: "",
          region: "",
        };
        this.contesttime = "";
        this.mytype = "ACM";
        this.isup = false;
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$router.go(-1);
      } else {
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.isup = false;
    },
    changechoose(val) {
      this.addproblem.push(val);
      this.$msg({
        type: "success",
        message: "添加成功",
        duration: 1600,
        offset: 80,
      });
    },
    async keysearch() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Contest/backContestSearchProblem",
        portType: {
          process: "8794",
        },
        data: {
          key: this.keyvalue,
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
      this.problemlist = res?.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      this.page = 1;
      if (
        this.keyvalue == "" ||
        this.keyvalue == null ||
        this.keyvalue == undefined
      ) {
        this.issearch = false;
        this.getlist();
        return;
      }
      this.issearch = true;
      this.keysearch();
    },
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
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