# 同步功能使用示例

## 在模型中启用同步

### 示例 1: Product 模型

```php
<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use Syncable; // 添加这一行即可启用同步
    
    // ... 其他代码
}
```

### 示例 2: Category 模型

```php
<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Syncable; // 添加这一行即可启用同步
    
    // ... 其他代码
}
```

## 注意事项

1. **确保模型在配置列表中**：模型类必须在 `config/sync.php` 的 `sync_models` 数组中

2. **模型必须有时间戳**：模型表必须有 `created_at` 和 `updated_at` 字段（Laravel 默认）

3. **主键必须是 id**：模型的主键字段名必须是 `id`

## 不需要同步的模型

如果某个模型不需要同步，只需：
- 不添加 `Syncable` trait
- 或从 `config/sync.php` 的 `sync_models` 数组中移除

## 临时禁用同步

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

## 查看同步日志

```php
use App\Models\SyncLog;

// 查看所有待同步的记录
$pending = SyncLog::where('status', 'pending')->get();

// 查看特定模型的同步记录
$productSyncs = SyncLog::where('model_type', Product::class)
    ->where('model_id', 1)
    ->get();

// 查看失败的同步
$failed = SyncLog::where('status', 'failed')
    ->where('retry_count', '<', 3)
    ->get();
```
