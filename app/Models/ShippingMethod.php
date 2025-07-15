<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ShippingMethod
 * 
 * @property int $id
 * @property string $code
 * @property bool $active
 * @property string|null $api_url
 * @property string|null $api_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Order[] $orders
 * @property Collection|ShippingMethodTranslation[] $shippingMethodTranslations
 *
 * @package App\Models
 */
class ShippingMethod extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'active' => 'bool'
    ];

    protected $hidden = [
        'api_token'
    ];

    protected $fillable = [
        'code',
        'active',
        'api_url',
        'api_token'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shippingMethodTranslations(): HasMany
    {
        return $this->hasMany(ShippingMethodTranslation::class);
    }
}
