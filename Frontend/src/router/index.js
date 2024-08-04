// 需要一次性加载
import Vue from 'vue';
import VueRouter from 'vue-router';
import Home from '../views/Home.vue';
import login from '../views/login.vue';
import register from '../views/register.vue';
import maintenance from '../views/maintenance.vue';
// home
import cloudfile from '../views/home/cloudfile.vue';
import oneproblemsolve from '../views/home/oneproblemsolve.vue';
import problemcode from '../views/home/problemcode.vue';
import staticfile from '../views/home/staticfile.vue';
import allarticle from '../views/home/allarticle.vue';
import classteach from '../views/home/classteach.vue';
import randomarticle from '../views/home/randomarticle.vue';
import music from '../views/home/music.vue';
import videocommunity from '../views/home/videocommunity.vue';
import user from '../views/home/user.vue';
import ide from '../views/home/ide.vue';
import oj from '../views/home/oj.vue';
import oneproblem from '../views/home/oneproblem.vue';
import onearticle from '../views/home/onearticle';
import contest from '../views/home/contest.vue';
import onecontest from '../views/home/onecontest.vue';
import dayproblem from '../views/home/dayproblem.vue';
import mycodehistory from '../views/home/mycodehistory.vue';
import allcodehistory from '../views/home/allcodehistory.vue';
import rank from '../views/home/rank.vue';
import userpage from '../views/home/userpage.vue';
import onequestion from '../views/home/onequestion.vue';
import onevideo from '../views/home/onevideo.vue';
import homelist from '../views/home/homelist.vue';
import chat from '../views/home/chat.vue';
import app from '../views/home/app.vue';
import goods from '../views/home/goods.vue';
import questionsheet from '../views/home/questionsheet.vue';
import onequestionsheet from '../views/home/onequestionsheet.vue';
import questionlist from '../views/home/questionlist.vue';
import sendquestion from '../views/home/sendquestion.vue';
// back
import lovearticle from '../views/back/lovearticle.vue';
import fans from '../views/back/fans.vue';
import myarticlemanage from '../views/back/myarticlemanage.vue';
import write from '../views/back/write.vue';
import follow from '../views/back/follow.vue';
import mydatamanage from '../views/back/mydatamanage.vue';
import myjoincontest from '../views/back/myjoincontest.vue';
import updateonearticle from '../views/back/updateonearticle.vue';
import mynotice from '../views/back/mynotice.vue';
import updateonequestion from '../views/back/updateonequestion.vue';
import myquestion from '../views/back/myquestion.vue';
import myjoinquestionsheet from '../views/back/myjoinquestionsheet.vue';
import myquestionsheetmanage from '../views/back/myquestionsheetmanage.vue';
import addquestionsheet from '../views/back/addquestionsheet.vue';
import updatequestionsheet from '../views/back/updatequestionsheet.vue';
import myappmamage from '../views/back/myappmamage.vue';
import fabulousvideo from '../views/back/fabulousvideo.vue';
import lovevideo from '../views/back/lovevideo.vue';
import mygoods from '../views/back/mygoods.vue';
import mylinuxmanage from '../views/back/mylinuxmanage.vue';
// admin
import oneproblemmanage from '../views/admin/oneproblemmanage.vue';
import problemmanage from '../views/admin/problemmanage.vue';
import addcontest from '../views/admin/addcontest.vue';
import updatacontest from '../views/admin/updatacontest.vue';
import managecontest from '../views/admin/managecontest.vue';
import usermanage from '../views/admin/usermanage.vue';
// root
import allquestionsheetmanage from '../views/root/allquestionsheetmanage.vue';
import linuxmanage from '../views/root/linuxmanage.vue';
import appmanage from '../views/root/appmanage.vue';
import noticemanage from '../views/root/noticemanage.vue';
import goodsmanage from '../views/root/goodsmanage.vue';
import photomanage from '../views/root/photomanage.vue';
import shortsentencemanage from '../views/root/shortsentencemanage.vue';
import videomanage from '../views/root/videomanage.vue';
import monitor from '../views/root/monitor.vue';
import setting from '../views/root/setting.vue';
import questionlistmanage from '../views/root/questionlistmanage.vue';
import userarticle from '../views/root/userarticle.vue';

Vue.use(VueRouter);

const routes = [
  {
    path: '*',
    redirect: '/homelist',
    meta: {
      title: '信息公告',
      keepAlive: true,
      need_login: true,
      index: 2,
    },
  },
  {
    path: '/home',
    name: 'Home',
    component: () => import('../views/Home.vue'),
    meta: {
      title: '首页',
      keepAlive: true,
      need_login: true,
      index: 1,
    },
    children: [
      {
        path: '/appmanage',
        name: 'appmanage',
        component: () => import('../views/root/appmanage.vue'),
        meta: {
          title: '应用管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/myappmamage',
        name: 'myappmamage',
        component: () => import('../views/back/myappmamage.vue'),
        meta: {
          title: '我的应用',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/app',
        name: 'app',
        component: () => import('../views/home/app.vue'),
        meta: {
          title: '应用市场',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/updateonequestion',
        name: 'updateonequestion',
        component: () => import('../views/back/updateonequestion.vue'),
        meta: {
          title: '更新问题',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/myjoinquestionsheet',
        name: 'myjoinquestionsheet',
        component: () => import('../views/back/myjoinquestionsheet.vue'),
        meta: {
          title: '我加入的题单',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/addquestionsheet',
        name: 'addquestionsheet',
        component: () => import('../views/back/addquestionsheet.vue'),
        meta: {
          title: '创建题单',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/updatequestionsheet',
        name: 'updatequestionsheet',
        component: () => import('../views/back/updatequestionsheet.vue'),
        meta: {
          title: '更新题单',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/myquestionsheetmanage',
        name: 'myquestionsheetmanage',
        component: () => import('../views/back/myquestionsheetmanage.vue'),
        meta: {
          title: '我的题单管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/allquestionsheetmanage',
        name: 'allquestionsheetmanage',
        component: () => import('../views/root/allquestionsheetmanage.vue'),
        meta: {
          title: '题单管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/linuxmanage',
        name: 'linuxmanage',
        component: () => import('../views/root/linuxmanage.vue'),
        meta: {
          title: '服务器管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/mygoods',
        name: 'mygoods',
        component: () => import('../views/back/mygoods.vue'),
        meta: {
          title: '我的商品',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/mylinuxmanage',
        name: 'mylinuxmanage',
        component: () => import('../views/back/mylinuxmanage.vue'),
        meta: {
          title: '云服务器管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/onequestion',
        name: 'onequestion',
        component: () => import('../views/home/onequestion.vue'),
        meta: {
          title: '问题详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/goods',
        name: 'goods',
        component: () => import('../views/home/goods.vue'),
        meta: {
          title: '商品',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/questionsheet',
        name: 'questionsheet',
        component: () => import('../views/home/questionsheet.vue'),
        meta: {
          title: '题单系统',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/onequestionsheet',
        name: 'onequestionsheet',
        component: () => import('../views/home/onequestionsheet.vue'),
        meta: {
          title: '题单详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/sendquestion',
        name: 'sendquestion',
        component: () => import('../views/home/sendquestion.vue'),
        meta: {
          title: '问答圈提问',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/myquestion',
        name: 'myquestion',
        component: () => import('../views/back/myquestion.vue'),
        meta: {
          title: '问答管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/questionlist',
        name: 'questionlist',
        component: () => import('../views/home/questionlist.vue'),
        meta: {
          title: '问答圈',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/questionlistmanage',
        name: 'questionlistmanage',
        component: () => import('../views/root/questionlistmanage.vue'),
        meta: {
          title: '问答圈管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/chat',
        name: 'chat',
        component: () => import('../views/home/chat.vue'),
        meta: {
          title: '聊天',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/onevideo',
        name: 'onevideo',
        component: () => import('../views/home/onevideo.vue'),
        meta: {
          title: '视频',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/homelist',
        name: 'homelist',
        component: () => import('../views/home/homelist.vue'),
        meta: {
          title: '信息公告',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/lovevideo',
        name: 'lovevideo',
        component: () => import('../views/back/lovevideo.vue'),
        meta: {
          title: '收藏的视频',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/fabulousvideo',
        name: 'fabulousvideo',
        component: () => import('../views/back/fabulousvideo.vue'),
        meta: {
          title: '点赞的视频',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/problemcode',
        name: 'problemcode',
        component: () => import('../views/home/problemcode.vue'),
        meta: {
          title: '题目提交历史',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/oneproblemsolve',
        name: 'oneproblemsolve',
        component: () => import('../views/home/oneproblemsolve.vue'),
        meta: {
          title: '题解社区',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/staticfile',
        name: 'staticfile',
        component: () => import('../views/home/staticfile.vue'),
        meta: {
          title: '文件详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/mynotice',
        name: 'mynotice',
        component: () => import('../views/back/mynotice.vue'),
        meta: {
          title: '消息通知',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/shortsentencemanage',
        name: 'shortsentencemanage',
        component: () => import('../views/root/shortsentencemanage.vue'),
        meta: {
          title: '短句管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/photomanage',
        name: 'photomanage',
        component: () => import('../views/root/photomanage.vue'),
        meta: {
          title: '首页侧边栏图片管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/setting',
        name: 'setting',
        component: () => import('../views/root/setting.vue'),
        meta: {
          title: '设置',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/videomanage',
        name: 'videomanage',
        component: () => import('../views/root/videomanage.vue'),
        meta: {
          title: '视频管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/monitor',
        name: 'monitor',
        component: () => import('../views/root/monitor.vue'),
        meta: {
          title: '监控管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/cloudfile',
        name: 'cloudfile',
        component: () => import('../views/home/cloudfile.vue'),
        meta: {
          title: '云盘',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/onecontest',
        name: 'onecontest',
        component: () => import('../views/home/onecontest.vue'),
        meta: {
          title: '竞赛详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/contest',
        name: 'contest',
        component: () => import('../views/home/contest.vue'),
        meta: {
          title: '竞赛列表',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/myjoincontest',
        name: 'myjoincontest',
        component: () => import('../views/back/myjoincontest.vue'),
        meta: {
          title: '我的竞赛',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/updatacontest',
        name: 'updatacontest',
        component: () => import('../views/admin/updatacontest.vue'),
        meta: {
          title: '更新竞赛',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/addcontest',
        name: 'addcontest',
        component: () => import('../views/admin/addcontest.vue'),
        meta: {
          title: '添加竞赛',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/managecontest',
        name: 'managecontest',
        component: () => import('../views/admin/managecontest.vue'),
        meta: {
          title: '竞赛管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/oneproblem',
        name: 'oneproblem',
        component: () => import('../views/home/oneproblem.vue'),
        meta: {
          title: '题目详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/problemmanage',
        name: 'problemmanage',
        component: () => import('../views/admin/problemmanage.vue'),
        meta: {
          title: '题目管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/oj',
        name: 'oj',
        component: () => import('../views/home/oj.vue'),
        meta: {
          title: '程序在线评测系统',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/ide',
        name: 'ide',
        component: () => import('../views/home/ide.vue'),
        meta: {
          title: '在线编辑器',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/dayproblem',
        name: 'dayproblem',
        component: () => import('../views/home/dayproblem.vue'),
        meta: {
          title: '每日一题',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/userpage',
        name: 'userpage',
        component: () => import('../views/home/userpage.vue'),
        meta: {
          title: '用户主页',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/user',
        name: 'user',
        component: () => import('../views/home/user.vue'),
        meta: {
          title: '用户中心',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/follow',
        name: 'follow',
        component: () => import('../views/back/follow.vue'),
        meta: {
          title: '关注',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/usermanage',
        name: 'usermanage',
        component: () => import('../views/admin/usermanage.vue'),
        meta: {
          title: '用户管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/goodsmanage',
        name: 'goodsmanage',
        component: () => import('../views/root/goodsmanage.vue'),
        meta: {
          title: '商品管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/noticemanage',
        name: 'notice',
        component: () => import('../views/root/noticemanage.vue'),
        meta: {
          title: '公告管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/updateonearticle',
        name: 'updateonearticle',
        component: () => import('../views/back/updateonearticle.vue'),
        meta: {
          title: '文章更新',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/oneproblemmanage',
        name: 'oneproblemmanage',
        component: () => import('../views/admin/oneproblemmanage.vue'),
        meta: {
          title: '题目编辑',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/onearticle',
        name: 'onearticle',
        component: () => import('../views/home/onearticle.vue'),
        meta: {
          title: '文章详情',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/userarticle',
        name: 'userarticle',
        component: () => import('../views/root/userarticle.vue'),
        meta: {
          title: '用户文章',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/allarticle',
        name: 'allarticle',
        component: () => import('../views/home/allarticle.vue'),
        meta: {
          title: '文章广场',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/classteach',
        name: 'classteach',
        component: () => import('../views/home/classteach.vue'),
        meta: {
          title: '在线课堂',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/randomarticle',
        name: 'randomarticle',
        component: () => import('../views/home/randomarticle.vue'),
        meta: {
          title: '随机文章',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/music',
        name: 'music',
        component: () => import('../views/home/music.vue'),
        meta: {
          title: '音乐广场',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/videocommunity',
        name: 'videocommunity',
        component: () => import('../views/home/videocommunity.vue'),
        meta: {
          title: '短视频',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/mycodehistory',
        name: 'mycodehistory',
        component: () => import('../views/home/mycodehistory.vue'),
        meta: {
          title: '我的提交记录',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/allcodehistory',
        name: 'allcodehistory',
        component: () => import('../views/home/allcodehistory.vue'),
        meta: {
          title: '全站提交记录',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        path: '/rank',
        name: 'rank',
        component: () => import('../views/home/rank.vue'),
        meta: {
          title: '全站积分排名',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        name: 'lovearticle',
        path: '/lovearticle',
        component: () => import('../views/back/lovearticle.vue'),
        meta: {
          title: '收藏的文章',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        name: 'myarticlemanage',
        path: '/myarticlemanage',
        component: () => import('../views/back/myarticlemanage.vue'),
        meta: {
          title: '我的文章管理',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        name: 'write',
        path: '/write',
        component: () => import('../views/back/write.vue'),
        meta: {
          title: '发布文章',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
      {
        name: 'mydatamanage',
        path: '/mydatamanage',
        component: () => import('../views/back/mydatamanage.vue'),
        meta: {
          title: '个人信息更新',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },

      {
        name: 'fans',
        path: '/fans',
        component: () => import('../views/back/fans.vue'),
        meta: {
          title: '粉丝列表',
          keepAlive: true,
          need_login: true,
          index: 2,
        },
      },
    ],
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/login.vue'),
    meta: {
      title: '登录',
      keepAlive: true,
      need_login: true,
      index: 1,
    },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/register.vue'),
    meta: {
      title: '注册',
      keepAlive: true,
      need_login: true,
      index: 1,
    },
  },
  {
    path: '/maintenance',
    name: 'maintenance',
    component: () => import('../views/maintenance.vue'),
    meta: {
      title: '系统维护',
      keepAlive: true,
      need_login: true,
      index: 1,
    },
  },
];

let is_electron = false;
try {
  is_electron =
    typeof navigator !== 'undefined' &&
    navigator.userAgent.toLowerCase().indexOf('electron') !== -1;
} catch (err) {
  is_electron = false;
}

const router = new VueRouter({
  mode: is_electron ? 'hash' : 'history',
  base: process.env.BASE_URL,
  routes,
});

export default router;
