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

6. **配置雪花ID机器ID（多节点部署必需）**
在多节点部署时，每个节点必须配置不同的机器ID以确保ID唯一性：
```env
# 雪花ID机器ID，范围：0-1023
# 单节点部署可以不配置，系统会自动使用IP地址或随机数
# 多节点部署时，每个节点必须配置不同的值
SNOWFLAKE_MACHINE_ID=1
```

7. **运行数据库迁移**
```bash
php artisan migrate
```

8. **填充示例数据**
```bash
php artisan db:seed
```

9. **构建前端资源**
```bash
npm run build
```

10. **启动开发服务器**
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

## 📡 API 接口文档

### 文章上传接口

**接口地址**: `POST /api/articles/add`

**功能**: 上传文章，支持多语言、图片上传和内容图片占位符替换

**请求参数**:

```json
{
  "slug": "article-slug",
  "main_image": {
    "image_id": "main-img-1",
    "contents": "base64编码的图片数据"
  },
  "content_images": [
    {
      "image_id": "img-1",
      "original_url": "http://example.com/img1.jpg",
      "contents": "base64编码的图片数据"
    }
  ],
  "translations": [
    {
      "language_id": 1,
      "title": "文章标题",
      "summary": "文章摘要",
      "content": "文章内容，可以使用 {{image:img-1}} 作为图片占位符"
    }
  ]
}
```

**参数说明**:
- `slug` (必填): 文章URL别名，必须唯一
- `main_image` (可选): 主图
  - `image_id`: 图片ID
  - `contents`: base64编码的图片数据
- `content_images` (可选): 内容图片数组
  - `image_id`: 图片ID，用于在内容中引用
  - `original_url`: 原始图片URL
  - `contents`: base64编码的图片数据
- `translations` (必填): 多语言翻译数组，至少需要一种语言
  - `language_id`: 语言ID
  - `title`: 文章标题
  - `summary`: 文章摘要（可选）
  - `content`: 文章内容，可以使用 `{{image:图片ID}}` 作为占位符

**响应示例**:

```json
{
  "message": "文章创建成功",
  "data": {
    "id": 1,
    "slug": "article-slug",
    "is_published": true,
    "articleTranslations": [...],
    "media": [...]
  }
}
```

**使用示例** (cURL):

```bash
curl -X POST http://your-domain.com/api/articles/add \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "my-article",
    "translations": [
      {
        "language_id": 1,
        "title": "我的文章",
        "content": "这是文章内容"
      }
    ]
  }'
```

### 商品上传接口

**接口地址**: `POST /api/products/add`

**功能**: 上传商品，支持多语言、多规格、分类自动创建、图片上传

**请求参数**:

```json
{
  "slug": "product-slug",
  "main_image": {
    "image_id": "main-img-1",
    "contents": "base64编码的图片数据"
  },
  "content_images": [
    {
      "image_id": "img-1",
      "original_url": "http://example.com/img1.jpg",
      "contents": "base64编码的图片数据"
    }
  ],
  "translations": [
    {
      "language_id": 1,
      "name": "商品名称",
      "short_description": "简短描述",
      "description": "详细描述，可以使用 {{image:img-1}} 作为图片占位符"
    }
  ],
  "categories": [
    {
      "slug": "category-slug",
      "parent_id": null,
      "translations": [
        {
          "language_id": 1,
          "name": "分类名称",
          "description": "分类描述"
        }
      ]
    }
  ],
  "variants": [
    {
      "sku": "SKU-001",
      "price": 99.99,
      "cost": 50.00,
      "stock": 100,
      "weight": 1.5,
      "length": 10,
      "width": 5,
      "height": 3,
      "specification_values": [
        {
          "specification_id": 1,
          "specification_value_id": 1
        }
      ]
    }
  ]
}
```

**参数说明**:
- `slug` (必填): 商品URL别名，必须唯一
- `main_image` (可选): 主图，格式同文章接口
- `content_images` (可选): 内容图片数组，格式同文章接口
- `translations` (必填): 多语言翻译数组，至少需要一种语言
  - `language_id`: 语言ID
  - `name`: 商品名称
  - `short_description`: 简短描述（可选）
  - `description`: 详细描述，可以使用 `{{image:图片ID}}` 作为占位符
- `categories` (可选): 分类数组，如果分类不存在会自动创建
  - `slug`: 分类slug
  - `parent_id`: 父分类ID（可选）
  - `translations`: 分类的多语言翻译数组
- `variants` (可选): 商品规格数组
  - `sku`: SKU编码，必须唯一
  - `price`: 价格（可选）
  - `cost`: 成本（可选）
  - `stock`: 库存（可选，默认0）
  - `weight`: 重量（可选）
  - `length`: 长度（可选）
  - `width`: 宽度（可选）
  - `height`: 高度（可选）
  - `specification_values`: 规格值关联数组
    - `specification_id`: 规格ID
    - `specification_value_id`: 规格值ID

**响应示例**:

```json
{
  "message": "商品创建成功",
  "data": {
    "id": 1,
    "slug": "product-slug",
    "status": "active",
    "productTranslations": [...],
    "productCategories": [...],
    "productVariants": [
      {
        "id": 1,
        "sku": "SKU-001",
        "price": 99.99,
        "specificationValues": [...]
      }
    ],
    "media": [...]
  }
}
```

**使用示例** (cURL):

```bash
curl -X POST http://your-domain.com/api/products/add \
  -H "Content-Type: application/json" \
  -d '{
    "slug": "my-product",
    "translations": [
      {
        "language_id": 1,
        "name": "我的商品",
        "short_description": "简短描述"
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

**注意事项**:
1. 图片数据必须是有效的 base64 编码的 PNG 格式
2. 如果分类不存在，系统会自动创建分类及其翻译
3. SKU 必须全局唯一
4. 商品 slug 必须全局唯一
5. 内容中的图片占位符 `{{image:图片ID}}` 会被自动替换为实际图片URL

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

## 🛠️ 开发工具

### Bin 目录工具说明

`bin/` 目录包含了项目使用的各种命令行工具，这些工具由 Composer 自动管理。

#### 开发工具

**PHPStan** (`phpstan` / `phpstan.phar`)
- 用途：检测代码中的类型错误、未定义方法、潜在 bug 等
- 使用：`./bin/phpstan analyse` 或 `composer analyse`
- 配置：`phpstan.neon`

**Pint** (`pint`)
- 用途：自动修复代码风格问题，统一代码格式（基于 PHP-CS-Fixer）
- 使用：`./bin/pint` (检查并修复) 或 `./bin/pint --test` (仅检查不修改)
- 配置：`pint.json`

**PHPUnit** (`phpunit`)
- 用途：运行单元测试和功能测试
- 使用：`./bin/phpunit` 或 `composer test`
- 配置：`phpunit.xml`

**PsySH** (`psysh`)
- 用途：交互式调试和测试 PHP 代码
- 使用：`./bin/psysh`

**Tinker**
- 用途：在 Laravel 应用上下文中交互式执行代码
- 使用：`php artisan tinker`

#### 部署和服务器工具

**Deployer** (`dep`)
- 用途：自动化部署应用到服务器
- 使用：`./bin/dep deploy production`
- 配置：`deploy.php`

**RoadRunner Worker** (`roadrunner-worker`)
- 用途：高性能 PHP 应用服务器（用于 Laravel Octane）
- 使用：通过 Laravel Octane 自动管理

**Swoole Server** (`swoole-server`)
- 用途：高性能 PHP 应用服务器（用于 Laravel Octane）
- 使用：通过 Laravel Octane 自动管理

#### 常用命令

```bash
# 代码质量检查
composer analyse          # 运行 PHPStan 静态分析
./bin/pint               # 修复代码风格
./bin/pint --test        # 仅检查代码风格

# 测试
composer test            # 运行所有测试
./bin/phpunit            # 运行测试（直接调用）
./bin/phpunit --filter   # 运行特定测试

# 代码质量全检查
composer check           # 运行所有质量检查（代码风格 + 静态分析 + 测试）
```

## 🧪 测试指南

### 测试类型说明

#### Unit 测试（单元测试）
**位置**: `tests/Unit/`

**用途**: 测试单个类或方法的功能，通常使用Mock来隔离依赖

**特点**:
- 测试速度快
- 不依赖外部资源（数据库、网络等）
- 专注于单个组件的逻辑
- 使用 `RefreshDatabase` trait 来重置数据库状态

**示例**:
```php
// 测试Model的关系
public function testUserRelationship()
{
    $order = new Order();
    $relation = $order->user();
    $this->assertInstanceOf(BelongsTo::class, $relation);
}

// 测试Service的方法
public function testCalculateVariantPrice()
{
    $service = new PromotionService();
    $result = $service->calculateVariantPrice($variant, 1);
    $this->assertEquals(100, $result['final_price']);
}
```

#### Feature 测试（功能测试）
**位置**: `tests/Feature/`

**用途**: 测试完整的功能流程，包括HTTP请求、路由、控制器、中间件等

**特点**:
- 测试完整的用户流程
- 可以测试HTTP请求和响应
- 测试路由、中间件、认证等
- 更接近真实使用场景

**示例**:
```php
// 测试API端点
public function testCanCreateArticle()
{
    $response = $this->postJson('/api/articles/add', [
        'slug' => 'test-article',
        'translations' => [...],
    ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('articles', ['slug' => 'test-article']);
}
```

### 测试覆盖情况

#### 枚举类测试（100%覆盖）
- ✅ OrderStatusEnumTest
- ✅ PaymentMethodEnumTest  
- ✅ ProductStatusEnumTest
- ✅ PromotionConditionTypeEnumTest
- ✅ PromotionDiscountTypeEnumTest
- ✅ PromotionTypeEnumTest
- ✅ ShippingMethodEnumTest

#### Model类测试（约30%覆盖）
- ✅ AddressTest
- ✅ ArticleTest
- ✅ ArticleTranslationTest
- ✅ CartTest
- ✅ CartItemTest
- ✅ CategoryTest
- ✅ CountryTest
- ✅ CurrencyTest
- ✅ LanguageTest
- ✅ OrderTest
- ✅ OrderItemTest
- ✅ ProductTest
- ✅ ProductVariantTest
- ✅ PromotionTest
- ✅ PromotionRuleTest
- ✅ UserTest
- ✅ ZoneTest

#### Service类测试（约50%覆盖）
- ✅ CartServiceTest
- ✅ LocaleCurrencyServiceTest
- ✅ PaymentServiceTest
- ✅ PromotionServiceTest
- ✅ ShippingServiceTest
- ✅ ShippingCalculatorFactoryTest
- ✅ SFExpressCalculatorTest
- ✅ EMSCalculatorTest

#### Feature测试
- ✅ ArticleApiTest
- ✅ ProductApiTest
- ✅ LanguageCurrencySwitcherTest

### 运行测试

```bash
# 运行所有测试
php bin/phpunit
# 或
composer test

# 运行Unit测试
php bin/phpunit tests/Unit/

# 运行Feature测试
php bin/phpunit tests/Feature/

# 运行特定测试类
php bin/phpunit tests/Unit/CartServiceTest.php

# 运行特定测试方法
php bin/phpunit --filter testGetCart

# 运行枚举类测试
php bin/phpunit tests/Unit/ --filter Enum
```

### 测试最佳实践

1. **测试命名**: 使用描述性的测试方法名，如 `testCanCreateArticle()`
2. **AAA模式**: Arrange（准备）-> Act（执行）-> Assert（断言）
3. **单一职责**: 每个测试方法只测试一个功能点
4. **独立性**: 测试之间不应该相互依赖
5. **使用Factory**: 使用Factory创建测试数据，而不是直接操作数据库
6. **清理数据**: 使用 `RefreshDatabase` trait 确保每次测试后数据库状态干净

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
