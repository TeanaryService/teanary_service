<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Promotion
 * 
 * @property int $id
 * @property string|null $code
 * @property string $type
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|PromotionRule[] $promotionRules
 * @property Collection|PromotionTranslation[] $promotionTranslations
 * @property Collection|UserGroup[] $userGroups
 *
 * @package App\Models
 */
class Promotion extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'active' => 'bool'
    ];

    protected $fillable = [
        'code',
        'type',
        'starts_at',
        'ends_at',
        'active'
    ];

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
                    ->withPivot('id')
                    ->withTimestamps();
    }
}
