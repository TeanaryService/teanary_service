# Teanary - 全球多节点电商平台系统

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-4.x-38B2AC.svg)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-4E56A6.svg)](https://livewire.laravel.com)

> 一个支持多节点部署、AI自动翻译、商品采集的现代化全球电商平台系统，支持多语言、多货币自动换算和结算。专为解决跨国电商运营难题而设计。

> 代码已通过单元测试和静态分析。普通 bug 反馈请通过 Issues 提交；安全相关 bug 请通过 Email (hello@teanary.com) 提交。

## 🎮 在线演示

**前端地址**: [https://demo.chatterup.fun:2003](https://demo.chatterup.fun:2003)  
**后台管理**: [https://demo.chatterup.fun:2003/m](https://demo.chatterup.fun:2003/m)

**测试账号**（前后端通用）:
- 邮箱: `demo@demo.com`
- 密码: `demo123456`

**重要说明**:
- ⚠️ **Demo 数据每 8 小时自动重置一次**
- 💻 当前 Demo 服务器运行在一台树莓派上，性能有限，请谅解
- 🌐 如果您愿意赞助服务器资源部署 Demo，我们可以部署多节点同步演示环境，展示完整的多节点同步功能
- 📧 如有赞助意向或想了解更多信息，请联系：hello@teanary.com

## 🌟 核心特性

### 🌍 多节点数据同步系统

支持任意数量节点间的数据双向同步，解决跨国服务器管理难题。数据自动同步，以最新数据为准，确保全球数据一致。

**主要特点：**
- 🔄 双向同步，支持任意数量节点
- 📦 批量同步，大幅提升效率
- 🔐 API Key 验证，HTTPS 加密传输
- 📁 自动同步媒体文件（图片、资源等）
- 🔁 自动重试机制，确保数据不丢失
- 📊 完整的同步日志和状态跟踪
- 🎯 基于哈希值的智能去重，避免重复同步
- 🔗 级联删除支持，自动同步关联数据

详细文档请参考 [多节点数据同步文档](docs/SYNC.md)。

### 🎨 现代化管理后台

基于 Livewire 4.x + 自定义 Blade 组件系统重构的管理后台，提供：

**核心功能：**
- 📊 实时数据统计和流量看板（完全用 Livewire 实现）
- 🛍️ 商品/规格/SKU/促销/订单等业务页面统一管理
- 🌐 多语言内容管理、媒体管理
- ⚡ 批量操作与同步深度集成
- 🎯 统一的组件系统（按钮、表单、提示等）
- 🔔 实时消息提示系统（右上角显示）
- 📱 响应式设计，支持移动端

**组件系统：**
- `<x-widgets.button>` - 统一的按钮组件
- `<x-widgets.checkbox>` - 统一的复选框组件
- `<x-widgets.alert>` - 统一的提示消息组件
- `<x-widgets.flash-messages>` - 实时消息提示系统

### 🤖 AI 自动翻译系统

支持 8 种语言自动翻译（中文、英文、西班牙语、法语、日语、韩语、德语、俄语），集成 Ollama 本地 AI 模型，无需第三方 API。

### 🛒 Chrome 插件商品采集

一键采集 1688 商品信息，自动下载图片并上传到服务器，支持批量导入。

### 🛍️ 完整电商功能

- **产品管理**：多规格、多图片、多语言支持
- **分类系统**：多级分类和属性筛选
- **购物车和订单**：完整的购物流程
- **支付集成**：PayPal 等支付方式
- **促销系统**：优惠券、折扣、满减等
- **用户系统**：用户注册、登录、个人中心
- **内容管理**：文章、页面管理
- **SEO 优化**：友好的 URL、Meta 标签等

### 🔧 开发体验优化

- **代码质量**：PHPStan 静态分析、Pint 代码格式化
- **单元测试**：完整的测试覆盖，包括同步逻辑测试
- **组件化开发**：统一的组件系统，提高开发效率
- **批量操作**：支持批量删除、批量更新状态等
- **级联删除**：Observer 模式实现关联数据自动删除

## 🚀 技术栈

### 后端技术
- **Laravel 12.x** - PHP Web 框架
- **PHP 8.2+** - 服务器端语言
- **MySQL 8.0+** - 数据库
- **Redis** - 缓存和会话存储
- **Laravel Octane** - 高性能应用服务器（可选）

### 前端技术
- **Tailwind CSS 4.x** - 实用优先的 CSS 框架
- **Livewire 4.x** - 全栈框架，无需编写 JavaScript
- **Alpine.js** - 轻量级 JavaScript 框架
- **Vite** - 现代前端构建工具

### 核心服务
- **SyncService** - 多节点数据同步服务
- **SnowflakeService** - 分布式 ID 生成服务
- **MediaLibrary** - 媒体文件管理（Spatie）
- **Scout** - 全文搜索（Meilisearch）

## 📚 文档

完整的项目文档位于 `docs` 目录：

- **[文档目录](docs/README.md)** - 所有文档的索引
- **[系统架构](docs/ARCHITECTURE.md)** - 系统架构与数据流程图
- **[部署指南](docs/DEPLOYMENT.md)** - 快速开始、开发环境安装和生产环境部署完整指南
- **[多节点数据同步](docs/SYNC.md)** - 详细的多节点同步配置和使用指南
- **[流量统计功能](docs/TRAFFIC-STATISTICS.md)** - 流量统计功能完整文档
- **[发布指南](docs/RELEASE.md)** - 版本发布流程

## 🛠️ 快速开始

### 开发环境

**环境要求**：PHP >= 8.2, Composer, Node.js >= 16.x, MySQL >= 8.0, Redis

**三步启动**：

```bash
# 1. 克隆项目
git clone https://github.com/TeanaryService/teanary_srvice.git
cd teanary_service

# 或使用 Gitee
git clone https://gitee.com/teanary/teanary_service.git
cd teanary_service

# 2. 安装依赖
composer install
npm install

# 3. 配置环境并启动
cp .env.example .env
php artisan key:generate
# 编辑 .env 文件，配置数据库连接信息
php artisan migrate  # 创建数据表
php artisan db:seed  # 填充初始数据（语言、货币、国家等）

# 4. 启动开发服务器（一键启动所有服务）
composer dev
```

访问 `http://localhost:8013` 查看网站。

> 💡 `composer dev` 会自动启动：Web 服务器、队列服务、定时任务、日志监控和前端构建工具。

### 生产环境部署

**推荐使用 Deployer 自动部署**（最简单）：

```bash
# 1. 复制部署配置文件
cp docs/example.deploy.php deploy.php

# 2. 编辑 deploy.php，修改服务器配置
# - 修改仓库地址
# - 修改服务器 IP、用户名、部署路径等

# 3. 一键部署
./bin/dep deploy production

# 4. 首次部署后，SSH 登录服务器运行迁移和填充数据
ssh deployer@your-server
cd /home/wwwroot/teanary/current
php artisan migrate --force  # 创建数据表
php artisan db:seed --force  # 填充初始数据
```

详细说明请参考 [部署指南](docs/DEPLOYMENT.md)。

## 🧪 测试

```bash
# 运行所有测试
composer test

# 运行单元测试
composer test:unit

# 运行功能测试
composer test:feature

# 生成测试覆盖率报告
composer test:coverage
```

## 🔍 代码质量

```bash
# 代码格式化
composer format

# 静态分析
composer analyse

# 代码质量检查（格式化 + 静态分析 + 测试）
composer check
```

## 💼 商业服务

我们提供专业的商业服务支持：

- **部署服务**：¥500/次（服务器配置、代码部署、性能优化）
- **维护服务**：¥1500/年（系统更新、安全补丁、技术支持）
- **界面二次开发**：自定义主题、界面定制、新功能开发
- **Chrome 采集插件**：¥1500（含3年免费更新支持）
- **AI 翻译端程序**：¥1500（含3年更新支持）

**联系方式**：
- 📧 邮箱：hello@teanary.com
- 📱 电话：+86 18184839903

## 📄 开源协议

本项目采用 **AGPL-3.0** (GNU Affero General Public License v3.0) 开源协议。

**您可以：**
- ✅ 自由使用、研究、修改代码
- ✅ 自由分发代码
- ✅ 用于商业项目

**您必须：**
- ⚠️ 如果修改代码并部署为网络服务，必须公开修改后的源代码
- ⚠️ 保留原始版权声明和协议声明
- ⚠️ 使用相同的协议发布衍生作品

完整协议内容请查看 [LICENSE](LICENSE) 文件。

## 🤝 贡献指南

我们欢迎社区贡献！请遵循以下步骤：

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建 Pull Request

### 代码规范

- 遵循 PSR-12 编码标准
- 使用有意义的变量和函数名
- 添加适当的注释
- 编写单元测试
- 运行代码质量检查：`composer check`

## 🙏 致谢

感谢所有为这个项目做出贡献的开发者和用户！

---

**Teanary** - 让全球电商运营更简单 🌍
