<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-03-17 09:42:16
 * @LastEditors: ltpp-universe 1491579574@qq.com
 * @LastEditTime: 2024-01-07 15:07:00
 * @FilePath: \LTPP-CODE\Frontend\src\views\home\questionlist.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
-->
<template>
  <div
    @contextmenu.prevent=""
    class="no-select"
    style="margin-left: auto; margin-right: auto"
  >
    <div class="shadow" style="border-width: 0rem">
      <div class="ltpp-list-box">
        <div class="search shadow">
          <el-input
            style="font-size: 1rem"
            placeholder="请输入需要搜索的问题"
            v-model.lazy="key"
            @keyup.enter.native="
              isinit = false;
              search();
            "
          >
            <el-button
              slot="append"
              icon="el-icon-search"
              @click="
                isinit = false;
                search();
              "
              >搜索</el-button
            >
          </el-input>
        </div>
      </div>
      <div class="ltpp-list-box">
        <div
          v-for="(tem, index) in data_list"
          :key="index"
          style="padding: 1rem"
        >
          <div style="height: 0.36rem"></div>
          <div style="display: flex; flex-direction: column; text-align: right">
            <div style="display: flex; flex-direction: row">
              <el-avatar
                style="height: 3.6rem; width: 3.6rem"
                :src="tem.headimage"
                alt=""
              ></el-avatar>
              <p
                @click="toUserPage(tem.userid)"
                style="
                  cursor: pointer;
                  font-size: 1.06rem;
                  font-weight: bold;
                  color: deepskyblue;
                  margin-top: 1rem;
                  margin-left: 1rem;
                "
              >
                {{ tem.writer }}
              </p>
              <p
                style="
                  font-size: 1.06rem;
                  color: rgba(38, 205, 77, 1);
                  margin-top: 1.02rem;
                  margin-left: 1.06rem;
                "
              >
                提问于：{{ tem.time }}
              </p>
            </div>
            <div style="height: 0.6rem"></div>
            <div class="markdown-body">
              <mavon-editor
                class="md"
                :codeStyle="prop.codeStyle"
                :toolbars="toolbars"
                :value="tem.name || '<br>'"
                :subfield="prop.subfield"
                :defaultOpen="prop.defaultOpen"
                :toolbarsFlag="prop.toolbarsFlag"
                :ishljs="true"
                :editable="prop.editable"
                :scrollStyle="prop.scrollStyle"
                :boxShadow="prop.boxShadow"
                :tabSize="prop.tabSize"
                :fontSize="prop.fontSize"
                :externalLink="externalLink"
                :xssOptions="xss_options"
                :stripIgnoreTagBody="stripIgnoreTagBody"
                style="
                  color: #ebeef5;
                  min-height: 4rem;
                  max-height: 36rem;
                  border-width: 0rem;
                "
              >
              </mavon-editor>
              <div style="height: 0.6rem"></div>
              <p style="font-size: 1rem; color: deeppink">
                {{
                  !isNaN(parseFloat(tem.answer_num)) && isFinite(tem.answer_num)
                    ? '累计回答：' + tem.answer_num + '次'
                    : tem.answer_num
                }}
              </p>
              <div style="height: 0.6rem"></div>
              <el-button
                type="success"
                size="mini"
                class="el-icon-bell pulse-enter-active"
                @click="toOneQuestionPage(tem.id)"
                style="
                  font-size: 1rem;
                  width: 10rem;
                  text-align: center;
                  color: var(--ltpp-box-text-color);
                "
                >查看详情
              </el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from '../../../updateCompoents/urlencode';
const limit = 50;

export default {
  name: 'questionlist',
  data() {
    return {
      lock: false,
      isinit: false,
      key: '',
      xss_options: this.$SqsGlobal.xss_options,
      stripIgnoreTagBody: this.$SqsGlobal.strip_ignore_tag_body,
      data_list: [],
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
        imagelink: false, // 图片链接

        code: true, // code
        subfield: true, // 是否需要分栏
        fullscreen: false, // 全屏编辑
        readmodel: true, // 沉浸式阅读
        /* 1.3.5 */
        undo: true, // 上一步
        trash: false, // 清空
        save: false, // 保存（触发events中的save事件）
        /* 1.4.2 */
        navigation: false, // 导航目录
        help: false,
      },
      externalLink: {
        markdown_css: false,
        // 默认public文件夹下
        hljs_js: () => 'md/highlightjs/highlight.min.js',
        hljs_css: (css) => 'md/highlightjs/styles/' + css + '.min.css',
        hljs_lang: (lang) => 'md/highlightjs/languages/' + lang + '.min.js',
        katex_css: () => 'md/katex/katex.min.css',
        katex_js: () => 'md/katex/katex.min.js',
      },
    };
  },
  created() {
    this.lock = false;
    this.getList();
  },
  activated() {
    this.lock = false;
    window.addEventListener('scroll', this.addlist);
  },
  deactivated() {
    this.lock = true;
    window.removeEventListener('scroll', this.addlist);
  },
  destroyed() {
    this.lock = true;
    window.removeEventListener('scroll', this.addlist);
  },
  methods: {
    initData() {
      this.data_list = [];
      let tem_list = [];
      for (let i = 0; i < limit; ++i) {
        tem_list.push(this.$SqsGlobal.question_list_data);
      }
      this.data_list = tem_list;
    },
    async addlist() {
      if (this.lock) {
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
      //滚动条到底部的条件
      if (!(scrollTop + windowHeight >= scrollHeight - 1 && scrollTop >= 100)) {
        return;
      }
      if (this.key) {
        this.search();
      } else {
        await this.getList();
      }
    },
    toUserPage(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: '/userpage',
          query: {
            path: urlencode(id, 'gbk'),
          },
        });
    },
    toOneQuestionPage(id) {
      id &&
        id != this.$SqsGlobal.loading_tips &&
        this.$router.push({
          path: '/onequestion',
          query: {
            path: urlencode(id, 'gbk'),
          },
        });
    },
    async getList() {
      if (this.lock) {
        return;
      }
      this.lock = true;
      if (!this.isinit) {
        this.initData();
      }
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Question/getList',
        portType: {
          process: '8793',
        },
        data: {
          question_id: this.data_list.length
            ? this.data_list[this.data_list.length - 1].id
            : 0,
        },
      }).catch((t) => {
        setTimeout(() => {
          this.lock = false;
        }, 360);
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.isinit = true;
        return;
      });
      if (res?.code == 1) {
        if (!this.isinit) {
          this.data_list = [];
        }
        this.data_list.push(...res?.data);
        if (res?.data && !res?.data?.length) {
          this.$msg({
            type: 'success',
            message: '没有更多啦！',
            duration: 1600,
            offset: 80,
          });
        }
      }
      setTimeout(() => {
        this.lock = false;
      }, 360);
      this.isinit = true;
    },
    async search() {
      if (this.lock) {
        return;
      }
      this.lock = true;
      if (!this.isinit) {
        this.initData();
      }
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Question/searchList',
        portType: {
          process: '8792',
        },
        data: {
          key: this.key,
          question_id: this.data_list.length
            ? this.data_list[this.data_list.length - 1].id
            : 0,
        },
      }).catch((t) => {
        setTimeout(() => {
          this.lock = false;
        }, 360);
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
        this.isinit = true;
        return;
      });
      if (res?.code == 1) {
        if (!this.isinit) {
          this.data_list = [];
        }
        this.data_list.push(...res?.data);
        if (res?.data && !res?.data?.length) {
          this.$msg({
            type: 'success',
            message: '没有更多啦！',
            duration: 1600,
            offset: 80,
          });
        }
      }
      setTimeout(() => {
        this.lock = false;
      }, 360);
      this.isinit = true;
    },
  },
  computed: {
    prop() {
      let data = {
        subfield: false, // 单双栏模式
        defaultOpen: 'preview', //edit： 默认展示编辑区域 ， preview： 默认展示预览区域
        editable: false,
        toolbarsFlag: false, //工具栏
        scrollStyle: true,
        codeStyle: 'atom-one-dark',
        boxShadow: false,
        ishljs: true,
        tabSize: 4,
        toolbarsBackground: 'rgba(0,0,0,0)',
        editorBackground: 'rgba(0,0,0,0)',
        previewBackground: 'rgba(0,0,0,0)',
        fontSize: '1.06rem',
        navigation: false,
      };
      return data;
    },
  },
};
</script>

<style scoped>
@import '../../../public/md/markdown/github-markdown.min.css';
</style>
