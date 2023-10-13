<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-08-07 18:43:57
 * @LastEditors: 18855190718 1491579574@qq.com
 * @LastEditTime: 2023-08-07 22:13:16
 * @FilePath: \LTPP-CODE\Frontend\src\views\home\ide.vue
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by SQS, All Rights Reserved. 
-->
<template>
  <div v-if="show_ide">
    <Myide></Myide>
  </div>
</template>
<script>
import Myide from "../../components/myide.vue";
export default {
  name: "ide",
  components: {
    Myide,
  },
  data() {
    return {
      show_ide: true,
    };
  },
  activated() {
    this.show_ide = true;
  },
  deactivated() {
    this.show_ide = false;
  },
};
</script>
