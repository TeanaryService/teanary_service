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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $slug
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|AttributeValue[] $attributeValues
 * @property Collection|ProductCategory[] $productCategories
 * @property Collection|ProductTranslation[] $productTranslations
 * @property Collection|ProductVariant[] $productVariants
 *
 * @package App\Models
 */

#[ObservedBy([ProductObserver::class])]

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use CascadesMediaDeletes;

    public static $snakeAttributes = false;

    protected $casts = [
        'status' => ProductStatusEnum::class
    ];
    protected $fillable = [
        'slug',
        'status'
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
            ->sharpen(10);
    }
}
