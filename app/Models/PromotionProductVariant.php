<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Promotion Product Variant 中间表模型.
 */
class PromotionProductVariant extends Pivot
{
    use Syncable;

    protected $table = 'promotion_product_variant';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'promotion_id',
        'product_id',
        'product_variant_id',
    ];

    protected $casts = [
        'promotion_id' => 'int',
        'product_id' => 'int',
        'product_variant_id' => 'int',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
