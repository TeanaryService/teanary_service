# 部署指南

本文档说明如何安装和部署 Teanary 项目，包括开发环境和生产环境。

## 📋 目录

- [快速开始](#快速开始) - 开发环境快速安装
- [环境要求](#环境要求)
- [部署方式](#部署方式)
- [使用 Deployer 自动部署](#使用-deployer-自动部署)
- [手动部署](#手动部署)
- [服务器配置](#服务器配置)
- [性能优化](#性能优化)
- [故障排查](#故障排查)

## 快速开始

### 开发环境安装

适合本地开发和测试环境。

#### 环境要求
- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- Redis
- Ollama (可选，用于 AI 翻译)

#### 安装步骤

1. **克隆项目**
```bash
git clone https://gitee.com/teanary/teanary_service.git
cd teanary_service
```

或

```bash
git clone https://github.com/TeanaryService/teanary_srvice.git
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

5. **运行数据库迁移**
```bash
php artisan migrate
php artisan db:seed
```

6. **构建前端资源**
```bash
npm run build
```

7. **启动开发服务器**
```bash
php artisan serve
```

访问 `http://localhost:8000` 查看网站。

#### 多节点同步配置（可选）

如需配置多节点同步，请参考 [多节点数据同步文档](SYNC.md)。

---

## 环境要求

### 服务器要求

- **操作系统**: Linux (推荐 Ubuntu 20.04+ 或 CentOS 7+)
- **PHP**: >= 8.1 (推荐 8.2+)
- **MySQL**: >= 8.0
- **Redis**: >= 6.0
- **Nginx**: >= 1.18
- **Node.js**: >= 16.x (用于构建前端资源)
- **Composer**: >= 2.0

### PHP 扩展要求

```bash
php -m | grep -E 'pdo_mysql|mbstring|xml|curl|zip|gd|redis|opcache'
```

必需的 PHP 扩展：
- `pdo_mysql` - MySQL 数据库连接
- `mbstring` - 多字节字符串处理
- `xml` - XML 处理
- `curl` - HTTP 请求
- `zip` - 压缩文件处理
- `gd` - 图片处理
- `redis` - Redis 连接
- `opcache` - PHP 操作码缓存

### 推荐配置

**最低配置**:
- CPU: 2 核
- 内存: 4GB
- 存储: 50GB SSD

**推荐配置**:
- CPU: 4 核+
- 内存: 8GB+
- 存储: 100GB+ SSD

## 部署方式

### 方式一：使用 Deployer 自动部署（推荐）

Deployer 是一个基于 PHP 的部署工具，可以自动化部署流程。

**优点**:
- ✅ 自动化部署流程
- ✅ 支持零停机部署
- ✅ 自动回滚功能
- ✅ 支持多服务器部署

### 方式二：手动部署

适合小型项目或学习目的。

## 使用 Deployer 自动部署

### 1. 安装 Deployer

```bash
# 全局安装
composer global require deployer/deployer

# 或使用项目依赖
composer require --dev deployer/deployer
```

### 2. 配置部署脚本

项目已包含 `deploy.php` 配置文件，位于项目根目录。

**主要配置项**:

```php
// 仓库地址
set('repository', 'git@gitee.com:teanary/teanary_service.git');

// 保留的发布版本数
set('keep_releases', 3);

// 共享文件和目录
add('shared_files', ['.env', 'public/sitemap.xml']);
add('shared_dirs', ['storage']);
```

### 3. 配置服务器

在 `deploy.php` 中配置服务器信息：

```php
host('production')
    ->set('hostname', 'your-server-ip')
    ->set('port', 22)
    ->set('remote_user', 'www')
    ->setIdentityFile('~/.ssh/id_rsa')
    ->set('deploy_path', '/home/wwwroot/teanary')
    ->set('branch', 'main')
    ->set('http_user', 'www');
```

### 4. 执行部署

**首次部署**:
```bash
vendor/bin/dep deploy:first production
```

**常规部署**:
```bash
vendor/bin/dep deploy production
```

**部署到多个服务器**:
```bash
vendor/bin/dep deploy production staging
```

### 5. 部署流程说明

Deployer 会自动执行以下步骤：

1. **准备阶段**
   - 克隆代码仓库
   - 创建发布目录

2. **构建阶段**
   - 安装 Composer 依赖
   - 安装 NPM 依赖
   - 构建前端资源
   - 运行数据库迁移

3. **发布阶段**
   - 清理缓存
   - 优化应用
   - 清理无关文件
   - 切换当前版本

4. **完成阶段**
   - 重启 PHP-FPM
   - 重启 Supervisor
   - 清理旧版本

## 手动部署

### 1. 克隆代码

```bash
cd /home/wwwroot
git clone https://gitee.com/teanary/teanary_service.git teanary
cd teanary
```

### 2. 安装依赖

```bash
# 安装 PHP 依赖
composer install --no-dev --optimize-autoloader

# 安装前端依赖
npm ci

# 构建前端资源
npm run build
```

### 3. 配置环境

```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
php artisan key:generate

# 编辑 .env 文件，配置数据库、Redis 等
nano .env
```

### 4. 数据库迁移

```bash
# 运行数据库迁移
php artisan migrate --force

# 填充初始数据（可选）
php artisan db:seed --force
```

### 5. 优化应用

```bash
# 缓存配置
php artisan config:cache

# 缓存路由
php artisan route:cache

# 缓存视图
php artisan view:cache
```

### 6. 设置权限

```bash
# 设置存储目录权限
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache

# 设置公共目录权限
chmod -R 755 public
chown -R www:www public
```

## 服务器配置

### Nginx 配置

项目包含示例 Nginx 配置文件：`deployment/nginx-teanary-phpfcgi.conf`

**主要配置要点**:

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

    # 静态资源缓存
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### PHP-FPM 配置

**推荐配置** (`/etc/php/8.2/fpm/pool.d/www.conf`):

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

### Supervisor 配置

项目包含 Supervisor 配置文件：`deployment/supervisor-queue.conf`

**队列工作进程配置**:

```ini
[program:teanary-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/wwwroot/teanary/current/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=2
redirect_stderr=true
stdout_logfile=/home/wwwroot/teanary/storage/logs/queue.log
```

**同步队列工作进程**:

```ini
[program:teanary-sync-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /home/wwwroot/teanary/current/artisan queue:work redis --queue=sync --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=1
redirect_stderr=true
stdout_logfile=/home/wwwroot/teanary/storage/logs/sync-queue.log
```

**安装 Supervisor 配置**:

```bash
# 复制配置文件
sudo cp deployment/supervisor-queue.conf /etc/supervisor/conf.d/teanary-queue.conf

# 重新加载配置
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start teanary-queue:*
```

### Laravel Octane 配置（可选）

Laravel Octane 提供高性能应用服务器，适合高并发场景。

**安装 Octane**:

```bash
composer require laravel/octane
php artisan octane:install
```

**启动 Octane**:

```bash
# 使用 Swoole
php artisan octane:start --server=swoole

# 或使用 RoadRunner
php artisan octane:start --server=roadrunner
```

**使用 Supervisor 管理 Octane**:

```ini
[program:teanary-octane]
command=php /home/wwwroot/teanary/current/artisan octane:start --server=swoole --host=127.0.0.1 --port=8000
autostart=true
autorestart=true
user=www
redirect_stderr=true
stdout_logfile=/home/wwwroot/teanary/storage/logs/octane.log
```

## 性能优化

### 1. OPcache 配置

编辑 `/etc/php/8.2/fpm/php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

### 2. Redis 配置

编辑 `/etc/redis/redis.conf`:

```conf
maxmemory 2gb
maxmemory-policy allkeys-lru
```

### 3. MySQL 配置

编辑 `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
query_cache_size = 64M
```

### 4. Nginx 优化

```nginx
# 启用 Gzip 压缩
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss;

# 连接数限制
worker_processes auto;
worker_connections 1024;
```

## 定时任务配置

Laravel 的调度器需要配置 cron 任务：

```bash
# 编辑 crontab
crontab -e

# 添加以下行（每分钟执行一次）
* * * * * cd /home/wwwroot/teanary/current && php artisan schedule:run >> /dev/null 2>&1
```

## 故障排查

### 1. 权限问题

```bash
# 检查目录权限
ls -la storage bootstrap/cache

# 修复权限
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache
```

### 2. 队列不工作

```bash
# 检查 Supervisor 状态
sudo supervisorctl status

# 查看队列日志
tail -f storage/logs/queue.log

# 重启队列工作进程
sudo supervisorctl restart teanary-queue:*
```

### 3. 缓存问题

```bash
# 清除所有缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 重新缓存
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. 数据库连接问题

```bash
# 测试数据库连接
php artisan tinker
>>> DB::connection()->getPdo();

# 检查 .env 配置
cat .env | grep DB_
```

### 5. 日志查看

```bash
# 查看应用日志
tail -f storage/logs/laravel.log

# 查看 Nginx 错误日志
tail -f /var/log/nginx/error.log

# 查看 PHP-FPM 日志
tail -f /var/log/php8.2-fpm.log
```

## 安全建议

1. **使用 HTTPS**: 配置 SSL 证书，强制 HTTPS 访问
2. **防火墙配置**: 只开放必要的端口（80, 443, 22）
3. **定期更新**: 保持系统和依赖包的最新版本
4. **备份数据**: 定期备份数据库和文件
5. **环境变量**: 确保 `.env` 文件权限正确（600）
6. **API Key 安全**: 使用强密码和 API Key

## 备份策略

### 数据库备份

```bash
# 创建备份脚本
cat > /home/wwwroot/backup-db.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u username -p password teanary > /backup/teanary_$DATE.sql
find /backup -name "teanary_*.sql" -mtime +7 -delete
EOF

chmod +x /home/wwwroot/backup-db.sh

# 添加到 crontab（每天凌晨2点备份）
0 2 * * * /home/wwwroot/backup-db.sh
```

### 文件备份

```bash
# 备份存储目录
tar -czf /backup/storage_$(date +%Y%m%d).tar.gz /home/wwwroot/teanary/current/storage
```

## 相关文档

- [多节点数据同步](SYNC.md) - 多节点部署配置
- [流量统计功能](traffic-statistics.md) - 流量统计配置
- [发布新版本指南](RELEASE.md) - 版本发布流程

---

**最后更新**: 2026-01-17
