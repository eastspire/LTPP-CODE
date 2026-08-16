# LTPP Online Development Platform

> **Learning · Teaching · Practice Platform** — An all-in-one online programming learning & collaboration platform that unifies *learning* and *practice* through articles, an in-browser IDE, an online judge (OJ), cloud Linux servers, short videos, Q&A, instant messaging, cloud disk, and an online shop.

### 🏷️ Badges

**Stack**
[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Webman](https://img.shields.io/badge/Webman-%5E1.5.0-00A0E9)](https://www.workerman.net/doc/webman)
[![Vue](https://img.shields.io/badge/Vue-2.7.16-42B883?logo=vue.js&logoColor=white)](https://v2.vuejs.org)
[![Workerman](https://img.shields.io/badge/Workerman-powered-brightgreen)](https://www.workerman.net)
[![Node](https://img.shields.io/badge/Node-%3E%3Dv20.18.1-339933?logo=node.js&logoColor=white)](https://nodejs.org)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**Repository**
[![GitHub stars](https://img.shields.io/github/stars/eastspire/LTPP-CODE?style=social)](https://github.com/eastspire/LTPP-CODE/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/eastspire/LTPP-CODE?style=social)](https://github.com/eastspire/LTPP-CODE/network/members)
[![GitHub watchers](https://img.shields.io/github/watchers/eastspire/LTPP-CODE?style=social)](https://github.com/eastspire/LTPP-CODE/watchers)
[![GitHub issues](https://img.shields.io/github/issues/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE/issues)
[![GitHub pull requests](https://img.shields.io/github/issues-pr/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE/pulls)
[![GitHub last commit](https://img.shields.io/github/last-commit/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE/commits/master)
[![GitHub commit activity](https://img.shields.io/github/commit-activity/m/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE/commits/master)
[![GitHub top language](https://img.shields.io/github/languages/top/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE)
[![GitHub language count](https://img.shields.io/github/languages/count/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE)
[![GitHub repo size](https://img.shields.io/github/repo-size/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE)
[![GitHub code size](https://img.shields.io/github/languages/code-size/eastspire/LTPP-CODE)](https://github.com/eastspire/LTPP-CODE)

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Architecture](#-architecture)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Quick Start](#-quick-start)
- [Frontend](#-frontend)
- [Backend](#-backend)
- [Scheduled Tasks (Crontabs)](#-scheduled-tasks-crontabs)
- [Deployment](#-deployment)
- [Configuration](#-configuration)
- [Routes & URL Design](#-routes--url-design)
- [Supported Languages](#-supported-languages)
- [Contributing](#-contributing)
- [License](#-license)
- [Contact](#-contact)

---

## 🌟 Overview

**LTPP** (Learning · Teaching · Practice Platform, /LTPP 在线开发平台/) is a self-hosted, full-stack online development and learning platform built on top of **[Webman](https://www.workerman.net/doc/webman)** (a high-performance PHP HTTP framework powered by Workerman) and **Vue 2 + Element UI**.

The original Chinese description (from `public/index.html`):

> LTPP（Learning Teaching Practice Platform）在线开发平台是一个编程学习网站，拥有文章学习、代码训练、云服务器、短视频、在线直播、在线问答、在线聊天、云盘和在线商店等功能，专注于提升用户编程能力，做到"学"与"练"的统一。

**In English:** LTPP is a programming learning website that combines article-based study, hands-on code training, cloud servers, short videos, online Q&A, real-time chat, cloud storage, and an online shop — all focused on improving programming ability through the unity of *learning* and *practice*.

It is designed to run on modest hardware (the default HTTP listener binds to **`0.0.0.0:48787`** and auto-scales worker processes to `min(cpu*2, 12)`), yet exposes a wide product surface for community, content, judging, and e-commerce.

---

## 🚀 Key Features

### For Learners
- **📚 Articles** — Long-form learning content with comments (`Article`, `ArticleComment` controllers).
- **🎥 Short Video Community** — Douyin/TikTok-style feed + creator dashboard (`Video`, `Dayproblem`).
- **❓ Q&A** — `Question`, `QuestionSheet` (paper-style) question banks.
- **🧠 Daily Problem (Day Problem)** — Auto-pushed practice problems on schedule.
- **💬 Real-time Chat** — Private + group chat + global notices over WebSocket (GatewayWorker).
- **🎵 Music Streaming** — Built-in music player integrated with backend service on port `3000`.
- **🛒 Goods Store** — Product listings, my-goods management, and an admin-facing goods manager.
- **👤 User Pages** — Profiles, follow/fans, score rank, school/class metadata.
- **📁 Cloud Disk / Cloud Files** — Personal cloud file storage.
- **🔔 Notice System** — Per-user notifications + global broadcast.

### For Developers (Coders)
- **💻 Online IDE** — In-browser code editor (Monaco) supporting 12+ languages, syntax-highlighted preview of static code files.
- **🌐 Webcode (Cloud Linux)** — SSH-backed remote Linux sandboxes on a configurable port (`49999`).
- **⚖️ Online Judge (OJ)** — Problem set, contest mode, judging worker, rank board, real-time status.
- **🧾 Code History** — Every submission/version saved and browsable.
- **📤 Code Share** — Share snippets publicly via short URLs.
- **🤖 Robot & Auto Judging** — Automated accounts participate in contests for testing.

### For Admins / Operators
- **🛠 Admin Console** — User, problem, contest, app, goods, photo, video, notice, and short-sentence management.
- **👑 Root Console** — Site-wide configuration: monitor, setting, video/article/goods manager.
- **📈 System Monitor** — Real-time metrics dashboard.
- **🧹 Automated Maintenance** — Crontabs for cleaning the DB, status correction, contest rank, daily problems, etc.

---

## 🏗 Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                         Browser (Vue 2 SPA)                  │
│   Home / IDE / OJ / Chat / Articles / Videos / Shop / Admin  │
└──────────────────────────────────────────────────────────────┘
                │ HTTP (port 48787)        │ WebSocket
                ▼                          ▼
┌──────────────────────────────────────────────────────────────┐
│                     Webman Application (PHP 8.2+)            │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐  │
│  │ Controllers    │  │ Middleware     │  │ Plugin:        │  │
│  │ (40+ classes)  │  │  - AuthCheck   │  │  gateway-worker│  │
│  │                │  │  - CrossDomain │  │  redis-queue   │  │
│  └────────────────┘  └────────────────┘  └────────────────┘  │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │ Webman GatewayWorker (Chat: private / group / notice)   │ │
│  └─────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘
                │                       │
                ▼                       ▼
┌────────────────────────┐  ┌────────────────────────┐
│ MySQL (port 13306)     │  │ Redis (port 16379,     │
│ Illuminate/Eloquent    │  │ 39 logical DBs)        │
└────────────────────────┘  └────────────────────────┘
                ▲                       ▲
                │                       │
┌──────────────────────────────────────────────────────────────┐
│   Crontab Workers (process/*.php)                            │
│   - CleanRobotDb   - DayProblem   - ContestRank              │
│   - CodeStatusCorrect  - Webcode  - CreatContest             │
│   - RobotContest   - DouYin       - CreatFileTable          │
└──────────────────────────────────────────────────────────────┘
```

---

## 🧰 Tech Stack

### Backend
| Layer | Technology |
|---|---|
| Language | **PHP ≥ 8.2** |
| HTTP / App Server | **Webman** (`workerman/webman-framework ^1.5.0`) on **Workerman** |
| Realtime / WebSocket | **Webman GatewayWorker** (`webman/gateway-worker ^1.0`) + **GatewayWorker/Lib** |
| ORM | **Illuminate Database** (`illuminate/database ^9.18`) — Eloquent for MySQL |
| Cache / Queue | **Redis** (`illuminate/redis ^9.18`, `predis/predis ^2.0`, `workerman/redis ^2.0`) + **webman/redis-queue** |
| Async Queue | `webman/redis-queue ^1.3` (see `app/queue/redis`) |
| Logging | **Monolog** (`monolog/monolog ^2.9.2`) |
| Auth | **JWT** via `tinywan/jwt ^1.2` |
| Mail | `phpmailer/phpmailer ^6.6` |
| Object Storage | `tinywan/storage ^0.3.3` (OSS-style abstraction) |
| Hooks | `webman/action-hook ^1.0` |
| Pagination | `jasongrimes/paginator ~1.0` + `illuminate/pagination ^9.18` |
| HTTP Foundation | `symfony/http-foundation ^3.0` |
| Scheduled Tasks | `workerman/crontab ^1.0` |
| HTTP Client | `workerman/http-client ^2.0` |
| PSR | `psr/container ^2.0.2` |

### Frontend
| Layer | Technology |
|---|---|
| Framework | **Vue 2.7.16** |
| UI Kit | **Element UI 2.15.6** |
| Router / Store | `vue-router 3.5.2`, `vuex 3.6.2` |
| HTTP | `axios 1.7.7` |
| Build | **Vue CLI 5** + `less`, `monaco-editor-webpack-plugin`, `compression-webpack-plugin` |
| Code Editor | **Monaco Editor** with syntax-highlighting for 12+ languages |
| Runtime | Node ≥ **v20.18.1**, Yarn ≥ **1.22.22** |

---

## 📁 Project Structure

```
LTPP-CODE/
├── app/                      # Application code
│   ├── controller/           # 40+ HTTP controllers (User, OJ, Chat, Article, Video, …)
│   ├── middleware/           # AuthCheckTest, CrossDomain
│   ├── queue/                # Redis-queue jobs
│   └── functions.php
├── config/                   # All Webman config files
│   ├── app.php, server.php, route.php, …
│   └── plugin/               # Plugin-specific configs
│       ├── tinywan/
│       └── webman/
│           ├── gateway-worker/
│           ├── redis-queue/
│           ├── console/
│           └── action-hook/
├── process/                  # Long-running workers / crontabs
│   ├── CleanRobotDbCrontab.php
│   ├── CodeStatusCorrectCrontab.php
│   ├── ContestRankCrontab.php
│   ├── CreatContestCrontab.php
│   ├── CreatFileTable.php
│   ├── DayproblemCrontab.php
│   ├── DouYinCrontab.php
│   ├── RobotContestCrontab.php
│   └── WebcodeCrontab.php
├── plugin/
│   └── webman/
│       └── gateway/          # GatewayWorker event handlers
│           ├── Events.php
│           ├── ChatBase.php
│           ├── ClassMsg.php
│           ├── GlobalNotice.php
│           ├── GroupChat.php
│           ├── PrivateChat.php
│           └── PrivateRobot.php
├── support/                  # Helpers, bootstrap, custom error handler
│   ├── bootstrap.php
│   ├── helpers.php
│   ├── LTPPErrorHandler.php
│   ├── Plugin.php
│   ├── Request.php
│   └── Response.php
├── Frontend/                 # Vue 2 SPA
│   ├── public/               # Static assets (logo, music, md, …)
│   ├── src/
│   │   ├── App.vue, main.js
│   │   ├── components/       # Reusable widgets
│   │   ├── plugins/
│   │   ├── router/
│   │   ├── utils/
│   │   ├── views/            # Home + home/, admin/, back/, root/, login.vue, register.vue
│   │   └── assets/
│   ├── babel.config.js
│   ├── vue.config.js
│   └── package.json
├── InstallMust/              # First-run installation assets / scripts
├── sh/                       # Shell helper scripts
├── vendor/                   # Composer dependencies (PHP)
├── composer.json
├── composer.lock
├── start.php                 # Webman entrypoint
├── webman                    # CLI entrypoint (php webman <command>)
├── windows.php / windows.bat # Windows launchers
├── vercel.json               # Static deploy config (Vercel)
├── LICENSE                   # MIT
└── README.md
```

---

## ⚡ Quick Start

### Prerequisites
- **PHP ≥ 8.2** with extensions: `pdo_mysql`, `redis`, `pcntl`, `posix`, `mbstring`, `openssl`, `curl`, `sockets`
- **MySQL 5.7+ / 8.0+** (default port `13306`)
- **Redis 5+** (default port `16379`)
- **Composer**
- **Node.js ≥ v20.18.1** & **Yarn ≥ 1.22.22** (for the frontend)
- OS: Linux (recommended) or Windows

### 1. Clone
```bash
git clone https://github.com/eastspire/LTPP-CODE.git
cd LTPP-CODE
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Configure
Edit `config/database.php` (MySQL DSN, host, port) and `config/redis.php` (host, port, password). The defaults target the bundled service ports (`13306` / `16379`).

### 4. Build the frontend
```bash
cd Frontend
yarn install
yarn build           # produces Frontend/frontend (served by Webman as static assets)
cd ..
```

> For dev mode: `yarn dev` (Vue CLI serves on its own port with HMR).

### 5. Run
**Linux / macOS:**
```bash
php start.php start          # foreground
php start.php start -d       # daemonized
```

**Windows:**
```bat
windows.bat
```

**CLI helper:**
```bash
php webman status            # check workers
php webman reload            # graceful reload
php webman stop              # stop
```

The HTTP server listens on `http://0.0.0.0:48787` (see `config/server.php`).

---

## 🎨 Frontend

The Vue 2 SPA in `Frontend/` is a feature-rich client with three role-based consoles:

| Path prefix | Purpose |
|---|---|
| `/` (Home.vue) | Landing + sidebar entry |
| `views/home/` | Public-facing pages: articles, video community, OJ, IDE, chat, music, shop, day-problem, contest, user page, … |
| `views/admin/` | Contest & problem management, user management |
| `views/back/`  | Personal-center pages: my-articles, my-goods, my-questions, my-contests, write/publish, … |
| `views/root/`  | Super-admin: setting, monitor, all-entities manager |
| `login.vue`, `register.vue`, `maintenance.vue` | Auth & maintenance screens |

The frontend is bundled to `Frontend/frontend` and is served as static files by Webman.

---

## ⚙️ Backend

### Controllers (selected, in `app/controller/`)

| Controller | Responsibility |
|---|---|
| `Base.php` | Core: token/JWT, file storage abstractions, language map, app constants |
| `User.php` | Profile, register, login, follow/fans, settings |
| `Login.php` / `Register.php` | Auth flow |
| `Oj.php` / `Ojjudge.php` | Online judge — problem browsing, judging, status |
| `Contest.php` | Contest lifecycle, registration, ranking |
| `Question.php` / `QuestionSheet.php` | Q&A + question papers |
| `Article.php` / `ArticleComment.php` | Article CMS |
| `Video.php` | Video community (upload, feed, like, comments) |
| `Chat.php` / `Chatfile.php` | Chat history + file attachments |
| `Cloudfile.php` / `File.php` / `Filehtml.php` | Cloud disk + static-file serving helper |
| `Goods.php` | E-commerce |
| `Linux.php` / `Ssh.php` / `Webcode.php` | Cloud Linux / SSH sandboxes |
| `Codehistory.php` / `Codeshare.php` | Code history & sharing |
| `Setting.php` | System settings |
| `Monitor.php` / `Scorerank.php` | Ops & leaderboards |
| `Image.php` / `Photo.php` / `Music.php` | Media |
| `Verification.php` | Email / captcha verification |
| `Version.php` | Client version checks |
| `Dayproblem.php`, `Robot.php`, `Proxy.php`, `Url.php`, `Email.php`, … | Misc utilities |

### Middleware (`app/middleware/`)
- **`AuthCheckTest.php`** — JWT/session authentication for protected routes
- **`CrossDomain.php`** — CORS handling

### Plugins (`plugin/webman/`)
- **`gateway/`** — WebSocket event handlers: private chat, group chat, class messages, global notice, private robot
- **`gateway-worker`** config — bind/transport settings for WS endpoint
- **`redis-queue`** config — async job consumers
- **`console`** — extra CLI commands
- **`action-hook`** — request lifecycle hooks

---

## ⏱ Scheduled Tasks (Crontabs)

All crontabs live in `process/` and are loaded as long-lived workers by Webman.

| Crontab | Purpose |
|---|---|
| `CleanRobotDbCrontab.php` | Periodically clean up data created by robot accounts |
| `CodeStatusCorrectCrontab.php` | Reconcile stale "judging" submissions into final status |
| `ContestRankCrontab.php` | Recompute contest ranking boards |
| `CreatContestCrontab.php` | Auto-create scheduled contests |
| `CreatFileTable.php` | Create/rotate file tables |
| `DayproblemCrontab.php` | Push the daily problem to users |
| `DouYinCrontab.php` | Sync Douyin videos (max 4 MB) |
| `RobotContestCrontab.php` | Drive robot accounts to participate in contests |
| `WebcodeCrontab.php` | Maintain cloud-Linux sandboxes (SSH on `49999`) |

---

## 🚢 Deployment

### Bare-metal / VM (recommended for full feature set)
Use **process supervision** (`systemd`, `supervisord`, or `pm2`) to keep `php start.php start -d` alive.

### Vercel
The repository ships a minimal `vercel.json` for static-only deploy of the frontend SPA (note: dynamic backend features — DB, WebSocket chat, OJ, cloud Linux — will **not** function on Vercel; a Vercel deploy is only useful for the static client).

### Production checklist
- [ ] Set `config/app.php` → `'debug' => false`
- [ ] Point MySQL / Redis to managed services (override `Base::$mysql_port`, `Base::$redis_port`)
- [ ] Set a strong JWT secret (`tinywan/jwt` config)
- [ ] Configure `phpmailer` SMTP for `Verification` / `Email`
- [ ] Place `Frontend/frontend` build artifacts where Webman static handler can serve them
- [ ] Expose ports `48787` (HTTP) and your WebSocket port behind a reverse proxy with TLS

---

## 🔧 Configuration

Key static defaults live in `app/controller/Base.php`:

| Constant | Default | Meaning |
|---|---|---|
| `$app_name` | `LTPP在线开发平台` | Display name |
| `$redis_db_num` | `39` | Number of logical Redis DBs |
| `$mysql_domain_name` | `MYSQL` | Env/service name for MySQL host |
| `$redis_domain_name` | `REDIS` | Env/service name for Redis host |
| `$clash_domain_name` | `CLASH` | Clash proxy host (for outbound) |
| `$music_port` | `3000` | Music service port |
| `$mysql_port` | `13306` | MySQL port |
| `$redis_port` | `16379` | Redis port |
| `$ssh_port` | `49999` | LTPP-SSH (cloud Linux) port |
| `$clash_port` | `7890` | Clash mixed-port |
| `$request_timout` | `600` | Request timeout (seconds) |
| `$ssh_min_open_ports_num` | `2` | Min public SSH ports |
| `$gzip_num` | `5` | gzip level |
| `$img_quality` | `60` | JPEG re-encoding quality |

Per-environment overrides are recommended via `config/*.php` (database, redis, plugin configs under `config/plugin/...`).

---

## 🌐 Routes & URL Design

The default `LTPP_public_static_path` is a route that maps any file-style URL to backend-stored content, dispatching by file extension:

- **Code files** (`c`, `cpp`, `java`, `py`, `go`, `php`, `js`, `ts`, `rs`, `cs`, `rb`, `css`, `sh`, …) → syntax-highlighted HTML preview via Monaco language bundles (server-side language map)
- **Markdown** (`.md`) → rendered Markdown
- **Other assets** → served with appropriate `Content-Type`, optional `gzip`, optional `Cache-Control: public,max-age=88888888`
- **Unknown paths** → fallback 404 page

This makes the platform act as both an app server and a content / static file host (similar in spirit to a personal Notion + Gist + Cloud Drive).

---

## 🈶 Supported Languages

Defined in `app/controller/Base.php` as a `Language` enum and `$map_language_file` map:

| Language | Extensions |
|---|---|
| C | `.c` |
| C++ | `.h`, `.cc`, `.cpp` |
| Java | `.java` |
| Python | `.py` (and `.rusthon`) |
| Go | `.go` |
| PHP | `.php` |
| JavaScript | `.js` |
| TypeScript | `.ts` |
| Rust | `.rs` |
| C# | `.cs`, `.c#` |
| Ruby | `.rb`, `.rbx`, `.jruby`, `.macruby`, `.rake` |
| Bash | `.sh` |
| CSS | `.css` |
| HTML | `.html` (raw preview, not highlighted) |

---

## 🤝 Contributing

PRs and issues are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/awesome-thing`
3. Commit: `git commit -m "feat: add awesome thing"`
4. Push: `git push origin feat/awesome-thing`
5. Open a Pull Request

### Coding style
- **PHP**: PSR-12, follow existing controller patterns in `app/controller/`
- **Vue**: Vue 2 + Element UI conventions; keep components in `Frontend/src/components/`
- Keep new crontabs in `process/` and reference them from the worker bootstrap

---

## 📄 License

This project is released under the **MIT License** — see [LICENSE](LICENSE).

The codebase builds on the MIT-licensed [Webman](https://github.com/walkor/webman) framework by walkor and contributors.

---

## 📬 Contact

| Channel | Detail |
|---|---|
| 📦 **GitHub** | <https://github.com/eastspire/LTPP-CODE> |
| 🐛 **Issues** | <https://github.com/eastspire/LTPP-CODE/issues> |

> "学"与"练"的统一 — *Unifying learning and practice.*
