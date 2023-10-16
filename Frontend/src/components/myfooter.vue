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
      <img src="../assets/icp.png" style="width: 1rem; height: 1rem" alt="" />
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
      </a>
      <a
        target="_blank"
        class="link animate"
        href="http://wpa.qq.com/msgrd?v=3&uin=1491579574&site=qq&menu=yes"
        >{{ footmeddage }}</a
      >
      <a
        @click="choosePublic()"
        class="link animate"
        v-if="!$store.state.is_public_network && !is_ssl"
      >
        | 切换公网版本 |</a
      >
      <a
        @click="choosePrivate()"
        class="link animate"
        v-if="$store.state.is_public_network && !is_ssl"
      >
        | 切换内网版本 |</a
      >
    </p>
  </div>
</template>

<script>
export default {
  date() {
    return {
      footmeddage: "",
      is_ssl: true,
    };
  },
  mounted() {
    this.initDevice();
  },
  created() {
    let date = new Date();
    let year = date.getFullYear();
    this.footmeddage = "©2021 - " + year + " LTPP版权所有";
    if (window.location.protocol === "https:") {
      this.is_ssl = true;
    } else {
      this.is_ssl = false;
    }
  },
  methods: {
    choosePublic() {
      window.localStorage.setItem("is_public_network", 1);
      this.$store.commit("updateObj", { is_public_network: 1 });
      this.$notice({
        title: "通知",
        dangerouslyUseHTMLString: true,
        message: "环境已切换【公网服务器】",
        duration: 3600,
        offset: 80,
      });
    },
    choosePrivate() {
      window.localStorage.setItem("is_public_network", 0);
      this.$store.commit("updateObj", { is_public_network: 0 });
      this.$notice({
        title: "通知",
        dangerouslyUseHTMLString: true,
        message: "环境已切换【内网服务器】",
        duration: 3600,
        offset: 80,
      });
    },
  },
};
</script>
    
<style>
</style>