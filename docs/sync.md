# 数据双向同步方案完整文档

## 目录

- [概述](#概述)
- [快速开始](#快速开始)
- [工作原理](#工作原理)
- [使用方法](#使用方法)
- [文件同步功能](#文件同步功能)
- [故障排查](#故障排查)
- [扩展和定制](#扩展和定制)
- [安全与监控](#安全与监控)

---

## 概述

这是一个与主代码解耦的双向数据同步方案，用于在多个节点之间同步数据变更（增删改），以时间最新为准。支持任意数量的节点，系统会自动同步到配置中的所有其他节点。

**支持同步的内容：**
- 数据库记录（模型数据）
- 媒体文件（图片、资源文件等）
- 文件转换（缩略图等）

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

5. **SyncBatchDataJob** (`app/Jobs/SyncBatchDataJob.php`)
   - 异步队列任务
   - 执行批量同步操作
   - 支持自动重试
   - 将多条记录打包成一个请求，大幅提升效率

6. **SyncController** (`app/Http/Controllers/Api/SyncController.php`)
   - 接收来自远程节点的批量同步请求
   - API Key 验证
   - 处理批量同步数据
   - 提供文件下载端点（用于同步媒体文件）

---

## 快速开始

### 1. 运行数据库迁移

```bash
php artisan migrate
```

这将创建以下表：
- `sync_logs` - 同步日志表
- `sync_status` - 同步状态表

### 2. 配置环境变量

在 `.env` 文件中添加以下配置：

**配置说明：**
- `SYNC_NODE`: 当前节点的唯一标识，可以是任意字符串（如 'node1', 'beijing', 'shanghai' 等）
- 每个远程节点需要配置三个环境变量：`SYNC_{节点名}_URL`、`SYNC_{节点名}_API_KEY`、`SYNC_{节点名}_TIMEOUT`
- 节点名称建议使用有意义的名称，如地理位置、服务器编号等
- 系统会自动同步到配置中的所有其他节点（排除当前节点）

**示例：节点1的配置**
```env
SYNC_ENABLED=true
SYNC_NODE=node1

# 节点1的配置（当前节点，通常不需要配置自己）
SYNC_NODE1_URL=https://node1.example.com
SYNC_NODE1_API_KEY=your-secret-api-key-for-node1
SYNC_NODE1_TIMEOUT=30

# 节点2的配置（远程节点）
SYNC_NODE2_URL=https://node2.example.com
SYNC_NODE2_API_KEY=your-secret-api-key-for-node2
SYNC_NODE2_TIMEOUT=30

# 节点3的配置（远程节点，可选）
SYNC_NODE3_URL=https://node3.example.com
SYNC_NODE3_API_KEY=your-secret-api-key-for-node3
SYNC_NODE3_TIMEOUT=30
```

**示例：节点2的配置**
```env
SYNC_ENABLED=true
SYNC_NODE=node2

# 节点1的配置（远程节点）
SYNC_NODE1_URL=https://node1.example.com
SYNC_NODE1_API_KEY=your-secret-api-key-for-node1
SYNC_NODE1_TIMEOUT=30

# 节点2的配置（当前节点）
SYNC_NODE2_URL=https://node2.example.com
SYNC_NODE2_API_KEY=your-secret-api-key-for-node2
SYNC_NODE2_TIMEOUT=30

# 节点3的配置（远程节点）
SYNC_NODE3_URL=https://node3.example.com
SYNC_NODE3_API_KEY=your-secret-api-key-for-node3
SYNC_NODE3_TIMEOUT=30
```

**重要提示：**
1. 所有节点的 `config/sync.php` 文件中必须包含所有节点的配置（包括自己）
2. 每个节点的 `SYNC_NODE` 环境变量必须唯一，且必须在 `remote_nodes` 配置中存在
3. 系统会自动同步到配置中的所有其他节点，无需手动指定
4. 可以添加任意数量的节点，只需在配置文件中添加相应的节点配置即可

**可选配置：**
```env
SYNC_QUEUE=sync
SYNC_RETRY_TIMES=3
SYNC_RETRY_DELAY=60
SYNC_BATCH_SIZE=100
SYNC_TIMEOUT=300  # 文件同步超时时间（秒）
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

**注意**：
- 模型类必须在 `config/sync.php` 的 `sync_models` 数组中
- 模型表必须有 `created_at` 和 `updated_at` 字段（Laravel 默认）
- 模型的主键字段名必须是 `id`

### 4. 配置队列

确保队列工作进程正在运行：

```bash
php artisan queue:work --queue=sync
```

或使用 Supervisor（推荐）：

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

或在 crontab 中添加：

```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. 测试同步

创建测试数据并检查同步日志：

```bash
php artisan tinker
```

```php
// 创建测试数据
$product = \App\Models\Product::create([
    'slug' => 'test-product-' . time(),
    'status' => 'active',
]);

// 检查同步日志
\App\Models\SyncLog::where('status', 'pending')->count();
\App\Models\SyncLog::latest()->first();

// 手动触发同步
// 退出 tinker 后执行：
// php artisan app:sync-pending --queue
```

---

## 工作原理

### 数据变更监听

1. 当模型发生变更（created、updated、deleted）时，`Syncable` trait 会自动触发
2. `SyncService::recordSync()` 方法会：
   - 检查模型是否在同步列表中
   - 检查数据是否真的变更（通过哈希值）
   - 为每个目标节点创建一条 `SyncLog` 记录

### 批量同步执行

1. 定时任务每分钟执行 `app:sync-pending` 命令
2. 命令获取所有待同步的记录，按目标节点分组
3. `SyncBatchDataJob` 执行批量同步操作：
   - 将多条记录打包成一个请求（默认每批50条）
   - 发送 HTTP POST 请求到远程节点的 `/api/sync/receive-batch` 端点
   - 包含 API Key 认证
   - 传递批量模型数据和操作类型
   - 大幅减少HTTP请求次数，提升同步效率

### 接收批量同步数据

1. 远程节点接收到批量同步请求后，`SyncController::receiveBatch()` 方法：
   - 验证 API Key
   - 验证批量请求数据（最多100条）
   - 调用 `SyncService::receiveBatchSync()` 处理批量数据

2. `SyncService::receiveBatchSync()` 方法：
   - 按模型类型分组处理，减少同步监听开关次数
   - 对每条记录检查时间戳，确保以最新为准
   - 临时禁用同步监听（避免循环同步）
   - 批量执行创建/更新/删除操作
   - 重新启用同步监听
   - 返回每条记录的处理结果

### 冲突解决

- **以时间最新为准**：如果本地数据更新时间晚于同步数据的时间戳，则忽略同步
- **哈希值检测**：通过数据哈希值判断数据是否真的变更，避免不必要的同步

---

## 使用方法

### 手动触发同步

```bash
# 批量同步所有待处理的数据（同步执行）
php artisan app:sync-pending

# 批量同步所有待处理的数据（使用队列，推荐）
php artisan app:sync-pending --queue

# 限制同步数量，配置每批大小
php artisan app:sync-pending --limit=100 --queue --batch-size=50

# 重试失败的同步任务（批量）
php artisan app:sync-retry-failed --queue --batch-size=50
```

### 检查同步状态

```bash
# API 端点
curl https://your-domain.com/api/sync/status
```

### 查看同步日志

```php
use App\Models\SyncLog;
use App\Models\SyncStatus;

// 查看待同步的记录
$pending = SyncLog::where('status', 'pending')->get();

// 查看失败的记录
$failed = SyncLog::where('status', 'failed')->get();

// 查看特定模型的同步记录
$productSyncs = SyncLog::where('model_type', Product::class)
    ->where('model_id', 1)
    ->get();

// 查看同步状态
$status = SyncStatus::where('model_type', Product::class)
    ->where('model_id', 1)
    ->get();
```

### 临时禁用同步

在某些情况下，你可能需要临时禁用同步（例如批量导入数据）：

```php
use App\Models\Product;

// 禁用同步
Product::$syncDisabled = true;

// 执行批量操作
Product::create([...]);
Product::create([...]);

// 重新启用同步
Product::$syncDisabled = false;
```

---

## 文件同步功能

### 概述

系统自动同步所有通过 Spatie Media Library 管理的媒体文件，包括：
- 产品图片、分类图片、文章图片
- 用户头像、产品变体图片
- 其他所有媒体资源

### 工作原理

1. **文件上传时**：
   - Media 模型的变更会被自动记录
   - 同步数据包含文件的下载 URL（带安全令牌，10分钟有效期）

2. **同步到远程节点**：
   - 远程节点接收到 Media 同步数据后，自动从源节点下载实际文件内容
   - 保存到本地存储，保持相同的路径结构
   - 如果存在转换文件（如缩略图），也会自动同步

3. **文件下载安全**：
   - 文件下载 URL 包含临时令牌
   - 令牌验证确保只有授权的同步请求可以下载文件
   - 令牌有效期：10分钟

### API 端点

```
# 下载文件
GET /api/sync/download-file/{mediaId}?token={token}

# 下载转换文件
GET /api/sync/download-file/{mediaId}/conversion/{conversionName}?token={token}
```

### 配置

文件同步功能无需额外配置，Media 模型已自动包含在同步列表中。

如需调整文件下载超时时间，设置环境变量：

```env
SYNC_TIMEOUT=300  # 默认 300 秒（5分钟），用于大文件
```

### 注意事项

1. **文件大小**：默认超时时间为 5 分钟，大文件可能需要更长时间
2. **存储空间**：文件会占用双倍存储空间（源节点 + 目标节点）
3. **网络带宽**：文件同步会消耗网络带宽，建议在低峰期进行大量文件同步
4. **文件路径**：系统会保持文件路径结构一致，如果使用自定义 PathGenerator，确保两个节点配置相同
5. **转换文件**：转换文件（如缩略图）会自动同步，如果转换失败，不影响主文件同步

### 性能优化

- **批量同步**：文件同步通过队列异步执行，不会阻塞主流程
- **重试机制**：失败的文件会自动重试（最多 3 次）
- **并发控制**：通过队列系统控制并发数量

### 测试文件同步

```php
// 在一个节点上传文件
$product = Product::find(1);
$product->addMediaFromUrl('https://example.com/image.jpg')
    ->toMediaCollection('images');

// 检查同步日志
\App\Models\SyncLog::where('model_type', \Spatie\MediaLibrary\MediaCollections\Models\Media::class)
    ->latest()
    ->first();

// 在另一个节点检查文件是否存在
$media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find(1);
$media->getUrl(); // 应该返回可访问的 URL
```

---

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
5. 使用 `php artisan app:sync-retry-failed --queue` 命令重试

### 数据不一致

1. 检查时间戳是否正确
2. 检查是否有手动修改数据库的情况
3. 使用 `php artisan app:sync-pending --queue` 命令手动触发同步

### 文件同步失败

1. 检查网络连接
2. 检查文件下载 URL 是否可访问
3. 检查存储空间是否充足
4. 检查下载令牌是否过期（10分钟有效期）
5. 查看日志：`storage/logs/laravel.log`

### 常见问题

**Q: 同步是实时的吗？**  
A: 不是，同步是异步的。数据变更会立即记录到 `sync_logs` 表，但实际同步通过队列异步执行，通常延迟几秒到几分钟。

**Q: 如何查看同步状态？**  
A: 
```bash
php artisan tinker
\App\Models\SyncLog::where('status', 'pending')->count();
\App\Models\SyncLog::where('status', 'failed')->get();
```

**Q: 如何重试失败的同步？**  
A: `php artisan app:sync-retry-failed --queue`

**Q: 不需要同步的模型怎么办？**  
A: 不添加 `Syncable` trait，或从 `config/sync.php` 的 `sync_models` 数组中移除。

---

## 测试

同步功能包含完整的单元测试，测试文件位于 `tests/Unit/NodeSyncTest.php`。

### 运行测试

```bash
# 运行所有同步相关测试
php artisan test --filter NodeSyncTest

# 运行所有单元测试
composer test:unit
```

### 测试覆盖

测试覆盖以下场景：

- ✅ 产品创建/更新/删除同步到所有目标节点
- ✅ Pivot 表创建/删除同步
- ✅ 批量同步到多个节点
- ✅ 接收来自不同节点的批量同步
- ✅ 同步状态防止重复同步
- ✅ 数据变更后创建新的同步日志
- ✅ 删除操作总是创建日志
- ✅ Pivot 表使用哈希值作为 model_id
- ✅ 节点配置变更时的同步行为
- ✅ 当前节点排除逻辑
- ✅ 同步失败错误记录
- ✅ 同步成功状态更新

### 测试配置

测试使用内存数据库（SQLite），确保测试环境独立：

```php
// 在测试中配置同步服务
Config::set('sync.enabled', true);
Config::set('sync.node', 'node1');
Config::set('sync.remote_nodes', [
    'node2' => [
        'url' => 'https://node2.example.com',
        'api_key' => 'test-api-key',
        'timeout' => 600,
    ],
]);
```

## 扩展和定制

### 添加新的同步模型

1. 在模型中添加 `Syncable` trait
2. 在 `config/sync.php` 的 `sync_models` 数组中添加模型类名
3. 确保模型的主键是 `id`，且有 `created_at` 和 `updated_at` 时间戳

**注意**：Media 模型已经自动包含在同步列表中，无需额外配置。

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

### 存储同步脚本

如果需要手动同步存储文件，可以使用以下脚本（位于 `sync_storage.sh`）：

```bash
#!/bin/bash

LOCAL_DIR="/home/wwwroot/teanary.test/shared/storage/app/"
REMOTE_DIR="/home/wwwroot/teanary/shared/storage/app"
REMOTE_HOST="your.remote.ip.or.host"
REMOTE_USER="root"
SSH_KEY="/home/youruser/.ssh/id_rsa"  # 替换为你的私钥路径

# Rsync local to remote with SSH key
rsync -az -e "ssh -i ${SSH_KEY}" --delete "${LOCAL_DIR}" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/"

# Change owner and permission remotely
ssh -i ${SSH_KEY} ${REMOTE_USER}@${REMOTE_HOST} <<EOF
chown -R www:www ${REMOTE_DIR}
chmod -R 777 ${REMOTE_DIR}
EOF
```

---

## 安全与监控

### 安全建议

1. **使用 HTTPS**：确保同步 API 使用 HTTPS 加密传输
2. **API Key 安全**：确保 API Key 足够复杂且保密，不要提交到版本控制系统
3. **API Key 轮换**：定期更换 API Key
4. **IP 白名单**：如果可能，在防火墙层面限制访问来源 IP
5. **速率限制**：考虑添加速率限制，防止恶意请求
6. **日志审计**：定期检查同步日志，发现异常情况

### 监控建议

1. 监控 `sync_logs` 表中 `status='failed'` 的记录数量
2. 监控队列长度
3. 监控同步延迟时间
4. 监控文件下载失败率
5. 监控存储空间使用情况
6. 设置告警，当失败率超过阈值时通知

### 注意事项总结

1. **数据一致性**：
   - 同步是异步的，可能存在短暂的数据不一致
   - 建议在业务逻辑中考虑这一点

2. **性能考虑**：
   - 大量数据变更时，建议调整 `SYNC_BATCH_SIZE` 和队列处理速度
   - 监控队列长度，避免积压

3. **错误处理**：
   - 失败的同步会自动重试（最多 3 次）
   - 可以通过 `sync:retry-failed` 命令手动重试

4. **循环同步防护**：
   - 系统会自动禁用同步监听，避免循环同步
   - 通过时间戳和哈希值双重检查避免重复同步

5. **文件同步注意事项**：
   - 文件大小限制：默认超时时间为 5 分钟（300秒）
   - 存储空间：确保两个节点都有足够的存储空间
   - 网络带宽：文件同步会消耗网络带宽
   - 文件路径一致性：系统会保持文件路径结构一致
   - 转换文件：转换文件（如缩略图）会自动同步
