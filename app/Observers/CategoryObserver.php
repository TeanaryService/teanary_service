<?php

namespace App\Observers;

use App\Models\Category;
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
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCategoryCache();
    }

    /**
     * 清除所有语言下的分类缓存
     */
    protected function clearCategoryCache(): void
    {
        $languages = app(\App\Services\LocaleCurrencyService::class)->getLanguages();
        foreach ($languages as $lang) {
            Cache::forget("categories.with.translations.{$lang->id}");
        }
    }
}
