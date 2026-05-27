# 西游记怀旧MUD H5网页游戏

<p align="center">
  <img src="pic/favicon.ico" alt="Logo" width="120" height="120"/>
</p>

<p align="center">
  <h2>🎮 西游记怀旧mud</h2>
  <p>源自Mud西游记2000的经典还原H5网页文字游戏</p>
  <p>
    <img src="https://img.shields.io/badge/PHP-7.4+-purple.svg" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-5.7+-blue.svg" alt="MySQL">
    <img src="https://img.shields.io/badge/HTML5-H5-green.svg" alt="HTML5">
    <img src="https://img.shields.io/badge/PWA-Supported-orange.svg" alt="PWA">
  </p>
</p>

---

## 📖 项目简介

西游记怀旧mud是一款基于H5技术的经典MUD风格网页游戏，完美还原了Mud西游记2000的游戏体验。玩家可以在游戏中体验经典的角色扮演、地图探索、任务挑战、NPC交互等丰富玩法。

### 🎯 游戏特色

- 🏮 **经典MUD风格** - 原汁原味的文字冒险体验
- 🗺️ **丰富地图系统** - 涵盖长安、洛阳、天庭、花果山等西游记经典场景
- ⚔️ **战斗系统** - 与各路妖怪展开激烈对战
- 💬 **聊天系统** - 实时与其他玩家交流互动
- 🛒 **商店系统** - 购买装备、道具强化角色
- 🎲 **娱乐系统** - 赌大小、股票交易等休闲玩法
- 📱 **移动端优化** - 完美适配手机浏览器，支持PWA安装
- 🌙 **主题切换** - 支持日/夜模式，保护眼睛

---

## 🛠️ 技术栈

### 后端
- **PHP 7.4+** - 服务器端脚本语言
- **MySQL 5.7+/8.0+** - 关系型数据库

### 前端
- **HTML5** - 页面结构
- **CSS3** - 样式设计，包含响应式布局
- **JavaScript** - 交互逻辑
- **jQuery** - 简化DOM操作
- **PWA** - 渐进式网页应用，支持离线访问

### 数据库表结构
- `users` - 用户信息表
- `bet_history` - 赌大小游戏记录
- `stocks` - 股票信息表
- `positions` - 股票持仓表
- `transactions` - 交易记录表
- `npcs` - NPC数据表
- `items` - 物品数据表
- `maps` - 地图数据表

---

## 📁 项目结构

```
wap_xiyouji/
├── index.html              # 游戏首页/登录页面
├── zhuce.php               # 用户注册页面
├── login.php               # 登录处理
├── logout.php              # 退出登录
├── check_user.php          # 用户验证
├── user_now.php           # 当前用户信息
├── db.php                 # 数据库连接和核心函数
├── manifest.json          # PWA配置文件
│
├── css/                   # 样式文件
│   ├── index.css         # 首页样式
│   ├── theme.css         # 主题样式
│   ├── chat.css          # 聊天样式
│   ├── stock.css         # 股票样式
│   └── ...
│
├── js/                    # JavaScript脚本
│   ├── auth.js           # 认证相关
│   ├── chat.js           # 聊天功能
│   ├── theme.js          # 主题切换
│   ├── time.js           # 时间显示
│   └── ...
│
├── map/                   # 地图相关
│   ├── mappic/           # 地图图片和页面
│   └── maptext/          # 地图文本
│
├── casino_game/           # 赌大小游戏
│   ├── dudaxiao.php      # 赌大小主页面
│   ├── stock/            # 股票交易系统
│   └── ...
│
├── shop/                  # 商店系统
│   ├── shop.php          # 商店主页
│   ├── exchange.php      # 兑换系统
│   └── claim.php         # 领取系统
│
├── chat/                  # 聊天模块
│   └── chat.html         # 聊天室
│
├── fly/                   # 飞行系统
│   ├── fly.php           # 飞行主页
│   └── fly2.php          # 飞行功能
│
├── work/                  # 工作/任务系统
│   └── work.html         # 工作页面
│
├── bbs/                   # 论坛系统
│   └── bbs.html          # 论坛主页
│
├── help/                  # 帮助系统
│   └── help.html         # 帮助页面
│
├── pic/                   # 图片资源
│   ├── favicon.ico       # 网站图标
│   ├── dao.png           # 游戏图标
│   └── huyanlv.jpg       # 护眼背景图
│
├── sql/                   # 数据库脚本
│   └── init.sql          # 数据库初始化脚本
│
└── 阿里云.txt             # 部署说明文档
```

---

## 🚀 快速开始

### 环境要求

- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Web服务器 (Apache/Nginx)
- 现代浏览器 (Chrome/Firefox/Safari/Edge)

### 安装步骤

#### 1. 克隆项目

```bash
git clone https://github.com/liunian-lajiaojiang/wap_xiyouji.git
```

#### 2. 配置数据库

编辑 `db.php` 文件，修改数据库连接信息：

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'xyj');
```

#### 3. 导入数据库

```bash
mysql -u root -p < sql/init.sql
```

或者通过phpMyAdmin导入 `sql/init.sql` 文件。

#### 4. 配置Web服务器

**Apache** (在项目根目录创建 `.htaccess`)：

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?/$1 [L]
```

**Nginx** (配置站点)：

```nginx
server {
    listen 80;
    server_name your_domain.com;
    root /path/to/wap_xiyouji;
    index index.html index.php;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 5. 访问游戏

打开浏览器访问：`http://localhost/` 或您的域名

---

## 🎮 游戏功能

### 基础系统

- ✅ 用户注册与登录
- ✅ Token免登录（7天有效期）
- ✅ 记住密码功能
- ✅ 日/夜主题切换

### 游戏内容

- ✅ 地图探索 - 包含40+个经典场景
- ✅ NPC交互 - 与各路神仙妖怪对话
- ✅ 任务系统 - 完成各种挑战任务
- ✅ 签到系统 - 每日签到获取奖励
- ✅ 战斗系统 - 与妖怪进行对战
- ✅ 物品系统 - 收集和使用各种道具

### 娱乐系统

- ✅ 赌大小 - 经典骰子游戏，赔率1:1，手续费25%
- ✅ 股票交易 - 模拟股票市场，支持做多/做空
- ✅ 聊天室 - 实时与其他玩家交流

### 经济系统

- 💰 铜板 - 游戏基础货币
- 💎 银两 - 高级货币
- 🔄 货币兑换 - 100铜板 = 1银两

---

## 📱 PWA支持

游戏支持渐进式网页应用(PWA)，可以像原生应用一样安装到设备上：

1. 使用Chrome/Edge访问游戏
2. 点击地址栏右侧的安装图标
3. 确认安装即可在桌面/主屏幕创建快捷方式

---

## 🔧 常用操作

### 提交代码到GitHub

```bash
# 添加所有更改
git add .

# 提交更改
git commit -m "描述你的更改"

# 推送到GitHub
git push origin main
```

### 拉取最新代码

```bash
git pull origin main
```

### 查看提交历史

```bash
git log --oneline
```

---

## 📝 更新日志

详细更新内容请查看：[近期更新思路](gengxinsilu.html)

### v2.2 (2026-04-24)
- ✅ 优化数据库结构
- ✅ 新增股票交易系统
- ✅ 完善赌大小游戏逻辑
- ✅ 改进UI/UX设计

### v2.1
- ✅ 添加聊天系统
- ✅ 实现地图系统
- ✅ 优化移动端体验

### v2.0
- ✅ 初始版本发布
- ✅ 用户系统完善
- ✅ 基础游戏功能

---

## 🤝 贡献指南

欢迎提交Issue和Pull Request！

1. Fork 本仓库
2. 创建您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建Pull Request

---

## 📄 许可证

本项目仅供学习和交流使用，请勿用于商业目的。

---

## 👨‍💻 作者信息

- GitHub: [@liunian-lajiaojiang](https://github.com/liunian-lajiaojiang)
- 邮箱: qq554498935@163.com

---

## 🙏 致谢

- 感谢Mud西游记2000的原开发者
- 感谢所有测试和反馈的玩家
- 感谢开源社区提供的优秀工具

---

<p align="center">
  <strong>🎮 西游记怀旧mud - 经典重现，情怀依旧 🎮</strong>
</p>

<p align="center">
  如果这个项目对您有帮助，请为我们点个 ⭐️
</p>
