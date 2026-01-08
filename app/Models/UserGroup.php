<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\TranslationStatusEnum;
use App\Observers\UserGroupObserver;
use App\Traits\HasSnowflakeId;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class UserGroup.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Collection|Promotion[] $promotions
 * @property Collection|UserGroupTranslation[] $userGroupTranslations
 * @property Collection|User[] $users
 */
#[ObservedBy([UserGroupObserver::class])]
class UserGroup extends Model
{
    use HasFactory;
    use Syncable;
    use HasSnowflakeId;

    public static $snakeAttributes = false;

    protected $casts = [
        'translation_status' => TranslationStatusEnum::class,
    ];

    protected $fillable = [
        'translation_status',
    ];

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)
            ->using(PromotionUserGroup::class);
    }

    public function userGroupTranslations(): HasMany
    {
        return $this->hasMany(UserGroupTranslation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
