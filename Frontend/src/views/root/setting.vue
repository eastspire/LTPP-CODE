<!-- 设置 -->

<template>
  <div
    @contextmenu.prevent=""
    style="margin-left: auto; margin-right: auto"
    class="no-select"
  >
    <div class="shadow ltpp-list-box" style="border-width: 0rem">
      <div style="margin-left: 1.4rem; margin-right: 1.4rem">
        <div style="height: 0.8rem"></div>
        <div style="margin-left: 1rem; margin-right: 1rem">
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 0.5rem 0rem;
            "
          >
            是否开启注册通道
          </p>
          <el-switch
            v-model.lazy="resdata.canregister"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            @change="updatesetting()"
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
            是否开启登录通道
          </p>
          <el-switch
            v-model.lazy="resdata.canlogin"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            @change="updatesetting()"
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
            是否使用QQ邮件服务器
          </p>
          <el-switch
            v-model.lazy="resdata.useqqmail"
            :active-value="1"
            :inactive-value="0"
            active-text="是"
            inactive-text="否"
            @change="updatesetting()"
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
            是否开启登录邮件发送
          </p>
          <el-switch
            v-model.lazy="resdata.useemail"
            :active-value="1"
            :inactive-value="0"
            active-text="开启"
            inactive-text="关闭"
            @change="updatesetting()"
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
            是否是内网服务器
          </p>
          <el-switch
            v-model.lazy="resdata.offline"
            :active-value="1"
            :inactive-value="0"
            active-text="内网"
            inactive-text="公网"
            @change="updatesetting()"
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
            是否开启抖音视频保存本地
          </p>
          <el-switch
            v-model.lazy="resdata.douyin_save_file"
            :active-value="1"
            :inactive-value="0"
            active-text="是"
            inactive-text="否"
            @change="updatesetting()"
            active-color="#13ce66"
            inactive-color="#ff4949"
          >
          </el-switch>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            清空服务器缓存（保留单点登录缓存）
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="deleteSomeRedis()"
            >清空服务器缓存（保留单点登录缓存）</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            清空所有缓存（缓存异常时使用）
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="delredis()"
            >清空所有缓存</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            清空所有限速用户和IP的缓存
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="delredisaidip()"
            >清空所有限速用户和IP的缓存</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            更新文章图片链接
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="updateimage()"
            >更新文章图片链接</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            判题机安装
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="installJudgeSever()"
            >安装判题机</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            安装题库测试样例
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="installTest()"
            >安装题库测试样例</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            启动音乐后端程序
          </p>
          <el-button
            type="text"
            class="pulse-enter-active"
            style="font-size: 1.06rem; color: deeppink"
            @click="runmusic()"
            >启动音乐后端程序</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            前端服务器地址-协议+IP地址/域名:端口
          </p>

          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入前端服务器协议+IP地址/域名:端口"
            v-model.lazy="resdata.GLOBfronturl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            后端服务器地址-协议+IP地址/域名:端口
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入后端服务器协议+IP地址/域名:端口"
            v-model.lazy="resdata.GLOBlinuxurl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            SSH服务器地址-协议+IP地址/域名:端口
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入SSH服务器协议+IP地址/域名:端口"
            v-model.lazy="resdata.ssh_back_url"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            用户批量生成（仅支持数字类型的名称）
          </p>
          <el-input
            style="font-size: 1.06rem; width: 42%"
            placeholder="请输入起始数字"
            v-model.lazy="userbeginnum"
          >
          </el-input>
          <el-input
            style="font-size: 1.06rem; width: 42%; margin-left: 1%"
            placeholder="请输入终止数字（包含终止数字）"
            v-model.lazy="userendnum"
          >
          </el-input>
          <el-button
            type="success"
            size="medium"
            style="
              border-radius: 0.2rem;
              font-size: 1.06rem;
              width: 14%;
              overflow: hidden;
              margin-left: 1%;
              background-color: rgba(var(--ltpp-main-bk-color), 0.2)
              border-width: 0rem;
            "
            icon="el-icon-upload"
            :disabled="isadd"
            @click="largeadduser()"
            >生成</el-button
          >
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            socket服务器地址-协议+IP地址/域名:端口
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入在线课堂协议+IP地址/域名:端口"
            v-model.lazy="resdata.socketurl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            QQ邮件服务器-SMTP邮箱
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入QQ邮件服务器邮箱"
            v-model.lazy="resdata.smtp"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            QQ邮件服务器-SMTP授权码
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入QQ邮件服务器授权码"
            v-model.lazy="resdata.smtpkey"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            个人邮件服务器地址-协议+IP地址/域名:端口
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入个人邮件服务器地址-协议+IP地址/域名:端口"
            v-model.lazy="resdata.mysmtpurl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            个人邮件服务器-SMTP账号
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入SMTP邮箱"
            v-model.lazy="resdata.mysmtpname"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            个人邮件服务器-SMTP密码
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入SMTP授权码"
            v-model.lazy="resdata.mysmtppassword"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            课堂直播播放地址
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入直播页面播放地址链接"
            v-model.lazy="resdata.classurl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            后端音乐地址（例如：http://0.0.0.0:3000，请勿修改端口）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入后端音乐地址（例如：http://0.0.0.0:11111）"
            v-model.lazy="resdata.musicbkurl"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            用户代码最长运行时间（单位：MS）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入用户代码最长运行时间"
            v-model.lazy="resdata.idemaxtime"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            用户代码最大消耗内存（单位：MB）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入用户代码最大消耗内存"
            v-model.lazy="resdata.idemaxmemory"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            普通用户代码托管容量（MB）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入普通用户代码托管容量（MB）"
            v-model.lazy="resdata.usercloudfilememory"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            一段时间内请求限制次数（一段时间指的是：请求频率记录有效期）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入一段时间内请求限制次数"
            v-model.lazy="resdata.GLOBiplimit"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            一段时间内请求拉黑次数（一段时间指的是：请求频率记录有效期）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入一段时间内请求拉黑次数"
            v-model.lazy="resdata.GLOBipblack"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>
          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            请求频率记录有效期（单位：秒）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入请求频率记录有效期"
            v-model.lazy="resdata.GLOBiplimitTime"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            ChatGPT API接口地址
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入ChatGPT API接口地址"
            v-model.lazy="resdata.chatgpt_api_url"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            ChatGPT KEYS（使用换行/空格进行分隔）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入ChatGPT KEYS（使用换行/空格进行分隔）"
            v-model.lazy="resdata.chatgpt_keys"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认开始时间（当天零点开始的秒数）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认开始时间（当天零点开始的秒数）	"
            v-model.lazy="resdata.default_contest_begin_time"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认持续时间（单位秒）
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认持续时间（单位秒）"
            v-model.lazy="resdata.default_contest_duration"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认题目数目
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认题目数目"
            v-model.lazy="resdata.default_contest_problem_num"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认最小参赛人数
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认最小参赛人数"
            v-model.lazy="resdata.default_contest_min_people_num"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认最大参赛人数
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认最大参赛人数"
            v-model.lazy="resdata.default_contest_max_people_num"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            竞赛默认内容描述
          </p>
          <el-input
            type="textarea"
            autosize
            style="font-size: 1.06rem"
            placeholder="请输入竞赛默认内容描述"
            v-model.lazy="resdata.default_contest_content"
            @blur="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            抖音收藏URL下载链接
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入抖音收藏URL下载链接"
            v-model.lazy="resdata.douyin_listcollection_url"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            抖音Cookie
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入抖音Cookie"
            v-model.lazy="resdata.douyin_cookie"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            抖音每次爬取最大条数限制
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入抖音每次爬取最大条数限制"
            v-model.lazy="resdata.douyin_save_limit"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            抖音视频最大未更新秒数限制
          </p>
          <el-input
            type="password"
            show-password
            style="font-size: 1.06rem"
            placeholder="请输入抖音视频最大未更新秒数限制"
            v-model.lazy="resdata.douyin_noupdate_limit_seconds"
            @keyup.enter.native="updatesetting()"
          >
            <el-button
              slot="append"
              icon="el-icon-upload"
              @click="updatesetting()"
              >更新</el-button
            >
          </el-input>

          <p
            style="
              font-size: 1.06rem;
              text-align: left;
              font-weight: bold;
              margin: 1rem 0rem 1rem 0rem;
            "
          >
            IP黑名单
          </p>

          <el-input
            style="font-size: 1.06rem"
            placeholder="请输入需要搜索的IP名称"
            v-model.lazy="keyip"
            @keyup.enter.native="search()"
          >
            <el-button slot="append" icon="el-icon-search" @click="search()"
              >搜索</el-button
            >
          </el-input>
          <div style="height: 2rem"></div>
          <div style="text-align: center">
            <el-button
              type="text"
              icon="el-icon-circle-plus"
              class="pulse-enter-active"
              style="font-size: 1.06rem; margin: 0.4rem 10rem; color: red"
              @click="showadd = true"
              >添加IP黑名单</el-button
            >
            <el-button
              type="text"
              icon="el-icon-s-platform"
              class="pulse-enter-active"
              style="font-size: 1.06rem; margin: 0.4rem 10rem; color: red"
              @click="
                showlinux = true;
                lookbase();
              "
              >查看服务器信息</el-button
            >
          </div>
          <div style="height: 2rem"></div>
          <div v-if="iplist.length > 0">
            <el-table
              :cell-style="cellStyle"
              :header-cell-style="{
                color: '#FFFFFF',
                'font-size': '1.06rem',
              }"
              :data="iplist"
              style="width: 100%"
            >
              <el-table-column label="ip" width="400">
                <template slot-scope="scope">
                  <a
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: var(--ltpp-box-text-color);
                      color: #409eff;
                    "
                    >{{ scope.row.ip.substr(0, 20) }}</a
                  >
                </template>
              </el-table-column>
              <el-table-column label="用户名" width="400" align="center">
                <template slot-scope="scope">
                  <a
                    style="
                      font-weight: bold;
                      font-size: 1.06rem;
                      color: deeppink;
                    "
                    >{{ scope.row.username.substr(0, 20) }}</a
                  >
                </template>
              </el-table-column>
              <el-table-column label="删除" width="auto" align="center">
                <template slot-scope="scope">
                  <el-button
                    type="text"
                    class="pulse-enter-active"
                    style="
                      font-weight: bold;
                      font-size: 1rem;
                      color: red;
                      margin-right: 2rem;
                    "
                    @click="deleteip(scope.row.id)"
                  >
                    删除
                  </el-button>
                </template>
              </el-table-column>
            </el-table>

            <div style="height: 3.4rem"></div>
            <el-pagination
              background
              v-show="total"
              style="text-align: center"
              @size-change="handleSizeChange"
              @current-change="handleCurrentChange"
              :current-page="page"
              :page-sizes="[1, 2, 6, 10, 20, 50]"
              :page-size="limit"
              layout="total, sizes, prev, pager, next, jumper"
              :total="total"
            ></el-pagination>
          </div>
          <!-- IP对话框 -->
          <el-dialog
            title="添加IP黑名单"
            :visible="showadd"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
          >
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 0rem 0rem 1rem 0rem;
              "
            >
              添加IP黑名单
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入需要添加的IP名称"
              v-model.lazy="ip"
              @keyup.enter.native="addip()"
            ></el-input>
            <div style="height: 1rem"></div>
            <p
              style="
                font-size: 1.06rem;
                text-align: left;
                font-weight: bold;
                margin: 1rem 0rem 1rem 0rem;
              "
            >
              添加用户ID黑名单
            </p>
            <el-input
              style="font-size: 1.06rem"
              placeholder="请输入需要添加的用户ID"
              v-model.lazy="aid"
              @keyup.enter.native="addip()"
            ></el-input>

            <el-button
              class="pulse-enter-active"
              slot="append"
              icon="el-icon-search"
              @click="addip()"
              >添加</el-button
            >

            <div style="height: 2rem"></div>
            <div style="float: left">
              <el-button
                type="danger"
                @click="
                  addip();
                  showadd = false;
                "
                style="margin-left: 2rem; font-weight: bold"
                width="auto"
                class="el-icon-check pulse-enter-active"
                >确定</el-button
              >
            </div>
            <div style="text-align: right">
              <el-button
                type="primary"
                @click="showadd = false"
                style="margin-right: 2rem; font-weight: bold"
                class="el-icon-s-unfold pulse-enter-active"
                width="auto"
                >取消</el-button
              >
            </div>
            <div style="height: 1rem"></div>
          </el-dialog>

          <!-- 日志和服务器信息对话框 -->
          <el-dialog
            title="服务器信息展示"
            :visible.sync="showlinux"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
          >
            <div v-for="tem in linuxdata" :key="tem.index">
              <div v-for="tt in tem" :key="tt.index">
                <pre style="font-size: 1.06rem">{{ tt }}</pre>
              </div>
              <div style="height: 2rem"></div>
            </div>
            <div style="height: 2rem"></div>
            <div style="text-align: right">
              <el-button
                type="text"
                @click="showlinux = false"
                style="
                  margin-right: 2rem;
                  font-size: 1.06rem;
                  font-weight: bold;
                "
                class="el-icon-s-unfold pulse-enter-active"
                width="auto"
                >关闭</el-button
              >
            </div>
            <div style="height: 1rem"></div>
          </el-dialog>
          <el-dialog
            title="安装结果"
            :width="
              ($store.state.max_width / $store.state.now_width) * 100 + '%'
            "
            :visible.sync="isseeinstallres"
          >
            <pre>{{ installres }}</pre>
          </el-dialog>
          <div style="height: 4rem"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "setting",

  async activated() {
    this.userbeginnum = "";
    this.userendnum = "";
    this.isseeinstallres = false;
    await this.getsettinglist();
    this.ip = "";
    this.showadd = false;
    this.page = 1;
    this.issearch = false; //判断是否搜索，从而进行分页查找
    this.limit = 10;
    this.showlinux = false;
    this.problemList = [];
    await this.lookiplist();
  },

  data() {
    return {
      isadd: false,
      userbeginnum: "",
      userendnum: "",
      isseeinstallres: false,
      installres: "",
      resdata: {
        canregister: 1,
        canlogin: 1,
        useemail: 1,
        classurl: "",
        smtp: "",
        smtpkey: "",
        GLOBlinuxurl: "",
        GLOBipfrequency: "",
        GLOBipblack: "",
        GLOBiplimit: "",
      },
      iplist: [],
      keyip: "",
      limit: 10,
      total: 0,
      page: 1,
      issearch: false,
      ip: "",
      aid: 0,
      showadd: false,
      showlinux: false,
      linuxdata: [],
    };
  },
  methods: {
    async largeadduser() {
      if (this.isadd) {
        this.$msg({
          type: "error",
          message: "正在生成中，请稍等！",
          duration: 1600,
          offset: 80,
        });
        return;
      }
      this.isadd = true;
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/largeAddUser",
        portType: {
          process: "8797",
        },
        data: {
          userbeginnum: this.userbeginnum,
          userendnum: this.userendnum,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.isadd = false;
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    cellStyle({ row, rowIndex }) {
      let styleRes = {
        background: "rgba(var(--ltpp-light-color), 0.16) !important",
        height: "3.6rem !important",
        color: "chartreuse",
      };

      return styleRes;
    },
    async runmusic() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/runMusic",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async installTest() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/installTest",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async installJudgeSever() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/installJudgeSever",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async updatesetting() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/updateSetting",
        portType: {
          process: "8797",
        },
        data: {
          data: this.resdata,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
        sessionStorage.removeItem("musicbkurl");
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.getsettinglist();
    },
    async updateimage() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/updateImage",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async delredis() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/deleteRedis",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async deleteSomeRedis() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/deleteSomeRedis",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async delredisaidip() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/deleteRedisIdIp",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },

    async lookbase() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/linuxData",
        portType: {
          process: "8797",
        },
        data: {
          dowhat: "lookbase",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.linuxdata = res.data;
    },

    async lookiplist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/getIpBlackList",
        portType: {
          process: "8797",
        },
        data: {
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.iplist = res.data;
        this.total = res.allnum;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    async addip() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/addBlackIpUser",
        portType: {
          process: "8797",
        },
        data: {
          ip: this.ip,
          user_id: this.aid,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.ip = "";
      this.showadd = false;
      this.lookiplist();
    },
    async deleteip(id) {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/deleteBlack",
        portType: {
          process: "8797",
        },
        data: {
          delete_id: id,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.$msg({
          type: "success",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
      this.lookiplist();
    },

    async getsettinglist() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/getSettingList",
        portType: {
          process: "8797",
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      if (res.code == 1) {
        this.resdata = res.data;
      } else {
        this.$msg({
          type: "error",
          message: res.msg,
          duration: 1600,
          offset: 80,
        });
      }
    },
    handleCurrentChange(val) {
      this.page = val;
      if (this.issearch) {
        this.search();
      } else {
        this.lookiplist();
      }
    },

    handleSizeChange(val) {
      this.page = 1;
      this.limit = val;
      if (this.issearch) {
        this.search();
      } else {
        this.lookiplist();
      }
    },
    //搜索
    async keysearch() {
      const { data: res } = await this.$ajax({
        method: "post",
        url: "/Setting/searchIp",
        portType: {
          process: "8797",
        },
        data: {
          ip: this.keyip,
          page: this.page,
          limit: this.limit,
        },
      }).catch((t) => {
        this.$msg({
          type: "error",
          message: t,
          duration: 1600,
          offset: 80,
        });
      });
      this.iplist = res.data;
      this.total = res.allnum;
    },
    //搜索预处理
    search() {
      this.iplist = [];
      this.page = 1;
      if (this.keyip == "" || this.keyip == null || this.keyip == undefined) {
        this.issearch = false;
        this.lookiplist();
        return;
      }
      this.issearch = true;
      this.keysearch();
    },
  },
};
</script>

<style scoped>
::v-deep .el-table,
::v-deep .el-table__expanded-cell {
  background-color: transparent !important;
}
/* 表格内背景颜色 */
::v-deep .el-table th,
::v-deep .el-table tr,
::v-deep .el-table td {
  background-color: transparent !important;
}
::v-deep .el-table__row > td {
  border: none !important;
}
::v-deep .el-table::before {
  height: 0px !important;
}
::v-deep .el-table__cell,
.is-leaf {
  border: none !important;
}
</style>