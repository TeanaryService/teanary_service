<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Cart.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $user
 * @property Collection|CartItem[] $cartItems
 */
class Cart extends Model
{
    use HasFactory;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeEmpty($query)
    {
        return $query->whereDoesntHave('cartItems');
    }
}
