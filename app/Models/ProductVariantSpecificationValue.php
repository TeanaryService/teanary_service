<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Product Variant Specification Value 中间表模型.
 */
class ProductVariantSpecificationValue extends Pivot
{
    use Syncable;

    protected $table = 'product_variant_specification_value';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'specification_id',
        'specification_value_id',
    ];

    protected $casts = [
        'product_variant_id' => 'int',
        'specification_id' => 'int',
        'specification_value_id' => 'int',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    public function specificationValue(): BelongsTo
    {
        return $this->belongsTo(SpecificationValue::class);
    }
}
