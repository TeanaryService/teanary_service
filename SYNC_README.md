# 数据双向同步方案说明

## 概述

这是一个与主代码解耦的双向数据同步方案，用于在国内外两个节点之间同步数据变更（增删改），以时间最新为准。

**支持同步的内容：**
- 数据库记录（模型数据）
- 媒体文件（图片、资源文件等）
- 文件转换（缩略图等）

## 架构设计

### 核心组件

1. **SyncService** (`app/Services/SyncService.php`)
   - 负责记录数据变更
   - 执行同步到远程节点
   - 接收来自远程节点的同步数据

2. **Syncable Trait** (`app/Traits/Syncable.php`)
   - 可复用的同步功能
   - 监听模型的 created、updated、deleted 事件
   - 自动记录需要同步的数据变更

3. **SyncLog 模型** (`app/Models/SyncLog.php`)
   - 记录所有同步任务
   - 跟踪同步状态（pending、processing、completed、failed）

4. **SyncStatus 模型** (`app/Models/SyncStatus.php`)
   - 记录每个模型的最后同步状态
   - 通过哈希值检测数据变更，避免重复同步

5. **SyncDataJob** (`app/Jobs/SyncDataJob.php`)
   - 异步队列任务
   - 执行实际的同步操作
   - 支持自动重试

6. **SyncController** (`app/Http/Controllers/Api/SyncController.php`)
   - 接收来自远程节点的同步请求
   - API Key 验证
   - 处理同步数据
   - 提供文件下载端点（用于同步媒体文件）

## 文件同步功能

### 支持的媒体类型

系统自动同步所有通过 Spatie Media Library 管理的媒体文件，包括：
- 产品图片
- 分类图片
- 文章图片
- 用户头像
- 产品变体图片
- 其他所有媒体资源

### 文件同步流程

1. **文件上传时**：
   - 当媒体文件被添加到模型时，Media 模型的变更会被自动记录
   - 同步数据包含文件的下载 URL（带安全令牌）

2. **同步到远程节点**：
   - 远程节点接收到 Media 模型的同步数据
   - 自动从源节点下载实际文件内容
   - 保存到本地存储，保持相同的路径结构

3. **文件转换同步**：
   - 如果源文件有转换版本（如缩略图），也会自动同步
   - 转换文件使用相同的下载机制

### 文件下载安全

- 文件下载 URL 包含临时令牌（10分钟有效期）
- 令牌验证确保只有授权的同步请求可以下载文件
- 支持下载原始文件和转换文件

### 文件存储

- 文件保存在配置的磁盘上（默认：`public`）
- 保持与源节点相同的路径结构
- 支持本地存储和云存储（S3等）

## 安装和配置

### 1. 运行数据库迁移

```bash
php artisan migrate
```

这将创建以下表：
- `sync_logs` - 同步日志表
- `sync_status` - 同步状态表

### 2. 配置环境变量

在 `.env` 文件中添加以下配置：

```env
# 启用同步功能
SYNC_ENABLED=true

# 当前节点标识（domestic 或 overseas）
SYNC_NODE=domestic

# 国内节点配置（如果在国外服务器上）
SYNC_DOMESTIC_URL=https://domestic.example.com
SYNC_DOMESTIC_API_KEY=your-secret-api-key-here
SYNC_DOMESTIC_TIMEOUT=30

# 国外节点配置（如果在国内服务器上）
SYNC_OVERSEAS_URL=https://overseas.example.com
SYNC_OVERSEAS_API_KEY=your-secret-api-key-here
SYNC_OVERSEAS_TIMEOUT=30

# 同步队列配置（可选）
SYNC_QUEUE=sync
SYNC_RETRY_TIMES=3
SYNC_RETRY_DELAY=60
SYNC_BATCH_SIZE=100
SYNC_TIMEOUT=300
```

### 3. 在模型中启用同步

在需要同步的模型中添加 `Syncable` trait：

```php
use App\Traits\Syncable;

class Product extends Model
{
    use Syncable;
    // ... 其他代码
}
```

**注意**：配置文件 `config/sync.php` 中已经列出了需要同步的模型列表。如果添加了新的模型，需要：

1. 在模型中添加 `Syncable` trait
2. 在 `config/sync.php` 的 `sync_models` 数组中添加模型类名

### 4. 配置队列

确保队列工作进程正在运行：

```bash
php artisan queue:work --queue=sync
```

或者使用 Supervisor（推荐）：

```ini
[program:teanary-sync-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=sync --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www
numprocs=1
```

### 5. 配置定时任务

确保 Laravel 的调度器正在运行：

```bash
php artisan schedule:work
```

或者在 crontab 中添加：

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 工作原理

### 数据变更监听

1. 当模型发生变更（created、updated、deleted）时，`Syncable` trait 会自动触发
2. `SyncService::recordSync()` 方法会：
   - 检查模型是否在同步列表中
   - 检查数据是否真的变更（通过哈希值）
   - 为每个目标节点创建一条 `SyncLog` 记录

### 同步执行

1. 定时任务每分钟执行 `sync:pending` 命令
2. 命令获取所有待同步的记录，并分发到队列
3. `SyncDataJob` 执行实际的同步操作：
   - 发送 HTTP POST 请求到远程节点的 `/api/sync/receive` 端点
   - 包含 API Key 认证
   - 传递模型数据和操作类型

### 接收同步数据

1. 远程节点接收到同步请求后，`SyncController::receive()` 方法：
   - 验证 API Key
   - 验证请求数据
   - 调用 `SyncService::receiveSync()` 处理数据

2. `SyncService::receiveSync()` 方法：
   - 检查时间戳，确保以最新为准
   - 临时禁用同步监听（避免循环同步）
   - 执行创建/更新/删除操作
   - 重新启用同步监听

### 冲突解决

- **以时间最新为准**：如果本地数据更新时间晚于同步数据的时间戳，则忽略同步
- **哈希值检测**：通过数据哈希值判断数据是否真的变更，避免不必要的同步

## 使用方法

### 手动触发同步

```bash
# 同步所有待处理的数据（同步执行）
php artisan sync:pending

# 同步所有待处理的数据（使用队列）
php artisan sync:pending --queue

# 限制同步数量
php artisan sync:pending --limit=50

# 重试失败的同步任务
php artisan sync:retry-failed --queue
```

### 检查同步状态

```bash
# API 端点
curl https://your-domain.com/api/sync/status
```

### 查看同步日志

```php
use App\Models\SyncLog;

// 查看待同步的记录
$pending = SyncLog::where('status', 'pending')->get();

// 查看失败的记录
$failed = SyncLog::where('status', 'failed')->get();

// 查看同步状态
use App\Models\SyncStatus;
$status = SyncStatus::where('model_type', Product::class)
    ->where('model_id', 1)
    ->get();
```

## 注意事项

1. **API Key 安全**：确保 API Key 足够复杂且保密，不要提交到版本控制系统

2. **网络稳定性**：确保两个节点之间的网络连接稳定，建议使用 HTTPS

3. **数据一致性**：
   - 同步是异步的，可能存在短暂的数据不一致
   - 建议在业务逻辑中考虑这一点

4. **性能考虑**：
   - 大量数据变更时，建议调整 `SYNC_BATCH_SIZE` 和队列处理速度
   - 监控队列长度，避免积压

5. **错误处理**：
   - 失败的同步会自动重试（最多 3 次）
   - 可以通过 `sync:retry-failed` 命令手动重试

6. **循环同步防护**：
   - 系统会自动禁用同步监听，避免循环同步
   - 通过时间戳和哈希值双重检查避免重复同步

## 故障排查

### 同步不工作

1. 检查 `SYNC_ENABLED` 是否设置为 `true`
2. 检查模型是否添加了 `Syncable` trait
3. 检查模型是否在 `config/sync.php` 的 `sync_models` 列表中
4. 检查队列是否正在运行
5. 查看日志：`storage/logs/laravel.log`

### 同步失败

1. 检查网络连接
2. 检查 API Key 是否正确
3. 检查远程节点 URL 是否正确
4. 查看 `sync_logs` 表中的 `error_message` 字段
5. 使用 `sync:retry-failed` 命令重试

### 数据不一致

1. 检查时间戳是否正确
2. 检查是否有手动修改数据库的情况
3. 使用 `sync:pending` 命令手动触发同步

## 扩展和定制

### 添加新的同步模型

1. 在模型中添加 `Syncable` trait
2. 在 `config/sync.php` 的 `sync_models` 数组中添加模型类名
3. 确保模型的主键是 `id`，且有 `created_at` 和 `updated_at` 时间戳

**注意**：Media 模型已经自动包含在同步列表中，无需额外配置。所有通过 Media Library 上传的文件都会自动同步。

### 自定义同步逻辑

可以继承 `SyncService` 类并重写相关方法：

```php
class CustomSyncService extends SyncService
{
    protected function preparePayload(Model $model, string $action): array
    {
        // 自定义数据准备逻辑
    }
}
```

然后在 `AppServiceProvider` 中绑定：

```php
$this->app->singleton(SyncService::class, CustomSyncService::class);
```

## 安全建议

1. **使用 HTTPS**：确保同步 API 使用 HTTPS 加密传输
2. **API Key 轮换**：定期更换 API Key
3. **IP 白名单**：如果可能，在防火墙层面限制访问来源 IP
4. **速率限制**：考虑添加速率限制，防止恶意请求
5. **日志审计**：定期检查同步日志，发现异常情况

## 文件同步注意事项

1. **文件大小限制**：
   - 默认超时时间为 5 分钟（300秒）
   - 大文件可能需要更长时间，建议调整 `SYNC_TIMEOUT` 配置

2. **存储空间**：
   - 确保两个节点都有足够的存储空间
   - 文件会占用双倍存储空间（源节点 + 目标节点）

3. **网络带宽**：
   - 文件同步会消耗网络带宽
   - 建议在低峰期进行大量文件同步

4. **文件路径一致性**：
   - 系统会保持文件路径结构一致
   - 如果使用自定义 PathGenerator，确保两个节点配置相同

5. **转换文件**：
   - 转换文件（如缩略图）会自动同步
   - 如果转换失败，不影响主文件同步

## 监控建议

1. 监控 `sync_logs` 表中 `status='failed'` 的记录数量
2. 监控队列长度
3. 监控同步延迟时间
4. 监控文件下载失败率
5. 监控存储空间使用情况
6. 设置告警，当失败率超过阈值时通知
