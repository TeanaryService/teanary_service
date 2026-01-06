<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderItem.
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int $qty
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 * @property Product $product
 * @property ProductVariant|null $productVariant
 */
class OrderItem extends Model
{
    use HasFactory;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'order_id' => 'int',
        'product_id' => 'int',
        'product_variant_id' => 'int',
        'qty' => 'int',
        'price' => 'float',
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'qty',
        'price',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
