/* 
登录

 */
<template>
  <div @contextmenu.prevent="" class="big-div">
    <div class="divlogin">
      <el-form :model="LoginForm" ref="LoginForm" class="demo-LoginForm">
        <div style="height: 2rem"></div>
        <h2 class="logintitle">登录</h2>
        <div style="height: 2rem"></div>
        <el-form-item label="账号" label-width="14%">
          <el-input
            type="text"
            v-model.lazy="LoginForm.name"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
          ></el-input>
        </el-form-item>
        <el-form-item label="密码" label-width="14%" v-if="!dialog">
          <el-input
            type="password"
            show-password
            v-model.lazy="LoginForm.password"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
            @keyup.enter.native="submitForm()"
          ></el-input>
        </el-form-item>
        <el-form-item label="邮箱" label-width="14%" v-if="dialog">
          <el-input
            v-model.lazy="email"
            autocomplete="off"
            style="width: 90%; padding: 1rem, 1rem, 1rem, 1rem"
            @keyup.enter.native="sendPassword()"
          ></el-input>
        </el-form-item>
        <el-form-item>
          <div>
            <div style="display: inline; padding: 2rem 13rem 2rem 2rem">
              <el-button
                class="el-icon-s-unfold"
                type="text"
                style="color: rgb(255, 246, 84)"
                @click="register()"
                >没有账号？注册一个</el-button
              >
            </div>

            <el-button
              v-if="!dialog"
              class="el-icon-s-comment"
              style="color: rgb(255, 246, 84)"
              type="text"
              @click="dialog = true"
              >忘记密码？</el-button
            >
            <el-button
              v-if="dialog"
              class="el-icon-s-unfold"
              style="color: rgb(255, 246, 84)"
              type="text"
              @click="dialog = false"
              >登录</el-button
            >
          </div>
        </el-form-item>
        <el-form-item style="text-align: center" v-if="!dialog">
          <el-button class="bt" @click="submitForm()" :loading="isup"
            >登 陆</el-button
          >
        </el-form-item>
        <el-form-item style="text-align: center" v-if="dialog">
          <el-button class="bt" @click="sendPassword()" :loading="isup"
            >重 置 密 码</el-button
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
  name: "login",
  components: {
    myfooter: myfooter,
  },
  data() {
    return {
      isup: false,
      email: "",
      dialog: false,
      LoginForm: {
        name: "",
        password: "",
      },
    };
  },
  methods: {
    register() {
      this.$route.path != "/register" &&
        this.$router.replace({
          path: "/register",
          replace: true,
        });
    },

    trueOnline() {
      this.$ajax({
        method: "get",
        url: "/User/trueOnline",
        portType: {
          process: "8793",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
    },
    async submitForm() {
      if (this.isup) {
        return;
      }
      this.isup = true;

      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Login/judgeLogin",
        portType: {
          process: "8799",
        },
        data: {
          name: this.LoginForm.name,
          password: this.LoginForm.password,
        },
      }).catch((t) => {
        this.isup = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == -1) {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: res.msg,
            duration: 2000,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      } else if (res.code == 1 || res.code == 2 || res.code == 3) {
        window.localStorage.setItem("authorization", res.authorization);
        window.localStorage.setItem("key", res.key);
        this.$store.commit("updateObj", { login: true });
        this.$store.commit("updateObj", { root: res.code == 3 });
        this.$store.commit("updateObj", { admin: res.code == 2 });
        this.trueOnline();
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 2600,
          offset: 80,
        });
        this.$route.path != "/homelist" &&
          this.$router.replace({
            path: "/homelist",
            replace: true,
          });
      } else {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: res.msg,
            duration: 2000,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      }
      this.isup = false;
    },
    resetForm() {
      this.LoginForm.name = "";
      this.LoginForm.password = "";
    },
    async sendPassword() {
      if (this.isup) {
        return;
      }
      this.isup = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Verification/sendPassword",
        portType: {
          process: "8799",
        },
        data: {
          name: this.LoginForm.name,
          to: this.email,
        },
      }).catch((t) => {
        this.isup = false;
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        setTimeout(() => {
          this.$msg({
            type: "success",
            message: res.msg,
            duration: 1600,
            offset: 80,
          });
          this.dialog = false;
          this.isup = false;
        }, 360);
        return;
      } else {
        setTimeout(() => {
          this.$msg({
            type: "error",
            message: res.msg,
            duration: 1600,
            offset: 80,
          });
          this.isup = false;
        }, 360);
        return;
      }
    },
  },
};
</script>
<style scoped>
::v-deep .el-input,
::v-deep .el-form-item__label,
::v-deep .el-input__inner {
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
  background-color: #e493d0;
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

.divlogin {
  display: flex;
  opacity: 1;
  position: absolute;
  left: 50%;
  top: 44%;
  transform: translate(-50%, -50%);
}

.logintitle {
  color: rgb(255, 246, 84);
  padding: 3rems, auto;
  text-align: center;
}
.demo-LoginForm {
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

.bt {
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
  /* 字母变大写 */
  text-transform: uppercase;
  /* 设置成胶囊状 */
  border-radius: 50px;
  /* 值为正数在上面显示，反之 */
  z-index: 1;
}
/* 设置发光 */

.bt:hover::before {
  animation: sun 8s infinite;
  color: #ffffffe6;
}
/* 鼠标经过产生的效果 */
.bt:hover {
  /* 产生8秒的效果，sun是名称*/
  animation: sun 8s infinite;
  color: #ffffffe6;
}
/* 设置流光 */
@keyframes sun {
  100% {
    /* 以x轴为基准向左移动4个自身大小 */
    background-position: -400% 0;
  }
}
</style>
