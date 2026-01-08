<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        $this->clearCategoryCache();
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        $this->clearCategoryCache();
    }

    /**
     * Handle the Category "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Category $category): void
    {
        // 递归删除子分类
        $category->categories()->each(function ($child) {
            $child->delete();
        });

        // 删除分类翻译
        $category->categoryTranslations()->each(function ($translation) {
            $translation->delete();
        });

        // 删除中间表关联（产品-分类）
        $category->productCategories()->detach();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCategoryCache();
    }

    /**
     * 清除所有语言下的分类缓存.
     */
    protected function clearCategoryCache(): void
    {
        Cache::forget(CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
    }
}
