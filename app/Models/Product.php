<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\ProductStatusEnum;
use App\Observers\ProductObserver;
use App\Traits\CascadesMediaDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product.
 *
 * @property int $id
 * @property string $slug
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|AttributeValue[] $attributeValues
 * @property Collection|ProductCategory[] $productCategories
 * @property Collection|ProductTranslation[] $productTranslations
 * @property Collection|ProductVariant[] $productVariants
 */
#[ObservedBy([ProductObserver::class])]

class Product extends Model implements HasMedia
{
    use CascadesMediaDeletes;
    use HasFactory;
    use InteractsWithMedia;
    use Searchable;

    public static $snakeAttributes = false;

    protected $casts = [
        'status' => ProductStatusEnum::class,
    ];

    protected $fillable = [
        'slug',
        'status',
        'source_url',
    ];

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_value')
            ->withPivot('attribute_id');
    }

    public function productCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
    }

    public function productTranslations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
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
     * 获取模型的索引化数据数组。
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        // 合并所有翻译的 title 和 content
        $translations = $this->productTranslations;
        $mergedText = '';
        foreach ($translations as $translation) {
            $mergedText .= strip_tags($translation->name ?? '').' ';
            $mergedText .= strip_tags($translation->description ?? '').' ';
        }
        $array['content'] = trim($mergedText);

        return $array;
    }
}
