<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div class="shadow main-center-box-content" style="border-width: 0rem">
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 0.8rem"></div>
        <div>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 0rem;
            "
          >
            标题
          </p>
          <div>
            <el-input
              v-model.lazy="article_data.name"
              style="font-size: 1.06rem"
              placeholder="请输入文章标题"
            ></el-input>
          </div>
        </div>
        <div style="height: 1rem"></div>
        <div>
          <div style="overflow: hidden">
            <img
              class="animate"
              v-show="article_data.image && reg.test(article_data.image)"
              :src="article_data.image"
              style="
                display: block;
                width: 100%;
                height: 26rem;
                object-fit: cover;
              "
            />
          </div>
        </div>
        <div style="height: 1rem"></div>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 1rem 0.5rem 0rem;
          "
        >
          图片链接
        </p>
        <div style="margin: 1rem 1rem 0.5rem 1rem">
          <el-input
            v-model.lazy="article_data.image"
            style="font-size: 1.06rem"
            placeholder="请输入图片链接"
          ></el-input>
        </div>

        <div style="height: 1rem"></div>
        <div>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 1rem 0.5rem 0rem;
            "
          >
            其他用户是否可见
          </p>
          <el-switch
            style="margin: 1rem 1rem 0.5rem 1rem"
            v-model.lazy="article_data.public"
            :active-value="1"
            :inactive-value="0"
            active-text="公开"
            inactive-text="私密"
            active-color="#13ce66"
            inactive-color="#ff4949"
          >
          </el-switch>
        </div>
        <div style="height: 1rem"></div>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 1rem 0.5rem 0rem;
          "
        >
          内容
        </p>
        <div style="margin: 1rem 0rem"></div>

        <div class="markdown-body">
          <mavon-editor
            ref="md"
            class="md"
            @imgAdd="$imgAdd"
            @imgDel="$imgDel"
            v-model.lazy="article_data.article"
            :toolbars="toolbars"
            :subfield="prop.subfield"
            :defaultOpen="prop.defaultOpen"
            :toolbarsFlag="prop.toolbarsFlag"
            :editable="prop.editable"
            :scrollStyle="prop.scrollStyle"
            :boxShadow="prop.boxShadow"
            :tabSize="prop.tabSize"
            :codeStyle="prop.codeStyle"
            :toolbarsBackground="prop.toolbarsBackground"
            :previewBackground="prop.previewBackground"
            :editorBackground="prop.editorBackground"
            :fontSize="prop.fontSize"
            :externalLink="externalLink"
            :xssOptions="whiteList"
            style="min-height: 16rem; height: auto"
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
        <p style="margin: 2rem 1rem 0.5rem 1rem">
          <el-tag
            size="small"
            class="pulse-enter-active"
            effect="dark"
            type="danger"
            style="cursor: pointer; font-size: 1.06rem; font-weight: bold"
            @click="touserpage(article_data.writerid)"
            >发布者：{{ article_data.writer }}
          </el-tag>
        </p>
        <p style="margin: 1rem 1rem 0.5rem 1rem">
          <el-tag
            size="small"
            effect="dark"
            type="success"
            style="font-size: 1.06rem; font-weight: bold"
            >发布于：{{ article_data.releasetime }}
          </el-tag>
        </p>
        <p style="margin: 1rem 1rem 0.5rem 1rem">
          <el-tag
            size="small"
            effect="dark"
            type="success"
            style="font-size: 1.06rem; font-weight: bold"
            >最后一次修改：{{ article_data.lastchangetime }}
          </el-tag>
        </p>
        <div style="height: 1rem"></div>
      </div>
      <div style="height: 2rem"></div>
      <div slot="footer">
        <el-button
          type="text"
          style="
            float: left;
            margin-left: 2.6rem;
            color: chartreuse;
            font-size: 1.06rem;
            cursor: auto;
          "
          width="auto"
          class="el-icon-message-solid"
        >
          点赞数：{{ article_data.fabulous }}</el-button
        >
        <el-button
          type="text"
          style="
            float: left;
            margin-left: 2rem;
            color: chartreuse;
            font-size: 1.06rem;
            cursor: auto;
          "
          width="auto"
          class="el-icon-s-claim"
        >
          收藏数：{{ article_data.collection }}</el-button
        >
        <el-button
          type="text"
          @click="
            isSeeComment = false;
            toback();
          "
          width="auto"
          style="
            float: right;
            margin-right: 2.6rem;
            color: red;
            font-size: 1.06rem;
          "
          class="el-icon-s-unfold pulse-enter-active"
          >返回</el-button
        >
        <el-button
          type="text"
          style="
            float: right;
            margin-right: 2rem;
            color: deeppink;
            font-size: 1.06rem;
          "
          @click="updata()"
          width="auto"
          class="el-icon-upload pulse-enter-active"
          >更新</el-button
        >
        <el-button
          v-show="!islove"
          type="text"
          style="
            float: right;
            margin-right: 2rem;
            color: chartreuse;
            font-size: 1.06rem;
          "
          @click="collectionClick()"
          width="auto"
          class="el-icon-star-off pulse-enter-active"
          >收藏
        </el-button>

        <el-button
          v-show="islove"
          type="text"
          style="
            float: right;
            margin-right: 2rem;
            color: deeppink;
            font-size: 1.06rem;
          "
          @click="delcollectionClick()"
          width="auto"
          class="el-icon-star-on pulse-enter-active"
          >取消收藏
        </el-button>

        <el-button
          type="text"
          v-show="!fabulous"
          style="
            float: right;
            margin-right: 2rem;
            color: aqua;
            font-size: 1.06rem;
          "
          @click="fabulousClick()"
          width="auto"
          class="el-icon-message-solid pulse-enter-active"
          >点赞</el-button
        >
        <el-button
          v-if="article_data.public == 1"
          type="text"
          style="
            float: right;
            margin-right: 2rem;
            color: deeppink;
            font-size: 1.06rem;
          "
          @click="shareArticle()"
          width="auto"
          class="el-icon-share pulse-enter-active"
          >分享</el-button
        >
        <el-button
          type="text"
          style="
            float: right;
            margin-right: 2rem;
            color: red;
            font-size: 1.06rem;
          "
          @click="del()"
          width="auto"
          class="el-icon-s-release pulse-enter-active"
          >删除</el-button
        >
      </div>

      <!-- 插入视频链接的dialog提示框，表单对话框 -->
      <el-dialog
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
            <el-select v-model.lazy="form.region" placeholder="请选择链接类型">
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
      <div style="height: 6rem"></div>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
import "../../../public/md/markdown/github-markdown.min.css";

export default {
  name: "updateonearticle",
  async activated() {
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
    this.article_data.id = urlencode.decode(this.$route.query.path, "gbk");
    this.islove = false;
    await this.lookarticle();
    this.form.region = "url";
    this.$nextTick(() => {
      this.totop();
    });
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
  data() {
    return {
      reg: /^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\*\+,;=.]+$/,
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
      isroot: false,
      fabulous: false,
      total: 0,
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

      articlepagesize: 10,
      page: 1,
      key: "",

      article_data: {
        id: "",
        public: 0,
        name: "加载中",
        writer: "加载中",
        article: "加载中",
        image: "加载中",
        fabulous: "加载中",
        collection: "加载中",
        lastchangetime: "加载中",
        releasetime: "加载中",
        writerid: "",
      },

      commentnum: "",

      comment: [],

      commenttext: "",

      islove: false,
    };
  },
  methods: {
    async shareArticle() {
      try {
        const back_url = await this.getBackurl();
        let url = back_url + "/Article/oneArticle?path=" + this.article_data.id;
        this.copy(url);
      } catch (err) {}
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

    // 绑定@imgAdd event
    $imgAdd(pos, $file) {
      // 第一步.将图片上传到服务器.
      let formdata = new FormData();
      formdata.append("image", $file);
      this.$ajax({
        url: "/ArticleImage/saveImage",
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
        url: "/ArticleImage/deleteImage",
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
    //更新
    async updata() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/updataOneArticle",
        portType: {
          process: "8792",
        },
        data: {
          data: this.article_data,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1)
        this.$msg({
          type: "success",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      else
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
    },
    //删除
    async del() {
      this.$confirm("确定删除该文章吗？", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        type: "warning",
      })
        .then(() => {
          this.$ajax({
            method: "post",
            url: "/Article/deleteOneArticle",
            portType: {
              process: "8792",
            },
            data: {
              delete_id: this.article_data.id,
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
                this.$router.go(-1);
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

    touserpage(id) {
      id &&
        this.$router.push({
          path: "/userpage",
          query: {
            path: urlencode(id, "gbk"),
          },
        });
    },
    toback() {
      this.$router.go(-1);
    },
    async lookarticle() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/loadOneArticle",
        portType: {
          process: "8792",
        },
        data: {
          article_id: this.article_data.id,
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
        this.article_data = res?.data;
        this.islove = res.love;
        this.fabulous = res.fabulous;
      } else {
        this.$router.go(-1);
      }
    },
    async fabulousClick() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/fabulousOneArticle",
        portType: {
          process: "8792",
        },
        data: {
          article_id: this.article_data.id,
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
        ++this.article_data.fabulous;
        this.fabulous = true;
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

    async collectionClick() {
      ++this.article_data.collection;
      this.islove = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/collectionOneArticle",
        portType: {
          process: "8792",
        },
        data: {
          article_id: this.article_data.id,
        },
      }).catch((t) => {
        --this.article_data.collection;
        this.islove = false;
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
        --this.article_data.collection;
        this.islove = false;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },

    async delcollectionClick() {
      this.islove = false;
      --this.article_data.collection;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Article/deleteLoveArticle",
        portType: {
          process: "8792",
        },
        data: {
          article_id: this.article_data.id,
        },
      }).catch((t) => {
        this.islove = true;
        ++this.article_data.collection;
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
        this.islove = true;
        ++this.article_data.collection;
        this.$msg({
          type: "error",
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
  },
};
</script>
<style scoped>
@import "../../../public/md/markdown/github-markdown.min.css";
/**
鼠标放上，图片变大
*/
.animate {
  -webkit-transition: all 1s ease; /* Safari and Chrome */
  -moz-transition: all 1s ease; /* Firefox */
  -o-transition: all 1s ease; /* Opera */
  -ms-transition: all 1s ease; /* IE 9 */
  transition: all 1s ease;
}
img:hover {
  transform-origin: center center;
  transform: scale(1.1, 1.1);
  -webkit-transform-origin: center center;
  -webkit-transform: scale(1.1, 1.1);
  -moz-transform-origin: center center;
  -moz-transform: scale(1.1, 1.1);
  -o-transform-origin: center center;
  -o-transform: scale(1.1, 1.1);
  -ms-transform-origin: center center;
  -ms-transform: scale(1.1, 1.1);
}
</style>