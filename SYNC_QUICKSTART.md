# 数据同步快速开始指南

## 快速部署步骤

### 1. 运行数据库迁移

```bash
php artisan migrate
```

### 2. 配置环境变量

**在国内服务器（.env）：**
```env
SYNC_ENABLED=true
SYNC_NODE=domestic
SYNC_DOMESTIC_URL=https://domestic.example.com
SYNC_DOMESTIC_API_KEY=your-domestic-api-key
SYNC_OVERSEAS_URL=https://overseas.example.com
SYNC_OVERSEAS_API_KEY=your-overseas-api-key
```

**在国外服务器（.env）：**
```env
SYNC_ENABLED=true
SYNC_NODE=overseas
SYNC_DOMESTIC_URL=https://domestic.example.com
SYNC_DOMESTIC_API_KEY=your-domestic-api-key
SYNC_OVERSEAS_URL=https://overseas.example.com
SYNC_OVERSEAS_API_KEY=your-overseas-api-key
```

**重要**：两个节点的 API Key 必须匹配！

### 3. 在模型中启用同步

例如，在 `Product` 模型中：

```php
use App\Traits\Syncable;

class Product extends Model
{
    use Syncable;
    // ... 其他代码
}
```

### 4. 启动队列工作进程

```bash
php artisan queue:work --queue=sync
```

或使用 Supervisor（推荐，见 `deployment/supervisor-queue.conf` 示例）

### 5. 确保定时任务运行

```bash
php artisan schedule:work
```

或在 crontab 中添加：
```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 测试同步

### 1. 创建测试数据

```bash
php artisan tinker
```

```php
$product = \App\Models\Product::create([
    'slug' => 'test-product-' . time(),
    'status' => 'active',
]);
```

### 2. 检查同步日志

```bash
php artisan tinker
```

```php
\App\Models\SyncLog::where('status', 'pending')->count();
\App\Models\SyncLog::latest()->first();
```

### 3. 手动触发同步

```bash
php artisan sync:pending --queue
```

## 常见问题

### Q: 同步不工作？
A: 检查以下几点：
1. `SYNC_ENABLED=true` 已设置
2. 模型已添加 `Syncable` trait
3. 模型在 `config/sync.php` 的 `sync_models` 列表中
4. 队列工作进程正在运行

### Q: 如何查看同步状态？
A: 
```bash
# 查看待同步记录数
php artisan tinker
\App\Models\SyncLog::where('status', 'pending')->count();

# 查看失败的记录
\App\Models\SyncLog::where('status', 'failed')->get();
```

### Q: 如何重试失败的同步？
A:
```bash
php artisan sync:retry-failed --queue
```

### Q: 同步是实时的吗？
A: 不是，同步是异步的。数据变更会立即记录到 `sync_logs` 表，但实际同步通过队列异步执行，通常延迟几秒到几分钟。

## 下一步

查看完整文档：`SYNC_README.md`
