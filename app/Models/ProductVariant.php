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
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductVariant
 * 
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property int|null $currency_id
 * @property float|null $price
 * @property int $stock
 * @property float|null $weight
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Currency|null $currency
 * @property Product $product
 * @property Collection|CartItem[] $cartItems
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|ProductVariantValue[] $productVariantValues
 *
 * @package App\Models
 */
class ProductVariant extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'currency_id' => 'int',
        'price' => 'float',
        'stock' => 'int',
        'weight' => 'float'
    ];

    protected $fillable = [
        'product_id',
        'sku',
        'currency_id',
        'price',
        'stock',
        'weight'
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

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

    public function productVariantValues(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class);
    }
}
