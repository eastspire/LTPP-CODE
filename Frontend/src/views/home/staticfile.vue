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
      :style="`height:${$store.state.no_scroll_height}vh;
          display: flex;
          justify-content: center;
          align-items: center;
          border-width: 0rem;
      `"
    >
      <video
        v-if="judgeIsType(url, 3)"
        :src="url"
        :style="`height:${$store.state.no_scroll_height * 0.68}vh; width: 100%`"
        preload
        controls
        controlslist="nodownload"
      ></video>
      <img
        style="width: 100%; height: 100%; object-fit: cover"
        v-else-if="judgeIsType(url, 7)"
        :src="url"
        alt=""
      />
      <audio
        v-else-if="judgeIsType(url, 2)"
        :src="url"
        preload
        controls
        controlslist="nodownload"
      ></audio>
      <div v-else>
        <h3>{{ $SqsGlobal.no_support_file_tips }}</h3>
      </div>
    </div>
  </div>
</template>

<script>
import urlencode from '../../../updateCompoents/urlencode';
export default {
  name: 'staticfile',
  activated() {
    this.url = '';
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
    this.url = urlencode.decode(this.$route.query.path, 'gbk');
  },
  deactivated() {
    this.url = '';
  },

  data() {
    return {
      url: window?.location?.href,
    };
  },
};
</script>
