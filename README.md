# Teanary - 全球多节点电商平台系统

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-38B2AC.svg)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6.svg)](https://livewire.laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-6366F1.svg)](https://filamentphp.com)

> 一个支持多节点部署、AI自动翻译、商品采集的现代化全球电商平台系统。专为解决跨国电商运营难题而设计。

> 代码已通过单元测试和静态分析,普通bug反馈请通过Issues提交；安全相关bug请通过Email(hello@teanary.com)提交

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
- 📁 自动同步媒体文件
- 🔁 自动重试，确保数据不丢失
- 📊 完整的同步日志和状态跟踪

### 🤖 AI 自动翻译系统

支持 8 种语言自动翻译（中文、英文、西班牙语、法语、日语、韩语、德语、俄语），集成 Ollama 本地 AI 模型，无需第三方 API。

### 🛒 Chrome 插件商品采集

一键采集 1688 商品信息，自动下载图片并上传到服务器，支持批量导入。

### 🛍️ 完整电商功能

- 产品管理（多规格、多图片、多语言）
- 分类系统和属性筛选
- 购物车和订单管理
- 支付集成（PayPal 等）
- 促销系统和优惠券
- 用户系统和内容管理
- SEO 优化

### 🎨 现代化管理后台

基于 Filament 3.x 的现代化管理面板，提供实时数据统计、多语言管理、媒体管理等功能。

## 🚀 技术栈

### 后端技术
- **Laravel 12.x** - PHP Web 框架
- **PHP 8.1+** - 服务器端语言
- **MySQL 8.0+** - 数据库
- **Redis** - 缓存和会话存储
- **Laravel Octane** - 高性能应用服务器

### 前端技术
- **Tailwind CSS 3.x** - 实用优先的 CSS 框架
- **Livewire 3.x** - 全栈框架
- **Alpine.js** - 轻量级 JavaScript 框架
- **Vite** - 现代前端构建工具

### 管理后台
- **Filament 3.x** - Laravel 管理面板

## 📚 文档

完整的项目文档位于 `docs` 目录：

- **[文档目录](docs/README.md)** - 所有文档的索引
- **[系统架构与数据流程图](docs/ARCHITECTURE.md)** - 系统架构与数据流程图
- **[部署指南](docs/DEPLOYMENT.md)** - 快速开始、开发环境安装和生产环境部署完整指南
- **[多节点数据同步](docs/SYNC.md)** - 详细的多节点同步配置和使用指南
- **[流量统计功能](docs/traffic-statistics.md)** - 流量统计功能完整文档
- **[代码优化说明](docs/OPTIMIZATION.md)** - 代码架构和优化说明
- **[发布新版本指南](docs/RELEASE.md)** - 版本发布流程

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
