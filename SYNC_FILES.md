# 文件同步功能说明

## 概述

系统现在支持自动同步媒体文件（图片、资源等），与数据同步功能完全集成。

## 工作原理

### 1. 文件上传时

当通过 Spatie Media Library 上传文件时：
- Media 模型的变更会被自动记录
- 同步数据包含文件的下载 URL（带安全令牌，10分钟有效期）

### 2. 同步到远程节点

远程节点接收到 Media 同步数据后：
- 自动从源节点下载实际文件内容
- 保存到本地存储，保持相同的路径结构
- 如果存在转换文件（如缩略图），也会自动同步

### 3. 文件下载安全

- 文件下载 URL 包含临时令牌
- 令牌验证确保只有授权的同步请求可以下载文件
- 令牌有效期：10分钟

## API 端点

### 下载文件

```
GET /api/sync/download-file/{mediaId}?token={token}
```

### 下载转换文件

```
GET /api/sync/download-file/{mediaId}/conversion/{conversionName}?token={token}
```

## 配置

文件同步功能无需额外配置，Media 模型已自动包含在同步列表中。

### 环境变量

如果需要调整文件下载超时时间：

```env
SYNC_TIMEOUT=300  # 默认 300 秒（5分钟），用于大文件
```

## 注意事项

1. **文件大小**：
   - 默认超时时间为 5 分钟
   - 大文件可能需要更长时间，建议调整 `SYNC_TIMEOUT`

2. **存储空间**：
   - 文件会占用双倍存储空间（源节点 + 目标节点）
   - 确保两个节点都有足够的存储空间

3. **网络带宽**：
   - 文件同步会消耗网络带宽
   - 建议在低峰期进行大量文件同步

4. **文件路径**：
   - 系统会保持文件路径结构一致
   - 如果使用自定义 PathGenerator，确保两个节点配置相同

5. **转换文件**：
   - 转换文件（如缩略图）会自动同步
   - 如果转换失败，不影响主文件同步

## 故障排查

### 文件同步失败

1. 检查网络连接
2. 检查文件下载 URL 是否可访问
3. 检查存储空间是否充足
4. 查看日志：`storage/logs/laravel.log`

### 文件下载失败

1. 检查下载令牌是否过期（10分钟有效期）
2. 检查文件是否存在
3. 检查存储磁盘配置是否正确

## 测试

### 测试文件同步

1. 在一个节点上传文件：
```php
$product = Product::find(1);
$product->addMediaFromUrl('https://example.com/image.jpg')
    ->toMediaCollection('images');
```

2. 检查同步日志：
```bash
php artisan tinker
\App\Models\SyncLog::where('model_type', \Spatie\MediaLibrary\MediaCollections\Models\Media::class)->latest()->first();
```

3. 在另一个节点检查文件是否存在：
```php
$media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find(1);
$media->getUrl(); // 应该返回可访问的 URL
```

## 支持的媒体类型

所有通过 Spatie Media Library 管理的媒体文件都会自动同步：
- 产品图片
- 分类图片
- 文章图片
- 用户头像
- 产品变体图片
- 其他所有媒体资源

## 性能优化

1. **批量同步**：文件同步通过队列异步执行，不会阻塞主流程
2. **重试机制**：失败的文件会自动重试（最多 3 次）
3. **并发控制**：通过队列系统控制并发数量
