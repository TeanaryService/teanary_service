<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\CascadesMediaDeletes;
use Carbon\Carbon;
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
            ->width(200)
            ->height(200)
            ->sharpen(10);
    }
}
