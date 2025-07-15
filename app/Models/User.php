<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property Carbon|null $email_verified_at
 * @property int|null $user_group_id
 * @property int|null $default_language_id
 * @property int|null $default_currency_id
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Currency|null $currency
 * @property Language|null $language
 * @property UserGroup|null $userGroup
 * @property Collection|Cart[] $carts
 * @property Collection|Order[] $orders
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'user_group_id' => 'int',
        'default_language_id' => 'int',
        'default_currency_id' => 'int'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'user_group_id',
        'default_language_id',
        'default_currency_id',
        'remember_token'
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'default_language_id');
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
