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
 * Class PromotionRule
 * 
 * @property int $id
 * @property int $promotion_id
 * @property string $condition_type
 * @property float $condition_value
 * @property string $discount_type
 * @property float $discount_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Promotion $promotion
 *
 * @package App\Models
 */
class PromotionRule extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'condition_value' => 'float',
        'discount_value' => 'float'
    ];

    protected $fillable = [
        'promotion_id',
        'condition_type',
        'condition_value',
        'discount_type',
        'discount_value'
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
