<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    /**
     * 查找或创建分类.
     *
     * @param  array  $categoryData  分类数据
     */
    public function findOrCreateCategory(array $categoryData): ?Category
    {
        // 根据slug查找分类
        $category = Category::where('slug', $categoryData['slug'])->first();

        // 如果分类不存在，创建分类
        if (! $category) {
            $category = Category::create([
                'slug' => $categoryData['slug'],
                'parent_id' => $categoryData['parent_id'] ?? null,
            ]);
        }

        // 处理分类的多语言翻译
        $this->syncCategoryTranslations($category, $categoryData['translations'] ?? []);

        return $category;
    }

    /**
     * 同步分类的多语言翻译.
     */
    public function syncCategoryTranslations(Category $category, array $translations): void
    {
        if (empty($translations) || ! is_array($translations)) {
            return;
        }

        $cacheCleared = false;

        foreach ($translations as $translation) {
            // 检查该语言的翻译是否已存在
            $existingTranslation = CategoryTranslation::where('category_id', $category->id)
                ->where('language_id', $translation['language_id'])
                ->first();

            // 如果翻译不存在，创建翻译
            if (! $existingTranslation) {
                CategoryTranslation::create([
                    'category_id' => $category->id,
                    'language_id' => $translation['language_id'],
                    'name' => $translation['name'],
                    'description' => $translation['description'] ?? null,
                ]);

                // 清除分类缓存（只需清除一次）
                if (! $cacheCleared) {
                    $this->clearCategoryCache();
                    $cacheCleared = true;
                }
            }
        }
    }

    /**
     * 批量查找或创建分类.
     *
     * @param  array  $categoriesData  分类数据数组
     * @return array 分类ID数组
     */
    public function findOrCreateCategories(array $categoriesData): array
    {
        $categoryIds = [];

        foreach ($categoriesData as $categoryData) {
            $category = $this->findOrCreateCategory($categoryData);
            if ($category) {
                $categoryIds[] = $category->id;
            }
        }

        return $categoryIds;
    }

    /**
     * 清除分类缓存.
     */
    protected function clearCategoryCache(): void
    {
        Cache::forget(CacheKeys::CATEGORIES_WITH_TRANSLATIONS);
    }
}
