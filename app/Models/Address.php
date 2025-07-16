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
 * Class Address
 * 
 * @property int $id
 * @property int|null $user_id
 * @property int|null $country_id
 * @property int|null $zone_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $city
 * @property string|null $postcode
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Country|null $country
 * @property User|null $user
 * @property Zone|null $zone
 * @property Collection|Order[] $orders
 *
 * @package App\Models
 */
class Address extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'user_id' => 'int',
        'country_id' => 'int',
        'zone_id' => 'int'
    ];

    protected $fillable = [
        'user_id',
        'country_id',
        'zone_id',
        'first_name',
        'last_name',
        'city',
        'postcode',
        'address_1',
        'address_2',
        'phone'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }
}
