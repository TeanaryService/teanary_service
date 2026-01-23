# 批量操作功能实现文档

## 概述

批量操作功能允许管理员在管理后台对多个项目进行批量操作，如批量删除、批量更新状态等。该功能基于 Livewire 4.x 实现，提供了统一的接口和用户体验。

## 功能特性

- ✅ 批量选择（单选、全选）
- ✅ 批量删除
- ✅ 批量更新状态（翻译状态、发布状态、激活状态等）
- ✅ 实时消息提示
- ✅ 与同步系统深度集成

## 技术实现

### 核心 Trait

批量操作功能通过 `HasBatchActions` Trait 实现，位于 `app/Livewire/Traits/HasBatchActions.php`。

#### 主要方法

```php
// 切换单个项目的选择状态
public function toggleSelect(int $id): void

// 切换全选状态
public function toggleSelectAll(): void

// 批量删除
protected function batchDelete(string $modelClass, ?string $cacheKey = null): void

// 批量更新翻译状态
public function batchUpdateTranslationStatus(bool $isTranslated): void

// 批量更新发布状态
public function batchUpdatePublishedStatus(bool $isPublished): void

// 批量更新激活状态
public function batchUpdateActiveStatus(bool $active): void

// 批量更新状态（通用方法）
public function batchUpdateStatus(string $field, $value): void

// 清除选择
protected function clearSelection(): void
```

### 使用示例

在 Livewire 组件中使用批量操作：

```php
<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use HasBatchActions, WithPagination;

    protected function getCurrentPageItems()
    {
        return Product::query()
            ->with(['translations', 'variants'])
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.manager.products', [
            'products' => $this->getCurrentPageItems(),
        ]);
    }
}
```

### 视图实现

在 Blade 模板中使用批量操作：

```blade
<div>
    <!-- 批量操作工具栏 -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <x-widgets.checkbox
                wire:model="selectAll"
                wire:click="toggleSelectAll"
                label="全选"
            />
            
            @if(count($selectedItems) > 0)
                <span class="text-sm text-gray-600">
                    已选择 {{ count($selectedItems) }} 项
                </span>
                
                <x-widgets.button
                    wire:click="batchDelete"
                    variant="danger"
                    size="sm"
                >
                    批量删除
                </x-widgets.button>
            @endif
        </div>
    </div>

    <!-- 数据列表 -->
    <div class="space-y-4">
        @foreach($products as $product)
            <div class="flex items-center gap-4">
                <x-widgets.checkbox
                    wire:click="toggleSelect({{ $product->id }})"
                    :checked="in_array($product->id, $selectedItems)"
                />
                
                <!-- 产品信息 -->
                <div>{{ $product->name }}</div>
            </div>
        @endforeach
    </div>
</div>
```

## 批量操作类型

### 1. 批量删除

批量删除支持级联删除，会自动删除关联数据：

```php
protected function batchDelete(string $modelClass, ?string $cacheKey = null): void
{
    if (empty($this->selectedItems)) {
        $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
        return;
    }

    // 执行批量删除
    // 会自动触发 Observer 的级联删除逻辑
    $modelClass::whereIn('id', $this->selectedItems)->delete();

    // 清除缓存
    if ($cacheKey) {
        Cache::forget($cacheKey);
    }

    // 清除选择
    $this->clearSelection();

    // 显示成功消息
    $this->dispatch('flash-message', type: 'success', message: __('manager.batch.deleted_successfully', ['count' => count($this->selectedItems)]));
}
```

### 2. 批量更新状态

支持多种状态字段的批量更新：

- **翻译状态** (`is_translated`)
- **发布状态** (`is_published`)
- **激活状态** (`is_active`)
- **通用状态字段**

```php
// 批量更新翻译状态
public function batchUpdateTranslationStatus(bool $isTranslated): void
{
    if (empty($this->selectedItems)) {
        $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
        return;
    }

    $this->getModelClass()::whereIn('id', $this->selectedItems)
        ->update(['is_translated' => $isTranslated]);

    $this->clearSelection();
    $this->dispatch('flash-message', type: 'success', message: __('manager.batch.translation_status_updated', ['count' => count($this->selectedItems)]));
}
```

## 与同步系统集成

批量操作会自动触发数据同步：

1. **删除操作**：通过 Observer 自动触发同步
2. **更新操作**：通过 Syncable Trait 自动触发同步
3. **级联删除**：关联数据的删除也会自动同步

## 消息提示系统

批量操作使用统一的消息提示系统，消息会在页面右上角显示：

```php
// 成功消息
$this->dispatch('flash-message', type: 'success', message: '操作成功');

// 错误消息
$this->dispatch('flash-message', type: 'error', message: '操作失败');

// 信息消息
$this->dispatch('flash-message', type: 'info', message: '提示信息');
```

消息组件会自动处理显示和自动隐藏。

## 扩展指南

### 添加新的批量操作

1. 在组件中添加新方法：

```php
public function batchCustomAction(): void
{
    if (empty($this->selectedItems)) {
        $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
        return;
    }

    // 执行自定义操作
    $this->getModelClass()::whereIn('id', $this->selectedItems)
        ->update(['custom_field' => 'value']);

    $this->clearSelection();
    $this->dispatch('flash-message', type: 'success', message: '操作成功');
}
```

2. 在视图中添加按钮：

```blade
<x-widgets.button
    wire:click="batchCustomAction"
    variant="primary"
    size="sm"
>
    自定义操作
</x-widgets.button>
```

### 自定义选择逻辑

如果需要自定义选择逻辑，可以重写 `getCurrentPageItems()` 方法：

```php
protected function getCurrentPageItems()
{
    return $this->getModelClass()::query()
        ->where('status', 'active')
        ->paginate(20);
}
```

## 注意事项

1. **性能考虑**：批量操作可能涉及大量数据，建议添加适当的限制
2. **事务处理**：批量操作应在数据库事务中执行，确保数据一致性
3. **权限检查**：确保用户有执行批量操作的权限
4. **级联删除**：删除操作会自动触发级联删除，请确保 Observer 配置正确

## 测试

批量操作功能已包含在单元测试中，测试文件位于 `tests/Unit/` 目录。

运行测试：

```bash
php artisan test --filter BatchActions
```

## 相关文档

- [多节点数据同步](SYNC.md) - 了解同步机制
- [系统架构](ARCHITECTURE.md) - 了解整体架构
- [部署指南](DEPLOYMENT.md) - 了解部署流程

---

**最后更新**: 2026-01-24
