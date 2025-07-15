<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductVariantSpecificationValue
 * 
 * @property int $id
 * @property int $product_variant_id
 * @property int $specification_value_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ProductVariant $productVariant
 * @property SpecificationValue $specificationValue
 *
 * @package App\Models
 */
class ProductVariantSpecificationValue extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_variant_id' => 'int',
        'specification_value_id' => 'int'
    ];

    protected $fillable = [
        'product_variant_id',
        'specification_value_id'
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function specificationValue(): BelongsTo
    {
        return $this->belongsTo(SpecificationValue::class);
    }
}
