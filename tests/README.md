# 测试文档

本项目使用 PHPUnit 进行单元测试和功能测试。

## 目录结构

```
tests/
├── TestCase.php              # 基础测试类
├── CreatesApplication.php    # 应用创建 Trait
├── Unit/                     # 单元测试
│   ├── CartServiceTest.php
│   ├── PromotionServiceTest.php
│   └── ExampleTest.php
├── Feature/                  # 功能测试
│   ├── Api/
│   │   └── ArticleControllerTest.php
│   ├── HomePageTest.php
│   └── ExampleTest.php
└── Helpers/                  # 测试辅助工具
    └── TestHelpers.php
```

## 运行测试

### 运行所有测试

```bash
php artisan test
```

或者使用 PHPUnit 直接运行：

```bash
./vendor/bin/phpunit
```

### 运行特定测试套件

```bash
# 只运行单元测试
php artisan test --testsuite=Unit

# 只运行功能测试
php artisan test --testsuite=Feature
```

### 运行特定测试文件

```bash
php artisan test tests/Unit/CartServiceTest.php
```

### 运行特定测试方法

```bash
php artisan test --filter test_returns_cart_for_authenticated_user
```

## 测试配置

测试配置位于 `phpunit.xml`，主要配置包括：

- **数据库**: 使用 SQLite 内存数据库 (`:memory:`)
- **缓存**: 使用数组缓存驱动
- **队列**: 使用同步队列驱动
- **邮件**: 使用数组邮件驱动

## 编写测试

### 单元测试示例

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;

class MyServiceTest extends TestCase
{
    /** @test */
    public function it_does_something()
    {
        // 测试逻辑
        $this->assertTrue(true);
    }
}
```

### 功能测试示例

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_access_a_route()
    {
        $response = $this->get('/some-route');
        
        $response->assertStatus(200);
    }
}
```

## 测试辅助工具

### TestHelpers

`Tests\Helpers\TestHelpers` 提供了一些常用的测试辅助方法：

```php
use Tests\Helpers\TestHelpers;

// 创建并登录用户
$user = TestHelpers::actingAsUser();

// 创建管理员用户
$admin = TestHelpers::actingAsAdmin();
```

## 数据库迁移

测试会自动运行数据库迁移。如果需要在测试中使用特定的数据，可以使用：

- **Factories**: 使用模型工厂创建测试数据
- **Seeders**: 使用数据库填充器填充测试数据

## 注意事项

1. 测试使用独立的测试数据库，不会影响开发数据库
2. 每个测试后会自动清理数据库（使用 `RefreshDatabase` trait）
3. 测试环境会自动清除缓存
4. 确保所有依赖的工厂文件都已创建

## 持续集成

在 CI/CD 环境中运行测试：

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan test
```

## 测试覆盖率

生成测试覆盖率报告：

```bash
./vendor/bin/phpunit --coverage-html coverage
```

覆盖率报告将生成在 `coverage/` 目录中。

