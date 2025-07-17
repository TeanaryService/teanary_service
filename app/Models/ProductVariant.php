<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductVariant
 * 
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property float|null $price
 * @property float|null $cost
 * @property int $stock
 * @property float|null $weight
 * @property float|null $length
 * @property float|null $width
 * @property float|null $height
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Product $product
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|Specification[] $specifications
 * @property Collection|SpecificationValue[] $specificationValues
 * @property Collection|Promotion[] $promotions
 *
 * @package App\Models
 */
class ProductVariant extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'price' => 'float',
        'cost' => 'float',
        'stock' => 'int',
        'weight' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float'
    ];

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'cost',
        'stock',
        'weight',
        'length',
        'width',
        'height'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class, 'product_variant_specification_value')
            ->withPivot('specification_value_id');
    }

    public function specificationValues(): BelongsToMany
    {
        return $this->belongsToMany(SpecificationValue::class, 'product_variant_specification_value')
            ->withPivot('specification_id');
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class, 'promotion_product_variant');
    }
}
