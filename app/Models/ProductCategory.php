<?php

namespace App\Models;

use App\Traits\Syncable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Product Category 中间表模型.
 */
class ProductCategory extends Pivot
{
    use Syncable;

    protected $table = 'product_category';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'category_id',
    ];

    protected $casts = [
        'product_id' => 'int',
        'category_id' => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
