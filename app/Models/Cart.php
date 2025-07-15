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

/**
 * Class Cart
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property int|null $currency_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Currency|null $currency
 * @property User|null $user
 * @property Collection|CartItem[] $cartItems
 *
 * @package App\Models
 */
class Cart extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'user_id' => 'int',
        'currency_id' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'session_id',
        'currency_id'
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
