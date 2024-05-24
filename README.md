<!--
 * @Author: 18855190718 1491579574@qq.com
 * @Date: 2023-01-12 12:38:58
 * @LastEditors: wmzn-ltpp 1491579574@qq.com
 * @LastEditTime: 2023-11-11 20:54:38
 * @FilePath: \LTPP-CODE\README.md
 * @Description: Email:1491579574@qq.com
 * QQ:1491579574
 * Copyright (c) 2023 by 18855190718 1491579574@qq.com, All Rights Reserved. 
-->

# LTPP在线开发平台

> 项目前端基于Vue.js2 + VueX + EventBus + WebWorker + Echarts + ElementUI + Animate.css + Electron + Flutter + Rust + Tauri开发，后端基于Webman + GatewayWorker开发，项目运行在Docker环境，其中Flutter用于APP开发，Electron和Tauri用于客户端开发

## 注意事项

### 域名（根据实际内网IP进行修改）

* Mysql域名（mysql.ltpp.vip）修改本地host文件映射即可
* Redis域名（redis.ltpp.vip）修改本地host文件映射即可
* LTPP-SSH域名（ssh.ltpp.vip）修改本地host文件映射即可
* Clash域名（clash.ltpp.vip）修改本地host文件映射即可

```sh
127.0.0.1       mysql.ltpp.vip
127.0.0.1       redis.ltpp.vip
127.0.0.1       ssh.ltpp.vip
127.0.0.1       clash.ltpp.vip
```

### 端口（请勿修改）

* 1236（内网|即时通讯系统注册，可忽略）
* 3000（公网|音乐|开启SSL）
* 4466（内网|Mysql）
* 6379（内网|Redis）
* 40025（公网|邮箱系统）
* 40080（公网|直播推流前端访问地址）
* 41935（公网|直播推流）
* 47272（内网|后端|即时通讯|开启SSL）
* 48787（内网|后端|开启SSL）

### PHP插件

> 如果使用机器人接口需要卸载swoole插件

### Docker使用

> 若windows使用docker中自带的网络连接主机数据库，项目的配置文件的数据库ip使用host.docker.internal

### Redis数据库

> Redis数据库共36个（无密码）

| Redis数据库编号 | 功能 |
| --- | --- |
| 0   |黑名单|
| 1   |请求限速|
| 2   |验证码限速|
| 3   |短句限速|
| 4   |竞赛相关|
| 5   |设置|
| 6   |判题机|
| 7   |发布问题限速|
| 8   |用户信息|
| 9   |登录限速|
| 10  |发布文章限速|
| 11  |保存浏览器信息|
| 12  |在线课堂|
| 13  |注册验证码|
| 14  |单点登录|
| 15  |用户更新锁|
| 16  |私聊的未读消息数目|
| 17  |群聊的未读消息数目|
| 18  |缓存的css以及js|
| 19  |消息队列|
| 20  |群信息缓存|
| 21  |竞赛信息缓存|
| 22  |题单信息缓存|
| 23  |404页面缓存|
| 24  |竞赛排名计算缓冲区|
| 25  |文章信息|
| 26  |题库信息|
| 27  |机器人已参赛并已经提交代码的竞赛ID|
| 28  |竞赛代码缓存|
| 29  |代码结果缓存|
| 30  |文件系统缓存|
| 31  |题库测试用例缓存|
| 32  |竞赛查重锁|
| 33  |代码缓存|
| 34  |题单题目列表缓存|
| 35  |MD5缓存|
| 36  |应用缓存|
| 37  |OJ样例更新时间缓存|