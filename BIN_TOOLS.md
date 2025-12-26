# Bin 目录工具说明

`bin/` 目录包含了项目使用的各种命令行工具，这些工具由 Composer 自动管理。

## 开发工具

### `phpstan` / `phpstan.phar`
**PHP 静态分析工具**
- 用途：检测代码中的类型错误、未定义方法、潜在 bug 等
- 使用：`./bin/phpstan analyse` 或 `composer analyse`
- 配置：`phpstan.neon`

### `pint`
**Laravel 代码风格工具**（基于 PHP-CS-Fixer）
- 用途：自动修复代码风格问题，统一代码格式
- 使用：`./bin/pint` (检查) 或 `./bin/pint --test` (仅检查不修改)
- 配置：`pint.json`

### `phpunit`
**PHP 单元测试框架**
- 用途：运行单元测试和功能测试
- 使用：`./bin/phpunit` 或 `composer test`
- 配置：`phpunit.xml`

### `psysh`
**PHP 交互式 Shell**
- 用途：交互式调试和测试 PHP 代码
- 使用：`./bin/psysh`

### `tinker`
**Laravel REPL**
- 用途：在 Laravel 应用上下文中交互式执行代码
- 使用：`php artisan tinker`

## 部署和服务器工具

### `dep`
**Deployer 部署工具**
- 用途：自动化部署应用到服务器
- 使用：`./bin/dep deploy production`
- 配置：`deploy.php`

### `roadrunner-worker`
**RoadRunner 工作进程**
- 用途：高性能 PHP 应用服务器（用于 Laravel Octane）
- 使用：通过 Laravel Octane 自动管理

### `swoole-server`
**Swoole 服务器**
- 用途：高性能 PHP 应用服务器（用于 Laravel Octane）
- 使用：通过 Laravel Octane 自动管理

## 辅助工具

### `var-dump-server`
**变量转储服务器**
- 用途：收集和显示 `dump()` 和 `dd()` 的输出
- 使用：`./bin/var-dump-server` 或 `php artisan dump-server`

### `blade-icons-generate`
**Blade 图标生成器**
- 用途：生成 Blade 图标组件
- 使用：通过 Laravel Blade Icons 包自动调用

### `carbon`
**Carbon 日期库 CLI**
- 用途：Carbon 日期时间库的命令行工具
- 使用：很少直接使用

### `php-parse`
**PHP 解析器**
- 用途：解析 PHP 代码的工具
- 使用：通常由其他工具内部使用

### `patch-type-declarations`
**类型声明补丁工具**
- 用途：修复 PHP 类型声明问题
- 使用：通常由 IDE 或静态分析工具自动调用

## 常用命令

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

