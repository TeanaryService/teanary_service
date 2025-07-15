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
 * Class UserGroup
 * 
 * @property int $id
 * @property string $code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|ProductPrice[] $productPrices
 * @property Collection|Promotion[] $promotions
 * @property Collection|UserGroupTranslation[] $userGroupTranslations
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class UserGroup extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $fillable = [
        'code'
    ];

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(Promotion::class)
                    ->withPivot('id')
                    ->withTimestamps();
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
