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
            // 使用 withoutEvents 避免触发观察者事件，防止ID冲突
            // 但需要手动生成 ID，因为 withoutEvents 会禁用 HasSnowflakeId 的 creating 事件
            $categoryId = app(SnowflakeService::class)->nextId();
            $category = Category::withoutEvents(function () use ($categoryData, $categoryId) {
                $category = new Category([
                    'slug' => $categoryData['slug'],
                    'parent_id' => $categoryData['parent_id'] ?? null,
                ]);
                $category->id = $categoryId;
                $category->save();

                return $category;
            });
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

            // 如果翻译不存在，创建翻译（使用 withoutEvents 避免 ID 冲突）
            if (! $existingTranslation) {
                $categoryId = $category->id; // 在闭包外获取 category_id
                CategoryTranslation::withoutEvents(function () use ($categoryId, $translation) {
                    CategoryTranslation::create([
                        'category_id' => $categoryId,
                        'language_id' => $translation['language_id'],
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                    ]);
                });

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
