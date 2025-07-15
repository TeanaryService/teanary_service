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
 * Class PromotionUserGroup
 * 
 * @property int $id
 * @property int $promotion_id
 * @property int $user_group_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Promotion $promotion
 * @property UserGroup $userGroup
 *
 * @package App\Models
 */
class PromotionUserGroup extends Model
{
    use HasFactory;
    protected $table = 'shop_server.promotion_user_group';
    public static $snakeAttributes = false;

    protected $casts = [
        'promotion_id' => 'int',
        'user_group_id' => 'int'
    ];

    protected $fillable = [
        'promotion_id',
        'user_group_id'
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
