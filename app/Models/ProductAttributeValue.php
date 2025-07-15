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
 * Class ProductAttributeValue
 * 
 * @property int $id
 * @property int $product_id
 * @property int $attribute_value_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property AttributeValue $attributeValue
 * @property Product $product
 *
 * @package App\Models
 */
class ProductAttributeValue extends Model
{
    use HasFactory;
    protected $table = 'shop_server.product_attribute_value';
    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'attribute_value_id' => 'int'
    ];

    protected $fillable = [
        'product_id',
        'attribute_value_id'
    ];

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
