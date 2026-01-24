<?php

namespace App\Livewire\Traits;

use Livewire\WithPagination;

/**
 * 提供搜索和筛选功能的 Trait.
 *
 * 用于列表组件，提供：
 * - 搜索功能
 * - 筛选功能
 * - 分页重置
 * - 筛选重置
 */
trait HasSearchAndFilters
{
    use WithPagination;

    /**
     * 搜索关键词.
     */
    public string $search = '';

    /**
     * 当搜索关键词更新时，重置分页.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * 重置所有筛选条件.
     *
     * 子类可以重写此方法来重置特定的筛选条件
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * 重置分页到第一页.
     */
    protected function resetPagination(): void
    {
        $this->resetPage();
    }
}
