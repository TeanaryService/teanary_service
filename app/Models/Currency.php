<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Observers\CurrencyObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Currency
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $symbol
 * @property float $exchange_rate
 * @property bool $default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Order[] $orders
 * @property Collection|ProductVariant[] $productVariants
 * @property Collection|User[] $users
 *
 * @package App\Models
 */

 #[ObservedBy([CurrencyObserver::class])]

class Currency extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'exchange_rate' => 'float',
        'default' => 'bool'
    ];

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'default'
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
