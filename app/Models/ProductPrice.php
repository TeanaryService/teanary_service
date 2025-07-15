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
 * Class ProductPrice
 * 
 * @property int $id
 * @property int $product_id
 * @property int $user_group_id
 * @property int $currency_id
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Currency $currency
 * @property Product $product
 * @property UserGroup $userGroup
 *
 * @package App\Models
 */
class ProductPrice extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'product_id' => 'int',
        'user_group_id' => 'int',
        'currency_id' => 'int',
        'price' => 'float'
    ];

    protected $fillable = [
        'product_id',
        'user_group_id',
        'currency_id',
        'price'
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }
}
