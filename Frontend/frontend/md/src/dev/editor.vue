<template>
  <div class="container">
    <div id="editor">
      <mavon-editor
        style="height: 100%"
        v-model="code"
        :codeStyle="codeStyle"
        :xssOptions="xssOptions"
      ></mavon-editor>
    </div>
    <div class="switch-code-style">
      <span>code style:</span>
      <select v-model="codeStyle">
        <option v-for="(val, key) in styles" :value="key">{{ key }}</option>
      </select>
    </div>
  </div>
</template>
<script>
import styles from "../lib/core/hljs/lang.hljs.css.js";

const code = "";

module.exports = {
  name: "editor",
  data: function () {
    return {
      codeStyle: "github",
      styles,
      code:
        '<span style="color:red;font-size:36px;">A</span> \n```' +
        code +
        "\n```",
      xssOptions: {
        whiteList: {},
      },
    };
  },
};
</script>
<style>
.container {
  margin: auto;
  width: 80%;
}
#editor {
  height: 580px;
}
.switch-code-style {
  margin-top: 10px;
  font-size: 16px;
}
</style>
