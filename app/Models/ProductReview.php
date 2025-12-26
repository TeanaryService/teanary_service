<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\CascadesMediaDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class ProductReview
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $product_variants
 * @property int|null $user_id
 * @property int $rating
 * @property string|null $content
 * @property bool $is_approved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Product $product
 * @property ProductVariant|null $productVariant
 * @property User|null $user
 */
class ProductReview extends Model implements HasMedia
{
    use CascadesMediaDeletes;
    use HasFactory;
    use InteractsWithMedia;

    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'product_variants' => 'int',
        'user_id' => 'int',
        'rating' => 'int',
        'is_approved' => 'bool',
    ];

    protected $fillable = [
        'product_id',
        'product_variants',
        'user_id',
        'rating',
        'content',
        'is_approved',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variants');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200)
            ->sharpen(10)
            ->nonOptimized();
    }
}
