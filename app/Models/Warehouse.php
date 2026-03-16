<?php

namespace App\Models;

use App\Traits\HasSnowflakeId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 仓库（分仓）.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $telephone
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $postcode
 * @property int|null $country_id
 * @property int|null $zone_id
 * @property bool $active
 * @property bool $is_default
 * @property int $sort_order
 */
class Warehouse extends Model
{
    use HasSnowflakeId;

    public static $snakeAttributes = false;

    protected $casts = [
        'country_id' => 'int',
        'zone_id' => 'int',
        'active' => 'bool',
        'is_default' => 'bool',
        'sort_order' => 'int',
    ];

    protected $fillable = [
        'name',
        'code',
        'telephone',
        'address_1',
        'address_2',
        'city',
        'postcode',
        'country_id',
        'zone_id',
        'active',
        'is_default',
        'sort_order',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse');
    }

    public function afterSales(): HasMany
    {
        return $this->hasMany(AfterSale::class);
    }

    /**
     * 转为“发货地地址”用于邮费计算（与 Address 结构兼容的数组）.
     */
    public function toOriginAddressArray(): array
    {
        return [
            'country_id' => $this->country_id,
            'zone_id' => $this->zone_id,
            'city' => $this->city,
            'postcode' => $this->postcode,
            'address_1' => $this->address_1,
        ];
    }
}
