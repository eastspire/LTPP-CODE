<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-06-03 14:03:20
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-26 12:11:59
 * @FilePath: \LTPP-CODE\Frontend\src\components\myfooter.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by ${git_name_email}, All Rights Reserved. 
-->
<template>
  <div class="bottomIcpLink">
    <p class="footer">
      <!-- <img src="../assets/icp.png" style="width: 1rem; height: 1rem" alt="" />
      <a
        target="_blank"
        href="https://beian.miit.gov.cn/#/Integrated"
        class="link animate"
      >
        皖ICP备2021014886号-2 |
      </a>
      <a
        target="_blank"
        href="https://www.beian.gov.cn/portal/registerSystemInfo?recordcode=34060002040031"
        class="link animate"
      >
        皖公网安备34060002040031号
      </a> -->
      <a
        target="_blank"
        class="link animate"
        href="http://wpa.qq.com/msgrd?v=3&uin=1491579574&site=qq&menu=yes"
        >{{ footmeddage }}</a
      >
      <a @click="changeBackendNetworkUrl()" class="link animate">
        | 使用代理 |</a
      >
    </p>
  </div>
</template>

<script>
export default {
  date() {
    return {
      footmeddage: '',
      is_ssl: true,
    };
  },
  mounted() {
    this.initDevice();
  },
  created() {
    let date = new Date();
    let year = date.getFullYear();
    this.footmeddage = '©2021 - ' + year + ' LTPP版权所有';
  },
  methods: {
    changeBackendNetworkUrl() {
      this.$prompt('请输入代理后端地址', '提示', {
        distinguishCancelAndClose: true,
        confirmButtonText: '确定',
        cancelButtonText: '重置',
        inputPattern:
          /^(https?:\/\/(([a-zA-Z0-9]+-?)+[a-zA-Z0-9]+\.)+(([a-zA-Z0-9]+-?)+[a-zA-Z0-9]+))(:\d+)?(\/.*)?(\?.*)?(#.*)?$/,
        inputErrorMessage: '代理后端地址不正确',
      })
        .then(({ value }) => {
          try {
            window.localStorage.setItem('backend_network_url', value);
          } catch (err) {}
          this.$store.commit('updateObj', { backend_network_url: value });
          this.$notice({
            title: '当前环境',
            dangerouslyUseHTMLString: false,
            message:
              this.$store.state.backend_network_url ==
              this.$store.state.default_backend_network_url
                ? '官方环境'
                : '代理环境',
            duration: 3600,
            offset: 80,
          });
        })
        .catch((action) => {
          if (action !== 'cancel') {
            this.$notice({
              title: '当前环境',
              dangerouslyUseHTMLString: false,
              message:
                this.$store.state.backend_network_url ==
                this.$store.state.default_backend_network_url
                  ? '官方环境'
                  : '代理环境',
              duration: 3600,
              offset: 80,
            });
            return;
          }
          try {
            window.localStorage.setItem(
              'backend_network_url',
              this.$store.state.default_backend_network_url
            );
          } catch (err) {}
          this.$store.commit('updateObj', {
            backend_network_url: this.$store.state.default_backend_network_url,
          });
          this.$notice({
            title: '当前环境',
            dangerouslyUseHTMLString: false,
            message:
              this.$store.state.backend_network_url ==
              this.$store.state.default_backend_network_url
                ? '官方环境'
                : '代理环境',
            duration: 3600,
            offset: 80,
          });
        });
    },
  },
};
</script>

<style></style>
