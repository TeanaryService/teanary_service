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
 * Class PromotionProductVariant
 * 
 * @property int $id
 * @property int $promotion_id
 * @property int $product_variant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property ProductVariant $productVariant
 * @property Promotion $promotion
 *
 * @package App\Models
 */
class PromotionProductVariant extends Model
{
    use HasFactory;
    protected $table = 'shop_server.promotion_product_variant';
    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'product_variant_id' => 'int'
    ];

    protected $fillable = [
        'promotion_id',
        'product_variant_id'
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
