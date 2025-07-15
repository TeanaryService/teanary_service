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
 * Class ProductVariantValue
 * 
 * @property int $id
 * @property int $product_variant_id
 * @property int $attribute_value_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property AttributeValue $attributeValue
 * @property ProductVariant $productVariant
 *
 * @package App\Models
 */
class ProductVariantValue extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_variant_id' => 'int',
        'attribute_value_id' => 'int'
    ];

    protected $fillable = [
        'product_variant_id',
        'attribute_value_id'
    ];

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
