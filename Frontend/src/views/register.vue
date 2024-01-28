/* 

注册

 */

<template>
  <div @contextmenu.prevent="" class="big-div">
    <div class="divregister">
      <el-form
        :model="RegisterForm"
        ref="RegisterForm"
        class="demo-RegisterForm"
      >
        <div style="height: 2rem"></div>
        <h2 class="registertitle">注册</h2>
        <div style="height: 2rem"></div>
        <el-form-item label="账号" label-width="14%">
          <el-input
            type="text"
            v-model.lazy="RegisterForm.name"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
          ></el-input>
        </el-form-item>

        <el-form-item label="密码" label-width="14%">
          <el-input
            type="password"
            show-password
            v-model.lazy="RegisterForm.password"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
          ></el-input>
        </el-form-item>

        <el-form-item label="邮箱" label-width="14%">
          <el-input
            type="text"
            v-model.lazy="RegisterForm.email"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
          ></el-input>
        </el-form-item>

        <el-form-item label="验证码" label-width="14%">
          <el-input
            type="text"
            v-model.lazy="RegisterForm.code"
            autocomplete="off"
            style="width: 59%; margin: 1rem, 1rem, 1rem, 1rem"
            @keyup.enter.native="submitForm()"
          ></el-input
          >&nbsp;
          <el-button class="bt1" @click="send()" :loading="isup"
            >验证码</el-button
          >
        </el-form-item>
        <el-form-item>
          <div>
            <div style="display: inline; padding: 2rem 13rem 2rem 2rem">
              <el-button
                class="el-icon-s-unfold"
                type="text"
                style="color: rgb(255, 246, 84)"
                @click="login()"
                >已有账号？去登录</el-button
              >
            </div>
          </div>
        </el-form-item>
        <el-form-item style="text-align: center">
          <el-button @click="submitForm()" class="bt2" :loading="isup"
            >注 册</el-button
          >
        </el-form-item>
        <div style="height: 0.46rem"></div>
      </el-form>
    </div>
    <myfooter></myfooter>
  </div>
</template>

<script>
import myfooter from "../components/myfooter.vue";
export default {
  name: "register",
  components: {
    myfooter: myfooter,
  },
  created() {
    this.getDate();
  },
  mounted() {
    this.initDevice();
  },
  data() {
    return {
      isup: false,
      footmeddage: "",
      RegisterForm: {
        name: "",
        password: "",
        value: "",
        email: "",
        code: "", //验证码
      },
      value: "男",
    };
  },

  methods: {
    getDate() {
      let date = new Date();
      let year = date.getFullYear();
      this.footmeddage = "©2021 - " + year + " LTPP版权所有";
    },
    login() {
      this.$route.path != "/login" &&
        this.$router.replace({
          path: "/login",
          replace: true,
        });
    },

    async submitForm() {
      if (this.isup) {
        return;
      }
      this.isup = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Register/judgeRegister",
        portType: {
          process: "8800",
        },
        data: {
          name: this.RegisterForm.name,
          password: this.RegisterForm.password,
          code: this.RegisterForm.code,
          sex: this.value,
          email: this.RegisterForm.email,
        },
      }).catch((t) => {
        this.isup = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });

      if (res?.code == 500) {
        this.isup = false;
        this.$msg({
          type: "error",
          message: "注册失败",
          duration: 2000,
          offset: 80,
        });
        return;
      }
      if (res?.code == 1) {
        setTimeout(() => {
          this.$msg({
            type: "success",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        this.login();
      } else {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
      }
    },

    async send() {
      if (this.isup) {
        return;
      }
      this.isup = true;
      if (this.RegisterForm.email == "" || this.RegisterForm.name == "") {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: "用户名和邮箱不能为空",
            duration: 2000,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      }
      if (this.RegisterForm.name == "root") {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: "禁止注册root账号",
            duration: 2000,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      }

      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Verification/send",
        portType: {
          process: "8800",
        },
        data: {
          name: this.RegisterForm.name,
          to: this.RegisterForm.email,
        },
      }).catch((t) => {
        this.isup = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });

      if (res?.code == 500) {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: "邮箱不存在",
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      } else if (res?.code == 1) {
        setTimeout(() => {
          this.$msg({
            type: "success",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
      } else if (res?.code == 0) {
        setTimeout(() => {
          this.$notice({
            title: "消息",
            dangerouslyUseHTMLString: true,
            message: res?.msg,
            duration: 0,
            offset: 80,
          });
          this.isup = false;
        }, 360);
      } else {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
      }
    },
  },
};
</script>
<style scoped>
.el-input,
.el-form-item__label,
.el-input__inner {
  color: rgb(255, 246, 84) !important;
  background-color: rgba(var(--ltpp-main-bk-color), 0) !important;
  border-color: rgb(233, 103, 194) !important;
  border-radius: 0.2rem !important;
  resize: none !important;
  -webkit-touch-callout: none !important; /* iOS Safari */
  -webkit-user-select: none !important; /* Chrome/Safari/Opera */
  -khtml-user-select: none !important; /* Konqueror */
  -moz-user-select: none !important; /* Firefox */
  -ms-user-select: none !important; /* Internet Explorer/Edge */
  user-select: none !important; /* Non-prefixed version, currently not supported by any browser */
}

.big-div {
  margin: 0;
  width: 100vw;
  min-height: 100vh;
  background-color: var(--ltpp-main-color);
  background-image: radial-gradient(
      closest-side,
      rgba(235, 105, 78, 1),
      rgba(235, 105, 78, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(243, 11, 164, 0.36),
      rgba(243, 11, 164, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(254, 234, 131, 1),
      rgba(254, 234, 131, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(170, 142, 245, 1),
      rgba(170, 142, 245, 0)
    ),
    radial-gradient(
      closest-side,
      rgba(248, 192, 147, 1),
      rgba(248, 192, 147, 0)
    );
  background-size: 130vmax 130vmax, 80vmax 80vmax, 90vmax 90vmax,
    110vmax 110vmax, 90vmax 90vmax;

  background-position: -80vmax -80vmax, 60vmax -30vmax, 10vmax 10vmax,
    -30vmax -10vmax, 50vmax 50vmax;

  background-repeat: no-repeat;
  animation: 6s move linear infinite;
}
big-div::after {
  content: "";
  display: block;
  position: fixed;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

@keyframes move {
  0%,
  100% {
    background-size: 130vmax 130vmax, 80vmax 80vmax, 90vmax 90vmax,
      110vmax 110vmax, 90vmax 90vmax;

    background-position: -80vmax -80vmax, 60vmax -30vmax, 10vmax 10vmax,
      -30vmax -10vmax, 50vmax 50vmax;
  }

  25% {
    background-size: 100vmax 100vmax, 90vmax 90vmax, 100vmax 100vmax,
      90vmax 90vmax, 60vmax 60vmax;

    background-position: -60vmax -60vmax, 50vmax -40vmax, 0vmax 10vmax,
      -40vmax -20vmax, 40vmax 40vmax;
  }
  50% {
    background-size: 90vmax 90vmax, 100vmax 100vmax, 80vmax 80vmax,
      90vmax 90vmax, 60vmax 60vmax;

    background-position: -70vmax -70vmax, 40vmax -40vmax, 0vmax 10vmax,
      -50vmax -30vmax, 30vmax 30vmax;
  }
  75% {
    background-size: 80vmax 80vmax, 70vmax 70vmax, 80vmax 80vmax, 70vmax 70vmax,
      50vmax 50vmax;

    background-position: -60vmax -60vmax, 60vmax -30vmax, 10vmax 10vmax,
      -40vmax -40vmax, 50vmax 50vmax;
  }
}

.bt1 {
  width: 30%;
  border-width: 0rem;
  /* 实现渐变色，90deg表示一个角度开始 */
  background: linear-gradient(90deg, #f441a5, #ffeb3b, #03a9f4, #f441a5);
  /* 背景色放大 */
  background-size: 400%;
  /* 文本居中 */
  text-align: center;
  font-size: 1rem;
  /* 字体颜色 */
  color: #fff;

  /* 值为正数在上面显示，反之 */
  z-index: 1;
}
/* 设置发光 */

.bt1:hover::before {
  animation: sun 8s infinite;
}
/* 鼠标经过产生的效果 */
.bt1:hover {
  /* 产生8秒的效果，sun是名称*/
  animation: sun 8s infinite;
}

.bt2 {
  width: 40%;
  border-width: 0rem;
  /* 实现渐变色，90deg表示一个角度开始 */
  background: linear-gradient(90deg, #f441a5, #ffeb3b, #03a9f4, #f441a5);
  /* 背景色放大 */
  background-size: 400%;

  /* 文本居中 */
  text-align: center;
  font-size: 1.06rem;
  /* 字体颜色 */
  color: #fff;

  border-radius: 50px;
  /* 值为正数在上面显示，反之 */
  z-index: 1;
}
/* 设置发光 */

.bt2:hover::before {
  animation: sun 8s infinite;
}
/* 鼠标经过产生的效果 */
.bt2:hover {
  animation: sun 8s infinite;
}
/* 设置流光 */
@keyframes sun {
  100% {
    /* 以x轴为基准向左移动4个自身大小 */
    background-position: -400% 0;
  }
}

.registertitle {
  color: rgb(255, 246, 84);
  padding: 3rem, auto;
  text-align: center;
}
.divregister {
  display: flex;
  opacity: 1;
  position: absolute;
  left: 50%;
  top: 44%;
  transform: translate(-50%, -50%);
}

.demo-RegisterForm {
  border-radius: 1rem;
  background-clip: padding-box;
  width: 30rem;
  height: auto;
  /*margin: 0,auto;外边距 上下，左右 */
  margin: auto;
  padding: 1rem, 1rem, 1rem, 1rem;
  background-color: rgba(232, 107, 195, 0.56);
  border: 0.4rem;
  box-shadow: 0 0 1.6rem rgba(232, 107, 195, 1);
}
</style>
