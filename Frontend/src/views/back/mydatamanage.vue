/* 个人信息修改 */
<template>
  <div @contextmenu.prevent="" style="margin-left: auto; margin-right: auto">
    <div
      class="shadow ltpp-list-box"
      style="border-width: 0rem; height: 6rem; width: 100%"
    >
      <div style="float: left">
        <el-avatar
          :src="$store.state.headimage"
          style="height: 4rem; width: 4rem; margin: 1rem 1rem"
        ></el-avatar>
      </div>
      <div style="float: left">
        <button
          class="pulse-enter-active shadow"
          style="
            font-size: 1rem;
            margin: 2rem 0.6rem;
            color: aqua;
            border-width: 0rem;
            border-radius: 2rem;
            width: 14rem;
            height: 2rem;
            cursor: pointer;
            border-radius: 0.46rem;
            border-color: rgba(var(--ltpp-main-bk-color), 0.8);
            background-color: rgba(var(--ltpp-main-bk-color), 0.8);
          "
          @click="useqqheadimage()"
        >
          点击使用邮箱头像
        </button>
      </div>
    </div>
    <div style="height: 1rem; clear: both"></div>
    <div class="shadow ltpp-list-box" style="border-width: 0rem">
      <div style="margin-left: 1.6rem; margin-right: 1.6rem">
        <div style="height: 1.6rem"></div>
        <span
          v-show="userdata.money && userdata.money >= 0"
          class="my-span shadow"
          style="
            font-size: 1rem;
            color: aqua;
            border-width: 0.1rem;
            border-radius: 2rem;
            border-color: var(--ltpp-main-text-color);
            padding: 0.6rem;
            border-radius: 0.46rem;
            border-color: rgba(var(--ltpp-main-bk-color), 0.8);
            background-color: rgba(var(--ltpp-main-bk-color), 0.8);
          "
        >
          {{
            userdata.money
              ? userdata.money < 0
                ? '欠费：' + Math.abs(userdata.money) + ' 学虫币'
                : '余额：' + Math.abs(userdata.money) + ' 学虫币'
              : '加载中'
          }}
        </span>
        <div style="height: 1.6rem"></div>
        <p style="font-size: 1.06rem; text-align: left; font-weight: bold">
          头像
        </p>
        <el-upload
          class="upload-demo"
          ref="upload_headimage"
          :headers="head"
          :action="headimage"
          :on-success="headimageupload"
          :on-error="resetHeadImageFileList"
          list-type="picture"
        >
          <el-button
            type="text"
            style="font-size: 1.06rem; color: deeppink; margin-top: 0.6rem"
            size="mini"
            class="pulse-enter-active"
            >上传头像</el-button
          >

          <div
            style="color: var(--ltpp-box-text-color)"
            slot="tip"
            class="el-upload__tip"
          >
            仅允许上传jpg/png/jpeg/gif文件
          </div>
        </el-upload>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          账号/用户名
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.name"
          placeholder="请输入用户名"
        />
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          密码
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.password"
          placeholder="请输入密码"
        />

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          邮箱
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.email"
          placeholder="请输入邮箱"
        />
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          图片保存方式
        </p>
        <el-switch
          v-model.lazy="userdata.image_use_remote"
          :active-value="1"
          :inactive-value="0"
          active-text="远程地址"
          inactive-text="BASE64"
          @change="updata()"
          active-color="#13ce66"
          inactive-color="#ff4949"
        >
        </el-switch>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          系统通知
        </p>
        <el-switch
          v-model.lazy="userdata.open_system_notice"
          :active-value="1"
          :inactive-value="0"
          active-text="开启"
          inactive-text="关闭"
          @change="updata()"
          active-color="#13ce66"
          inactive-color="#ff4949"
        >
        </el-switch>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          学号
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.student_number"
          placeholder="请输入学号"
        />

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          入学年份
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.enrollment_year"
          placeholder="请输入入学年份"
        />

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          学校
        </p>
        <el-autocomplete
          v-model.lazy="key_search_school"
          :fetch-suggestions="querySearchSchoolAsync"
          placeholder="请输入学校"
          @select="handleSelectSchool"
        ></el-autocomplete>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          学院
        </p>
        <el-autocomplete
          v-model.lazy="key_search_college"
          :fetch-suggestions="querySearchCollegeAsync"
          placeholder="请输入学院"
          @select="handleSelectCollege"
        ></el-autocomplete>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          专业
        </p>
        <el-autocomplete
          v-model.lazy="key_search_subject"
          :fetch-suggestions="querySearchSubjectAsync"
          placeholder="请输入专业"
          @select="handleSelectSubject"
        ></el-autocomplete>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          班级
        </p>
        <el-autocomplete
          v-model.lazy="key_search_class"
          :fetch-suggestions="querySearchClassAsync"
          placeholder="请输入班级"
          @select="handleSelectClass"
        ></el-autocomplete>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          网易云uid（用户id）
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.musicuid"
          placeholder="请输入网易云uid（用户id）"
        />

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          网易云我喜欢的列表id
        </p>
        <el-input
          style="font-size: 1.06rem"
          v-model.lazy="userdata.musiclovelistid"
          placeholder="请输入网易云我喜欢的列表id"
        />

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          个性签名
        </p>
        <el-input
          type="textarea"
          autosize
          placeholder="请输入个性签名"
          style="font-size: 1rem"
          v-model.lazy="userdata.mysay"
        >
        </el-input>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          CSS自定义配置
        </p>
        <el-input
          type="textarea"
          autosize
          placeholder="请输入CSS自定义配置"
          style="font-size: 1rem"
          v-model.lazy="userdata.root_css"
        >
        </el-input>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          性别
        </p>
        <el-select
          :popper-append-to-body="false"
          v-model.lazy="userdata.sex"
          placeholder="请选择"
          style="
            width: 10%;
            padding: 1rem, 1rem, 1rem, 1rem;
            font-size: 1.06rem;
          "
        >
          <el-option
            label="男"
            value="男"
            style="font-size: 1.06rem"
          ></el-option>
          <el-option
            label="女"
            value="女"
            style="font-size: 1.06rem"
          ></el-option>
        </el-select>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          图片背景
        </p>
        <el-upload
          ref="upload_bkimage"
          class="upload-demo"
          :headers="head"
          :action="bkimage"
          :on-success="bkimageupload"
          :on-error="resetBkImageFileList"
          list-type="picture"
        >
          <el-button
            type="text"
            style="font-size: 1.06rem; color: deeppink"
            size="mini"
            class="pulse-enter-active"
            >上传图片背景</el-button
          >
          <div
            slot="tip"
            class="el-upload__tip"
            style="color: var(--ltpp-box-text-color)"
          >
            仅允许上传jpg/png/jpeg/gif文件
          </div>
        </el-upload>
        <br />
        <div>
          <el-button
            type="text"
            style="font-size: 1.06rem; color: deeppink"
            size="mini"
            @click="resetbkimg()"
            class="pulse-enter-active"
            >使用默认背景</el-button
          >
        </div>
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          视频背景
        </p>
        <el-upload
          ref="upload_video"
          class="upload-demo"
          :headers="head"
          :action="videoimage"
          :on-success="videoupload"
          :on-error="resetVideoFileList"
        >
          <el-button
            type="text"
            style="font-size: 1.06rem; color: deeppink"
            size="mini"
            class="pulse-enter-active"
            >上传视频背景</el-button
          >
          <div
            slot="tip"
            class="el-upload__tip"
            style="color: var(--ltpp-box-text-color)"
          >
            仅允许上传MP4文件
          </div>
        </el-upload>
        <br />
        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          注册时间
        </p>
        <div style="font-size: 1.06rem">
          {{ this.userdata['registertime'] }}
        </div>

        <p
          style="
            font-size: 1.06rem;
            text-align: left;
            font-weight: bold;
            margin: 1rem 0rem 0.5rem 0rem;
          "
        >
          最近在线
        </p>
        <div style="font-size: 1.06rem">
          {{ userdata['lastlogin'] }}
        </div>

        <div style="height: 2rem"></div>
        <div style="text-align: right">
          <button
            v-show="!isup"
            class="pulse-enter-active shadow"
            style="
              font-size: 1.06rem;
              color: aqua;
              cursor: pointer;
              border-width: 0.1rem;
              border-radius: 2rem;
              border-color: var(--ltpp-main-text-color);
              width: 8rem;
              height: 2.6rem;
              border-radius: 0.46rem;
              border-color: rgba(var(--ltpp-main-bk-color), 0);
              background-color: rgba(var(--ltpp-main-bk-color), 0.8);
            "
            @click="updata()"
          >
            更新信息
          </button>
        </div>
        <div style="height: 6rem"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'mydatamanage',
  async activated() {
    this.isup = false;
    this.linuxurl = window.localStorage.getItem('linuxurl');
    this.headimage = this.linuxurl + '/User/saveheadimage';
    this.bkimage = this.linuxurl + '/User/saveBkImage';
    this.videoimage = this.linuxurl + '/User/saveVideoBkImage';
    this.head = {
      Authorization: 'Bearer ' + window.localStorage.getItem('authorization'),
      Key: window.localStorage.getItem('key'),
      Requestid: this.Base64Encode(new Date().getTime()),
    };
    this.requestid_timer = setInterval(() => {
      this.head.Requestid = this.Base64Encode(new Date().getTime());
    }, 1000);
    this.userdata.image_use_remote = this.$store.state.image_use_remote;
    this.userdata.open_system_notice = this.$store.state.open_system_notice;
    if (!this.linuxurl) {
      await this.getlinuxurl();
    }
    await this.loaddata();
  },
  deactivated() {
    clearInterval(this.requestid_timer);
    this.requestid_timer = null;
  },
  data() {
    return {
      requestid_timer: null,
      isup: false,
      userdata: [],
      head: {
        authorization: 'Bearer ' + window.localStorage.getItem('authorization'),
        key: window.localStorage.getItem('key'),
        Requestid: this.Base64Encode(new Date().getTime()),
      },
      linuxurl: window?.location?.href,
      bkimage: '',
      videoimage: '',
      headimage: '',
      timeout: null,
      key_search_school: '',
      key_search_college: '',
      key_search_subject: '',
      key_search_class: '',
    };
  },

  methods: {
    async querySearchSchoolAsync(queryString, cb) {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/School/keySearch',
        portType: {
          process: '8793',
        },
        data: {
          key: this.key_search_school,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        res?.data.forEach((t) => {
          t.value = t.name;
        });
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          cb(res?.data);
        }, 666);
      }
    },
    handleSelectSchool(data) {
      this.userdata['school'] = data.name;
    },

    async querySearchCollegeAsync(queryString, cb) {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/College/keySearch',
        portType: {
          process: '8793',
        },
        data: {
          key: this.key_search_college,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        res?.data.forEach((t) => {
          t.value = t.name;
        });
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          cb(res?.data);
        }, 666);
      }
    },
    handleSelectCollege(data) {
      this.userdata['college'] = data.name;
    },

    async querySearchSubjectAsync(queryString, cb) {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Subject/keySearch',
        portType: {
          process: '8793',
        },
        data: {
          key: this.key_search_subject,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        res?.data.forEach((t) => {
          t.value = t.name;
        });
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          cb(res?.data);
        }, 666);
      }
    },
    handleSelectSubject(data) {
      this.userdata['subject'] = data.name;
    },

    async querySearchClassAsync(queryString, cb) {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/Classes/keySearch',
        portType: {
          process: '8793',
        },
        data: {
          key: this.key_search_class,
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        res?.data.forEach((t) => {
          t.value = t.name;
        });
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
          cb(res?.data);
        }, 666);
      }
    },
    handleSelectClass(data) {
      this.userdata['class'] = data.name;
    },
    resetHeadImageFileList() {
      try {
        this.$refs.upload_headimage.clearFiles();
      } catch (err) {}
    },
    resetVideoFileList() {
      try {
        this.$refs.upload_video.clearFiles();
      } catch (err) {}
    },
    resetBkImageFileList() {
      try {
        this.$refs.upload_bkimage.clearFiles();
      } catch (err) {}
    },
    headimageupload(res) {
      this.resetHeadImageFileList();
      if (res?.code == 1) {
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$store.commit('updateObj', { headimage: res.url });
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    bkimageupload(res) {
      this.resetBkImageFileList();
      if (res?.code == 1) {
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$store.commit('updateObj', { bkimage: res.url });
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    videoupload(res) {
      this.resetVideoFileList();
      if (res?.code == 1) {
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
        this.$store.commit('updateObj', { bkvideo: res.url });
        this.$router.go(0);
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async resetbkimg() {
      await this.$ajax({
        method: 'post',
        url: '/User/resetBkImage',
        portType: {
          process: '8793',
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.$msg({
        type: 'success',
        message: '操作成功',
        duration: 1600,
        offset: 80,
      });
      this.$store.commit('updateObj', { bkimage: '' });
      this.$store.commit('updateObj', { bkvideo: '' });
    },
    async useqqheadimage() {
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/User/useQqHeadimage',
        portType: {
          process: '8793',
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res?.code == 1) {
        this.$msg({
          type: 'success',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: 'error',
          message: res?.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.$store.commit('updateObj', { headimage: res.url });
    },
    async getlinuxurl() {
      const res = await this.getBackurl();
      this.linuxurl = res;
      this.headimage = res + '/User/saveHeadImage';
      this.bkimage = res + '/User/saveBkImage';
      this.videoimage = res + '/User/saveVideoBkImage';
    },
    async loaddata() {
      const { data: res } = await this.$ajax({
        method: 'get',
        url: '/User/loadSelfData',
        portType: {
          process: '8793',
        },
      }).catch((t) => {
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.userdata = res?.data;
      this.key_search_school = res?.data['school'];
      this.key_search_college = res?.data['college'];
      this.key_search_subject = res?.data['subject'];
      this.key_search_class = res?.data['class'];
    },
    async updata() {
      if (this.isup) {
        return;
      }
      this.isup = true;
      this.$store.commit('updateObj', {
        image_use_remote: this.userdata?.image_use_remote,
      });
      this.$store.commit('updateObj', {
        open_system_notice: this.userdata?.open_system_notice,
      });
      const { data: res } = await this.$ajax({
        method: 'post',
        url: '/User/updateUser',
        portType: {
          process: '8793',
        },
        data: {
          data: this.userdata,
        },
      }).catch((t) => {
        this.isup = false;
        this.$msg({
          type: 'error',
          message: t,
          duration: 1600,
          offset: 80,
        });
        return;
      });
      setTimeout(() => {
        // 重新获取CSS配置并生效
        this.getCss();
        if (res?.code == 1) {
          this.$msg({
            type: 'success',
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
        } else {
          this.$msg({
            type: 'error',
            message: res?.msg,
            duration: 1600,
            offset: 80,
          });
        }
        this.isup = false;
      }, 360);
    },
  },
};
</script>
