<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-22 09:02:59
 * @FilePath: \LTPP-CODE\Frontend\src\views\home\staticfile.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
-->
<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto; text-align: center"
  >
    <div
      class="shadow main-center-box-content"
      :style="`height:${$store.state.no_scroll_height}vh; `"
    >
      <iframe
        style="border-width: 0rem; min-height: 31.2rem"
        height="100%"
        width="100%"
        :src="url"
        scrolling="no"
        border="0"
        frameborder="no"
        framespacing="0"
        allowfullscreen="true"
      ></iframe>
    </div>
  </div>
</template>

<script>
import urlencode from "../../../updateCompoents/urlencode";
export default {
  name: "staticfile",
  activated() {
    this.url = "";
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
    this.url = urlencode.decode(this.$route.query.path, "gbk");
  },
  deactivated() {
    this.url = "";
  },

  data() {
    return {
      url: "",
    };
  },
};
</script>
