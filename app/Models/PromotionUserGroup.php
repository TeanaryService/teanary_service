<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\PromotionUserGroupObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class PromotionUserGroup.
 *
 * @property int $promotion_id
 * @property int $user_group_id
 * @property Promotion $promotion
 * @property UserGroup $userGroup
 */
#[ObservedBy([PromotionUserGroupObserver::class])]

class PromotionUserGroup extends Pivot
{
    use HasFactory;

    protected $table = 'shop_server.promotion_user_group';

    public $incrementing = false;

    public $timestamps = false;

    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'user_group_id' => 'int',
    ];

    protected $fillable = [
        'promotion_id',
        'user_group_id',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }
}
