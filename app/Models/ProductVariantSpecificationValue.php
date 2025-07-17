<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ProductVariantSpecificationValue
 * 
 * @property int $product_variant_id
 * @property int $specification_id
 * @property int $specification_value_id
 * 
 * @property ProductVariant $productVariant
 * @property Specification $specification
 * @property SpecificationValue $specificationValue
 *
 * @package App\Models
 */
class ProductVariantSpecificationValue extends Pivot
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    public static $snakeAttributes = false;
    protected $table  = 'product_variant_specification_values';

    protected $casts = [
        'product_variant_id' => 'int',
        'specification_id' => 'int',
        'specification_value_id' => 'int'
    ];

    protected $fillable = [
        'product_variant_id',
        'specification_id',
        'specification_value_id'
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
