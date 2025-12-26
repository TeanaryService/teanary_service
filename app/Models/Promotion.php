<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\PromotionTypeEnum;
use App\Observers\PromotionObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion.
 *
 * @property int $id
 * @property string $type
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|ProductVariant[] $productVariants
 * @property Collection|PromotionRule[] $promotionRules
 * @property Collection|PromotionTranslation[] $promotionTranslations
 * @property Collection|UserGroup[] $userGroups
 */
#[ObservedBy([PromotionObserver::class])]

class Promotion extends Model
{
    use HasFactory;

    public static $snakeAttributes = false;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'bool',
        'type' => PromotionTypeEnum::class,
    ];

    protected $fillable = [
        'type',
        'starts_at',
        'ends_at',
        'active',
    ];

    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'promotion_product_variant')
            ->withPivot(['product_id', 'product_variant_id', 'promotion_id']);
    }

    public function promotionRules(): HasMany
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function promotionTranslations(): HasMany
    {
        return $this->hasMany(PromotionTranslation::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)
            ->using(PromotionUserGroup::class);
    }
}
