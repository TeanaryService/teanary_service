<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Observers\PromotionRuleObserver;
use App\Traits\CascadesMediaDeletes;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class PromotionRule.
 *
 * @property int $id
 * @property int $promotion_id
 * @property string $condition_type
 * @property float $condition_value
 * @property string $discount_type
 * @property float $discount_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Promotion $promotion
 */
#[ObservedBy([PromotionRuleObserver::class])]

class PromotionRule extends Model implements HasMedia
{
    use CascadesMediaDeletes;
    use HasFactory;
    use HasSnowflakeId;
    use InteractsWithMedia;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'condition_value' => 'float',
        'discount_value' => 'float',
        'condition_type' => PromotionConditionTypeEnum::class,
        'discount_type' => PromotionDiscountTypeEnum::class,
    ];

    protected $fillable = [
        'promotion_id',
        'condition_type',
        'condition_value',
        'discount_type',
        'discount_value',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
