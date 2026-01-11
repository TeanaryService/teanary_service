# Teanary - 全球多节点电商平台系统

[![License: AGPL-3.0](https://img.shields.io/badge/License-AGPL--3.0-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-38B2AC.svg)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6.svg)](https://livewire.laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-6366F1.svg)](https://filamentphp.com)

> 一个支持多节点部署、AI自动翻译、商品采集的现代化全球电商平台系统。专为解决跨国电商运营难题而设计。

## 🌟 核心特性

### 🌍 多节点数据同步系统

**解决的核心问题：**
- ✅ **跨国服务器管理难题**：服务器在国外，管理网站不方便？在中国部署管理节点，数据自动同步到全球各节点
- ✅ **本地化运营**：不同国家/地区运行独立节点，提供本地化服务，提升用户体验
- ✅ **数据一致性**：多节点数据自动双向同步，以最新数据为准，确保全球数据一致
- ✅ **故障容灾**：单个节点故障不影响其他节点，系统自动重试同步

**技术特点：**
- 🔄 **双向同步**：支持任意数量节点间的数据双向同步
- 📦 **批量同步**：多条记录打包同步，大幅提升效率
- 🔐 **安全可靠**：API Key 验证，支持 HTTPS 加密传输
- 📁 **文件同步**：自动同步媒体文件（图片、资源等）
- 🔁 **自动重试**：同步失败自动重试，确保数据不丢失
- 📊 **同步监控**：完整的同步日志和状态跟踪

**适用场景：**
- 中国管理节点 + 美国/欧洲/亚洲等多个销售节点
- 不同国家/地区独立运营，数据统一管理
- 需要本地化服务但统一数据源的场景

### 🤖 AI 自动翻译系统

**功能特点：**
- 🌐 **多语言支持**：支持 8 种语言自动翻译（中文、英文、西班牙语、法语、日语、韩语、德语、俄语）
- 📝 **内容翻译**：自动翻译商品信息、文章内容、分类描述等
- 🎯 **智能识别**：自动识别 HTML 内容，保留标签结构
- ⚡ **批量处理**：支持批量翻译，提升效率
- 🔄 **状态跟踪**：翻译状态实时跟踪（待翻译、翻译中、已完成、失败）

**技术实现：**
- 集成 Ollama 本地 AI 模型，无需第三方 API
- 支持自定义翻译提示词，优化翻译质量
- 异步队列处理，不阻塞主流程

### 🛒 Chrome 插件商品采集

**功能特点：**
- 🛍️ **1688 商品采集**：一键采集 1688 商品信息
- 📸 **图片自动下载**：自动下载商品图片并上传到服务器
- 🌐 **多语言处理**：自动提取中文信息，准备翻译
- 📋 **批量导入**：支持批量商品导入
- 🔄 **数据同步**：采集的商品自动同步到所有节点

**使用场景：**
- 从 1688 等平台快速采集商品
- 批量导入商品到电商平台
- 自动化商品管理流程

### 🛍️ 完整电商功能

- **产品管理**：多规格、多图片、多语言产品信息
- **分类系统**：灵活的层级分类和属性筛选
- **购物车**：实时购物车功能
- **订单管理**：完整的订单流程和状态跟踪
- **支付集成**：支持 PayPal 等多种支付方式
- **促销系统**：灵活的促销规则和优惠券
- **用户系统**：用户注册、登录、个人中心
- **内容管理**：多语言文章系统
- **SEO 优化**：自动生成 SEO 标签

### 🎨 现代化管理后台

- **Filament 3.x**：基于 Laravel 的现代化管理面板
- **实时数据统计**：销售数据、用户统计等
- **多语言管理**：统一管理所有语言内容
- **媒体管理**：图片上传、优化、管理
- **系统设置**：灵活的配置管理

## 🚀 技术栈

### 后端技术
- **Laravel 11.x** - PHP Web 框架
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
- **自定义组件** - 针对业务定制的管理组件

### 其他工具
- **Laravel Media Library** - 媒体文件管理
- **Laravel Scout** - 全文搜索
- **Laravel Queue** - 队列处理
- **Laravel Notifications** - 通知系统
- **Ollama** - 本地 AI 模型（用于翻译）

## 📦 快速开始

### 环境要求
- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- Redis
- Ollama (可选，用于 AI 翻译)

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/your-username/teanary_service.git
cd teanary_service
```

2. **安装依赖**
```bash
composer install
npm install
```

3. **环境配置**
```bash
cp .env.example .env
php artisan key:generate
```

4. **配置数据库**
编辑 `.env` 文件：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teanary
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **配置多节点同步（可选）**
```env
SYNC_ENABLED=true
SYNC_NODE=node1

# 配置其他节点
SYNC_NODE2_URL=https://node2.example.com
SYNC_NODE2_API_KEY=your-secret-api-key
SYNC_NODE2_TIMEOUT=600
```

6. **运行数据库迁移**
```bash
php artisan migrate
php artisan db:seed
```

7. **构建前端资源**
```bash
npm run build
```

8. **启动开发服务器**
```bash
php artisan serve
```

访问 `http://localhost:8000` 查看网站。

## 🌐 多节点部署指南

### 场景示例

**场景 1：中国管理 + 全球销售节点**
```
中国节点（管理节点）
├── 美国节点（销售节点）
├── 欧洲节点（销售节点）
└── 亚洲节点（销售节点）
```

**场景 2：多地区独立运营**
```
北京节点
├── 上海节点
├── 广州节点
└── 深圳节点
```

### 配置步骤

1. **在每个节点配置环境变量**
```env
# 节点 1 配置
SYNC_ENABLED=true
SYNC_NODE=beijing
SYNC_BEIJING_URL=https://beijing.example.com
SYNC_BEIJING_API_KEY=key-for-beijing
SYNC_SHANGHAI_URL=https://shanghai.example.com
SYNC_SHANGHAI_API_KEY=key-for-shanghai

# 节点 2 配置
SYNC_ENABLED=true
SYNC_NODE=shanghai
SYNC_BEIJING_URL=https://beijing.example.com
SYNC_BEIJING_API_KEY=key-for-beijing
SYNC_SHANGHAI_URL=https://shanghai.example.com
SYNC_SHANGHAI_API_KEY=key-for-shanghai
```

2. **配置雪花 ID 机器 ID**
每个节点必须配置不同的机器 ID：
```env
SNOWFLAKE_MACHINE_ID=1  # 节点 1
SNOWFLAKE_MACHINE_ID=2  # 节点 2
```

3. **启动队列处理**
```bash
php artisan queue:work
```

详细配置请参考 [SYNC.md](SYNC.md)

## 📡 API 文档

### 商品上传接口

**接口地址**: `POST /api/products/add`

**功能**: 上传商品，支持多语言、多规格、分类自动创建、图片上传

**请求示例**:
```bash
curl -X POST https://your-domain.com/api/products/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-api-token" \
  -d '{
    "slug": "product-slug",
    "translations": [
      {
        "language_id": 1,
        "name": "商品名称",
        "description": "商品描述"
      }
    ],
    "variants": [
      {
        "sku": "SKU-001",
        "price": 99.99,
        "stock": 100
      }
    ]
  }'
```

### 文章上传接口

**接口地址**: `POST /api/articles/add`

**功能**: 上传文章，支持多语言、图片上传

详细 API 文档请参考代码中的接口定义。

## 💼 商业服务

我们提供专业的商业服务支持：

### 🚀 部署服务
- **价格**：¥500/次
- **服务内容**：
  - 服务器环境配置
  - 代码部署和优化
  - 数据库配置
  - 多节点同步配置
  - SSL 证书配置
  - 性能优化

### 🔧 维护服务
- **价格**：¥1500/年
- **服务内容**：
  - 系统更新和维护
  - 安全补丁更新
  - 性能监控和优化
  - 技术支持（邮件/电话）
  - 故障排查和修复
  - 数据备份和恢复

### 🎨 界面二次开发
- **服务内容**：
  - 自定义主题开发
  - 界面定制和优化
  - 新功能开发
  - 第三方系统集成

### 🛒 Chrome 采集插件
- **价格**：¥1500（含3年免费更新支持）
- **服务内容**：
  - Chrome 浏览器插件
  - 1688 商品一键采集
  - 图片自动下载上传
  - 批量商品导入
  - 3年免费更新和技术支持
  - 使用教程和文档

### 🤖 AI 翻译端程序
- **价格**：¥1500（含3年更新支持）
- **服务内容**：
  - 独立的翻译服务程序
  - 集成 Ollama AI 模型
  - 支持 8 种语言自动翻译
  - 商品和文章批量翻译
  - 3年免费更新和技术支持
  - 部署指导和技术文档

**联系方式**：
- 📧 邮箱：hello@teanary.com
- 📱 电话：+86 18184839903
- 💬 微信：请通过邮箱联系获取

## 📄 开源协议

本项目采用 **AGPL-3.0** (GNU Affero General Public License v3.0) 开源协议。

### 协议要点

**您可以：**
- ✅ 自由使用、研究、修改代码
- ✅ 自由分发代码
- ✅ 用于商业项目

**您必须：**
- ⚠️ 如果修改代码并部署为网络服务，必须公开修改后的源代码
- ⚠️ 保留原始版权声明和协议声明
- ⚠️ 使用相同的协议发布衍生作品

**您不能：**
- ❌ 修改代码后作为闭源商业产品售卖
- ❌ 移除版权声明

**为什么选择 AGPL？**
- 保护开源项目的完整性
- 防止将开源项目包装成闭源商业产品
- 鼓励贡献回社区

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

## 📊 项目结构

```
teanary_service/
├── app/
│   ├── Console/          # 控制台命令
│   ├── Enums/           # 枚举类
│   ├── Filament/        # Filament管理面板
│   ├── Http/            # HTTP控制器
│   ├── Jobs/            # 队列任务
│   ├── Livewire/        # Livewire组件
│   ├── Models/          # 数据模型
│   ├── Services/        # 业务服务
│   │   └── SyncService.php  # 多节点同步服务
│   └── Traits/          # 特征类
│       └── Syncable.php     # 同步功能 Trait
├── config/
│   └── sync.php         # 同步配置
├── database/
│   ├── migrations/      # 数据库迁移
│   └── seeders/         # 数据填充
├── lang/                # 多语言文件（8种语言）
├── routes/              # 路由定义
└── tests/               # 测试文件
```

## 🧪 测试

```bash
# 运行所有测试
composer test

# 运行单元测试
php bin/phpunit tests/Unit/

# 运行功能测试
php bin/phpunit tests/Feature/
```

## 📚 相关文档

- [多节点同步文档](SYNC.md) - 详细的多节点同步配置和使用指南
- [代码优化文档](OPTIMIZATION.md) - 代码架构和优化说明
- [部署指南](#部署指南) - 生产环境部署说明

## 🌐 部署指南

### 高性能部署（推荐）

本项目已配置 Laravel Octane 高性能部署。

**首次部署**：
```bash
vendor/bin/dep deploy:first teanary
```

**常规部署**：
```bash
vendor/bin/dep deploy teanary
```

详细部署说明请参考 README 中的部署章节。

## 📞 联系我们

- **项目主页**: [GitHub Repository](https://github.com/your-username/teanary_service)
- **问题反馈**: [GitHub Issues](https://github.com/your-username/teanary_service/issues)
- **邮箱**: hello@teanary.com
- **电话**: +86 18184839903

## 🙏 致谢

感谢所有为这个项目做出贡献的开发者和用户！

---

**Teanary** - 让全球电商运营更简单 🌍
