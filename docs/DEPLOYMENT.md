# 部署指南

本文档说明如何快速部署 Teanary 项目，包括开发环境和生产环境。

## 📋 目录

- [开发环境](#开发环境) - 本地开发快速启动
- [生产环境部署](#生产环境部署) - 使用 Deployer 一键部署
- [环境要求](#环境要求) - 系统要求说明
- [常见问题](#常见问题) - 故障排查

---

## 开发环境

### 快速启动（3 步）

```bash
# 1. 克隆项目
git clone https://github.com/TeanaryService/teanary_srvice.git
cd teanary_service

# 或使用 Gitee（国内推荐）
git clone https://gitee.com/teanary/teanary_service.git
cd teanary_service

# 2. 安装依赖
composer install
npm install

# 3. 配置环境并启动
cp .env.example .env
php artisan key:generate
# 编辑 .env 文件，配置数据库连接信息
php artisan migrate
php artisan db:seed

# 4. 启动开发服务器（一键启动所有服务）
composer dev
```

访问 `http://localhost:8013` 查看网站。

> 💡 **`composer dev` 会自动启动**：
> - Web 服务器（端口 8013）
> - 队列服务
> - 定时任务
> - 日志监控
> - 前端构建工具（Vite）

### 开发环境说明

- **Web 服务器**：`http://localhost:8013`
- **队列服务**：自动处理后台任务
- **前端热更新**：修改前端代码自动刷新
- **日志监控**：实时查看应用日志

### 停止开发服务器

按 `Ctrl+C` 停止所有服务。

---

## 生产环境部署

### 使用 Deployer 自动部署（推荐）

Deployer 是 Laravel 官方推荐的部署工具，可以一键完成所有部署步骤。

#### 1. 准备部署配置

```bash
# 复制部署配置文件
cp docs/example.deploy.php deploy.php
```

#### 2. 编辑 deploy.php

打开 `deploy.php`，修改以下配置：

```php
// 修改仓库地址
set('repository', 'git@gitee.com:teanary/teanary_service.git');

// 修改服务器配置
host('production')  // 服务器名称
    ->set('hostname', '192.168.1.100')  // 服务器 IP
    ->set('port', 22)  // SSH 端口
    ->set('remote_user', 'deployer')  // SSH 用户名
    ->setIdentityFile('~/.ssh/id_rsa')  // SSH 密钥路径
    ->set('deploy_path', '/home/wwwroot/teanary')  // 部署路径
    ->set('branch', 'main')  // 部署分支
    ->set('http_user', 'www')  // Web 服务器用户
    ->set('php_fpm_service', 'php-fpm.service')  // PHP-FPM 服务名
    ->set('supervisor_service', 'supervisor.service');  // Supervisor 服务名
```

#### 3. 配置 SSH 免密登录

```bash
# 生成 SSH 密钥（如果还没有）
ssh-keygen -t rsa -b 4096

# 复制公钥到服务器
ssh-copy-id deployer@192.168.1.100
```

#### 4. 一键部署

```bash
# 部署到生产环境
./bin/dep deploy production

# 或部署到指定服务器
./bin/dep deploy production staging
```

#### 5. 部署流程

Deployer 会自动执行：

1. ✅ 克隆代码仓库
2. ✅ 安装 Composer 依赖
3. ✅ 安装 NPM 依赖并构建前端资源
4. ✅ 运行数据库迁移（自动执行）
5. ✅ 清理缓存和优化
6. ✅ 切换当前版本
7. ✅ 重启 PHP-FPM 和 Supervisor
8. ✅ 清理旧版本（保留 3 个版本）

#### 6. 首次部署后的配置

**重要**：首次部署后，需要完成以下步骤：

##### 6.1 配置环境文件

```bash
# SSH 登录服务器
ssh deployer@192.168.1.100

# 进入部署目录
cd /home/wwwroot/teanary/current

# 编辑环境配置
nano .env
```

配置数据库、Redis、邮件等：

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teanary
DB_USERNAME=your_username
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

##### 6.2 运行数据库迁移和填充数据

**首次部署必须执行**：

```bash
# 在服务器上执行（确保 .env 已配置好数据库信息）
cd /home/wwwroot/teanary/current

# 运行数据库迁移（创建数据表）
php artisan migrate --force

# 填充初始数据（语言、货币、国家等基础数据）
php artisan db:seed --force
```

> ⚠️ **注意**：
> - `--force` 参数用于生产环境，跳过确认提示
> - 首次部署必须运行 `migrate` 和 `db:seed`
> - 后续更新只需要运行 `migrate`（不需要 `db:seed`）

##### 6.3 创建管理员账号（可选）

如果需要创建管理员账号：

```bash
php artisan tinker
```

然后在 Tinker 中执行：

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('your_password'),
]);
$user->assignRole('manager');
```

#### 7. 后续更新

代码更新后，只需再次运行：

```bash
./bin/dep deploy production
```

Deployer 会自动运行数据库迁移（如果有新的迁移文件）。

**手动运行迁移**（如果需要）：

```bash
# SSH 登录服务器
ssh deployer@192.168.1.100

# 进入部署目录
cd /home/wwwroot/teanary/current

# 运行迁移（仅迁移，不填充数据）
php artisan migrate --force
```

> 💡 **提示**：后续更新通常不需要填充数据，除非有新的 Seeder 文件。

---

## 环境要求

### 开发环境

- **PHP**: >= 8.2
- **Composer**: 最新版本
- **Node.js**: >= 16.x
- **MySQL**: >= 8.0
- **Redis**: >= 6.0

### 生产环境

- **操作系统**: Linux (推荐 Ubuntu 20.04+ 或 CentOS 7+)
- **PHP**: >= 8.2 (推荐 8.2+)
- **MySQL**: >= 8.0
- **Redis**: >= 6.0
- **Nginx**: >= 1.18
- **Supervisor**: 用于管理队列进程

### PHP 扩展要求

```bash
php -m | grep -E "pdo_mysql|mbstring|xml|curl|zip|gd|redis"
```

必需扩展：
- `pdo_mysql` - MySQL 数据库
- `mbstring` - 字符串处理
- `xml` - XML 处理
- `curl` - HTTP 请求
- `zip` - 压缩文件
- `gd` - 图片处理
- `redis` - Redis 缓存

---

## 服务器配置

### Nginx 配置

项目包含示例配置文件：`deployment/nginx-teanary-phpfcgi.conf`

主要配置：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /home/wwwroot/teanary/current/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Supervisor 配置

项目包含示例配置文件：`deployment/supervisor-queue.conf`

安装配置：

```bash
# 复制配置文件
sudo cp deployment/supervisor-queue.conf /etc/supervisor/conf.d/teanary-queue.conf

# 修改配置文件中的路径
sudo nano /etc/supervisor/conf.d/teanary-queue.conf

# 重新加载配置
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start teanary-queue:*
```

---

## 常见问题

### 开发环境

**Q: `composer dev` 启动失败？**

A: 检查端口是否被占用：
```bash
# 检查端口 8013
lsof -i :8013
```

**Q: 前端资源没有更新？**

A: 确保 `npm run dev` 正在运行，或重新构建：
```bash
npm run build
```

**Q: 队列任务不执行？**

A: 确保队列服务正在运行：
```bash
php artisan queue:work
```

### 生产环境

**Q: 部署失败，提示权限错误？**

A: 检查服务器目录权限：
```bash
# 设置部署目录权限
sudo chown -R deployer:www /home/wwwroot/teanary
sudo chmod -R 775 /home/wwwroot/teanary
```

**Q: 部署后页面显示 500 错误？**

A: 检查日志：
```bash
# 查看 Laravel 日志
tail -f /home/wwwroot/teanary/current/storage/logs/laravel.log

# 查看 Nginx 错误日志
tail -f /var/log/nginx/error.log
```

**Q: 静态资源 404？**

A: 检查 Nginx 配置中的 `root` 路径是否正确指向 `current/public`。

**Q: 队列任务不执行？**

A: 检查 Supervisor 状态：
```bash
sudo supervisorctl status
sudo supervisorctl restart teanary-queue:*
```

---

## 多节点同步配置

如需配置多节点数据同步，请参考 [多节点数据同步文档](SYNC.md)。

---

## 性能优化

### 生产环境优化

部署后自动执行的优化：

- ✅ 配置缓存
- ✅ 路由缓存
- ✅ 视图缓存
- ✅ 清理开发文件

### 手动优化

```bash
# 进入部署目录
cd /home/wwwroot/teanary/current

# 缓存配置
php artisan config:cache

# 缓存路由
php artisan route:cache

# 缓存视图
php artisan view:cache
```

### 使用 Laravel Octane（可选）

如需更高性能，可以配置 Laravel Octane，参考 [Laravel Octane 文档](https://laravel.com/docs/octane)。

---

## 相关文档

- [多节点数据同步](SYNC.md) - 多节点配置指南
- [系统架构](ARCHITECTURE.md) - 系统架构说明
- [文档索引](README.md) - 查看完整文档目录
- [Teanary Vision（英文）](VISION.md) - AI‑Native 愿景与设计方向

---

**最后更新**: 2026-01-27
