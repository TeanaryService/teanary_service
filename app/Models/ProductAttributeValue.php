<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Product Attribute Value 中间表模型.
 */
class ProductAttributeValue extends Pivot
{
    use Syncable;

    protected $table = 'product_attribute_value';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'product_id',
        'attribute_value_id',
    ];

    protected $casts = [
        'attribute_id' => 'int',
        'product_id' => 'int',
        'attribute_value_id' => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
