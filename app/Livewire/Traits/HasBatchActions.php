<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Cache;

trait HasBatchActions
{
    public array $selectedItems = [];
    public bool $selectAll = false;

    /**
     * 切换单个项目的选择状态
     */
    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selectedItems)) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, [$id]));
        } else {
            $this->selectedItems[] = $id;
        }
        $this->selectAll = false;
    }

    /**
     * 切换全选状态
     */
    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedItems = [];
            $this->selectAll = false;
        } else {
            // 获取当前页的所有 ID
            $items = $this->getCurrentPageItems();
            $this->selectedItems = $items->pluck('id')->toArray();
            $this->selectAll = true;
        }
    }

    /**
     * 获取当前页的项目（需要在组件中实现）
     */
    abstract protected function getCurrentPageItems();

    /**
     * 批量删除
     */
    protected function batchDelete(string $modelClass, ?string $cacheKey = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
            return;
        }

        $count = 0;
        $skipped = 0;
        
        foreach ($this->selectedItems as $id) {
            try {
                $model = $modelClass::find($id);
                if ($model) {
                    // 检查是否有关联订单（如果模型有 orders 关系）
                    if (method_exists($model, 'orders') && $model->orders()->exists()) {
                        $skipped++;
                        continue; // 跳过有订单关联的
                    }
                    $model->delete();
                    $count++;
                }
            } catch (\Exception $e) {
                $skipped++;
                // 记录错误但不中断流程
                \Log::warning("批量删除失败: {$modelClass} ID {$id}", ['error' => $e->getMessage()]);
            }
        }

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->clearSelection();

        if ($skipped > 0) {
            $this->dispatch('flash-message', type: 'info', message: __('manager.batch.deleted_with_skipped', ['count' => $count, 'skipped' => $skipped]));
        } else {
            $this->dispatch('flash-message', type: 'success', message: __('manager.batch.deleted_successfully', ['count' => $count]));
        }
        
        $this->resetPage();
    }

    /**
     * 批量设置翻译状态
     */
    protected function batchUpdateTranslationStatus(string $modelClass, string $status, ?string $cacheKey = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
            return;
        }

        $count = $modelClass::whereIn('id', $this->selectedItems)
            ->update(['translation_status' => $status]);

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->clearSelection();

        $this->dispatch('flash-message', type: 'success', message: __('manager.batch.translation_status_updated', ['count' => $count]));
    }

    /**
     * 批量设置内容状态（发布状态）
     */
    protected function batchUpdatePublishedStatus(string $modelClass, bool $isPublished, ?string $cacheKey = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
            return;
        }

        $count = $modelClass::whereIn('id', $this->selectedItems)
            ->update(['is_published' => $isPublished]);

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->clearSelection();

        $statusText = $isPublished ? __('manager.batch.published') : __('manager.batch.unpublished');
        $this->dispatch('flash-message', type: 'success', message: __('manager.batch.published_status_updated', ['count' => $count, 'status' => $statusText]));
    }

    /**
     * 批量设置激活状态
     */
    protected function batchUpdateActiveStatus(string $modelClass, bool $active, ?string $cacheKey = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
            return;
        }

        $count = $modelClass::whereIn('id', $this->selectedItems)
            ->update(['active' => $active]);

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->clearSelection();

        $statusText = $active ? __('manager.batch.activated') : __('manager.batch.deactivated');
        $this->dispatch('flash-message', type: 'success', message: __('manager.batch.active_status_updated', ['count' => $count, 'status' => $statusText]));
    }

    /**
     * 批量设置商品状态
     */
    protected function batchUpdateProductStatus(string $modelClass, string $status, ?string $cacheKey = null): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('flash-message', type: 'error', message: __('manager.batch.no_items_selected'));
            return;
        }

        $count = $modelClass::whereIn('id', $this->selectedItems)
            ->update(['status' => $status]);

        if ($cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->clearSelection();

        $this->dispatch('flash-message', type: 'success', message: __('manager.batch.status_updated', ['count' => $count]));
    }

    /**
     * 检查是否有选中的项目
     */
    public function hasSelectedItems(): bool
    {
        return !empty($this->selectedItems);
    }

    /**
     * 获取选中项目的数量
     */
    public function getSelectedCount(): int
    {
        return count($this->selectedItems);
    }

    /**
     * 清除所有选择
     */
    public function clearSelection(): void
    {
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    /**
     * 重置选择状态（在分页或筛选后调用）
     */
    public function resetSelection(): void
    {
        $this->clearSelection();
    }
}
