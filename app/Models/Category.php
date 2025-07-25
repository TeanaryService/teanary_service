<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\CategoryObserver;
use App\Traits\CascadesMediaDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Category
 * 
 * @property int $id
 * @property int|null $parent_id
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Category|null $category
 * @property Collection|Category[] $categories
 * @property Collection|CategoryTranslation[] $categoryTranslations
 * @property Collection|ProductCategory[] $productCategories
 *
 * @package App\Models
 */

#[ObservedBy([CategoryObserver::class])]

class Category extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use CascadesMediaDeletes;

    public static $snakeAttributes = false;

    protected $casts = [
        'parent_id' => 'int'
    ];

    protected $fillable = [
        'parent_id',
        'slug'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function categoryTranslations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_category');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(350)
            ->height(350)
            ->sharpen(10)
            ->nonOptimized();
    }

    /**
     * 获取所有顶级分类（含多语言翻译和图片），永久缓存（含子分类）
     *
     * @param int|null $langId
     * @return \Illuminate\Support\Collection
     */
    public static function getCachedCategories()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("categories.with.translations", function () {
            return static::with([
                'categories.categories',
                'media',
                'categoryTranslations',
                'categories.media',
                'categories.categoryTranslations',
            ])
                ->whereNull('parent_id')
                ->get();
        });
    }

    /**
     * 获取当前语言下的分类（含递归子分类）
     */
    public static function getCategoriesForLanguage($langId)
    {
        $categories = static::getCachedCategories();
        return $categories->map(function ($cat) use ($langId) {
            return static::formatCategory($cat, $langId);
        });
    }

    /**
     * 格式化分类（含递归子分类）
     *
     * @param \App\Models\Category $category
     * @param int $langId
     * @return array
     */
    protected static function formatCategory($category, int $langId): array
    {
        $translation = $category->categoryTranslations->where('language_id', $langId)->first();

        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $translation ? $translation->name : $category->slug,
            'image_url' => $category->getFirstMediaUrl('image', 'thumb') ?: asset('logo.png'),
            'children' => $category->categories->map(function ($child) use ($langId) {
                return static::formatCategory($child, $langId);
            })->values(),
        ];
    }
}
