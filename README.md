# Teanary - 中国茶叶电商平台

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-38B2AC.svg)](https://tailwindcss.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6.svg)](https://livewire.laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-6366F1.svg)](https://filamentphp.com)

> 一个专注于中国茶文化的现代化电商平台，传承千年茶文化，让优质茶叶直达世界每一处。

## 🌟 项目特色

### 🍃 茶文化主题设计
- **中国传统色彩**：采用茶绿、竹青、陶瓷、墨色等传统色彩体系
- **文化元素**：融入茶叶、竹子、陶瓷等中国传统文化元素
- **优雅动画**：柔和的浮动和过渡动画，体现茶的优雅
- **响应式设计**：完美适配桌面端和移动端

### 🌍 多语言支持
支持8种语言，为全球用户提供本地化体验：
- 🇨🇳 中文 (zh_CN)
- 🇺🇸 英文 (en)
- 🇪🇸 西班牙语 (es)
- 🇫🇷 法语 (fr)
- 🇯🇵 日语 (ja)
- 🇰🇷 韩语 (ko)
- 🇩🇪 德语 (de)
- 🇷🇺 俄语 (ru)

### 🛍️ 完整电商功能
- **产品管理**：支持多规格、多图片、多语言产品信息
- **分类系统**：灵活的茶类分类和属性筛选
- **购物车**：实时购物车功能
- **订单管理**：完整的订单流程和状态跟踪
- **支付集成**：支持多种支付方式
- **促销系统**：灵活的促销规则和优惠券
- **用户系统**：用户注册、登录、个人中心

## 🚀 技术栈

### 后端技术
- **Laravel 11.x** - PHP Web框架
- **PHP 8.1+** - 服务器端语言
- **MySQL** - 数据库
- **Redis** - 缓存和会话存储

### 前端技术
- **Tailwind CSS 3.x** - 实用优先的CSS框架
- **Livewire 3.x** - 全栈框架
- **Alpine.js** - 轻量级JavaScript框架
- **Vite** - 现代前端构建工具

### 管理后台
- **Filament 3.x** - Laravel管理面板
- **自定义组件** - 针对茶叶业务定制的管理组件

### 其他工具
- **Laravel Media Library** - 媒体文件管理
- **Laravel Scout** - 全文搜索
- **Laravel Queue** - 队列处理
- **Laravel Notifications** - 通知系统

## 📦 安装指南

### 环境要求
- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- Redis

### 安装步骤

1. **克隆项目**
```bash
git clone https://github.com/your-username/teanary.git
cd teanary
```

2. **安装PHP依赖**
```bash
composer install
```

3. **安装前端依赖**
```bash
npm install
```

4. **环境配置**
```bash
cp .env.example .env
php artisan key:generate
```

5. **配置数据库**
编辑 `.env` 文件，设置数据库连接：
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teanary
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **运行数据库迁移**
```bash
php artisan migrate
```

7. **填充示例数据**
```bash
php artisan db:seed
```

8. **构建前端资源**
```bash
npm run build
```

9. **启动开发服务器**
```bash
php artisan serve
```

访问 `http://localhost:8013` 查看网站。

## 🎨 主题定制

### 茶文化色彩系统
项目使用自定义的茶文化色彩系统：

```css
/* 茶绿色系 */
.tea-50   { color: #f0f9f0; }
.tea-500  { color: #3a9d3a; } /* 主茶绿色 */
.tea-800  { color: #1c421c; }

/* 竹绿色系 */
.bamboo-50  { color: #f7f8f6; }
.bamboo-500 { color: #8a9a7a; } /* 竹绿色 */

/* 陶瓷色系 */
.ceramic-50  { color: #faf9f7; }
.ceramic-500 { color: #b8a585; } /* 陶瓷色 */

/* 墨色系 */
.ink-500 { color: #9a9a9a; }
.ink-950 { color: #262626; } /* 墨色 */
```

### 自定义组件
- `x-tea-decoration` - 茶文化装饰组件
- `x-tea-background` - 茶文化背景组件
- `x-tea-card` - 茶文化卡片样式
- `x-tea-btn-primary` - 茶文化按钮样式

## 📁 项目结构

```
teanary/
├── app/
│   ├── Console/          # 控制台命令
│   ├── Enums/           # 枚举类
│   ├── Filament/        # Filament管理面板
│   ├── Http/            # HTTP控制器
│   ├── Jobs/            # 队列任务
│   ├── Listeners/       # 事件监听器
│   ├── Livewire/        # Livewire组件
│   ├── Models/          # 数据模型
│   ├── Notifications/   # 通知类
│   ├── Observers/       # 模型观察者
│   ├── Providers/       # 服务提供者
│   ├── Services/        # 业务服务
│   ├── Support/         # 支持类
│   ├── Traits/          # 特征类
│   ├── Utils/           # 工具类
│   └── View/            # 视图组件
├── config/              # 配置文件
├── database/
│   ├── factories/       # 模型工厂
│   ├── migrations/      # 数据库迁移
│   └── seeders/         # 数据填充
├── lang/                # 多语言文件
│   ├── zh_CN/          # 中文
│   ├── en/             # 英文
│   ├── es/             # 西班牙语
│   ├── fr/             # 法语
│   ├── ja/             # 日语
│   ├── ko/             # 韩语
│   ├── de/             # 德语
│   └── ru/             # 俄语
├── public/              # 公共资源
├── resources/
│   ├── css/            # 样式文件
│   ├── js/             # JavaScript文件
│   └── views/          # 视图模板
├── routes/              # 路由定义
├── storage/             # 存储目录
└── tests/               # 测试文件
```

## 🔧 开发指南

### 添加新语言
1. 在 `lang/` 目录下创建新的语言文件夹
2. 复制现有语言文件并翻译内容
3. 在 `config/app.php` 中添加新语言配置

### 自定义茶文化主题
1. 修改 `tailwind.config.js` 添加新的色彩
2. 在 `resources/css/app.css` 中添加自定义样式
3. 创建新的组件在 `resources/views/components/`

### 添加新功能
1. 创建相应的模型和迁移
2. 添加控制器和路由
3. 创建Livewire组件
4. 更新管理面板

## 📊 功能模块

### 🛒 电商核心
- **产品管理**：多规格、多图片、多语言
- **分类系统**：层级分类、属性筛选
- **购物车**：实时更新、持久化存储
- **订单系统**：完整订单生命周期
- **支付集成**：多种支付方式支持

### 👥 用户系统
- **用户注册/登录**：支持邮箱验证
- **个人中心**：订单管理、地址管理
- **用户组**：支持用户分组和权限

### 🎯 营销功能
- **促销系统**：灵活的促销规则
- **优惠券**：折扣券、满减券
- **用户组促销**：针对特定用户组的优惠

### 📝 内容管理
- **文章系统**：多语言文章管理
- **SEO优化**：自动生成SEO标签
- **媒体管理**：图片上传和优化

### 🛠️ 管理后台
- **Filament面板**：现代化的管理界面
- **数据统计**：销售数据、用户统计
- **系统设置**：多语言、货币设置

## 🌐 部署指南

### 高性能部署 (推荐)

本项目已配置 Laravel Octane 高性能部署，使用 RoadRunner 应用服务器。

#### 环境要求
- **LNMP 环境** (推荐使用 LNMP.org 一键安装包)
- **PHP 8.2+**
- **MySQL 8.0+**
- **Redis**
- **FrankenPHP** (应用服务器)
- **Supervisor** (进程管理)
- **SSL证书**
- **域名配置**：
  - `teanary.com` - 主域名
  - `asset.teanary.com` - 静态资源 CDN 域名

#### 快速部署

**首次部署**（包含所有配置设置）：
```bash
vendor/bin/dep deploy:first teanary
```

**常规部署**（只更新代码，不重复配置）：
```bash
vendor/bin/dep deploy teanary
```

**更新配置**（重新部署 Nginx 和 Supervisor 配置）：
```bash
vendor/bin/dep deploy:config teanary
```

#### 手动部署
```bash
# 使用 Deployer 部署
vendor/bin/dep deploy teanary

# 检查服务状态
vendor/bin/dep octane:check teanary
```

#### 部署文件结构
```
deployment/
├── nginx-teanary-octane.conf    # Nginx 配置
├── supervisor-octane.conf       # Octane 进程管理
└── supervisor-queue.conf        # 队列进程管理
```

#### 性能优势
- **更快的响应时间** - 应用在内存中保持活跃状态
- **更高的并发能力** - 4 个工作进程处理请求
- **CDN 加速** - 静态资源通过 `asset.teanary.com` 域名服务，支持 CDN 加速
- **缓存优化** - 静态资源长期缓存（1年），减少服务器负载
- **自动重启** - 代码变更时自动重启工作进程

#### 静态资源 CDN 配置

项目配置了独立的静态资源域名 `asset.teanary.com`：

- **主域名** (`teanary.com`) - 处理动态请求，静态文件自动重定向到 CDN
- **CDN 域名** (`asset.teanary.com`) - 专门服务静态资源，支持长期缓存
- **缓存策略**：
  - CSS/JS/字体文件：1年缓存
  - 图片/媒体文件：30天缓存
  - 支持 CORS 跨域访问

#### 监控和日志
```bash
# 查看 Octane 状态
vendor/bin/dep octane:check teanary

# 查看队列状态
vendor/bin/dep queue:status teanary

# 查看 Octane 日志
sudo supervisorctl tail -f octane

# 查看队列日志
sudo supervisorctl tail -f teanary-queue

# 查看静态资源日志
tail -f /home/wwwlogs/asset.teanary.com.log

# 检查 LNMP 状态
lnmp status
```

### 传统部署 (备选)

如果不需要高性能部署，也可以使用传统的 PHP-FPM 方式：

1. **服务器要求**
- PHP 8.1+
- MySQL 8.0+
- Redis
- Nginx/Apache
- SSL证书

2. **环境配置**
```bash
# 设置生产环境
APP_ENV=production
APP_DEBUG=false

# 配置缓存
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

3. **优化配置**
```bash
# 优化自动加载
composer install --optimize-autoloader --no-dev

# 缓存配置
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 构建前端资源
npm run build
```

4. **设置队列处理**
```bash
# 启动队列worker
php artisan queue:work
```

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

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 📞 联系我们

- **项目主页**: [https://github.com/your-username/teanary](https://github.com/your-username/teanary)
- **问题反馈**: [Issues](https://github.com/your-username/teanary/issues)
- **邮箱**: hello@teanary.com
- **电话**: +86 18184839903

## 🙏 致谢

感谢所有为这个项目做出贡献的开发者和茶文化爱好者！

---

**Teanary** - 传承千年茶文化，让每一口茶香都充满温度 🍃
