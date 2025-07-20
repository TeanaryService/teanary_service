<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Observers\OrderObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Order
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $order_no
 * @property int|null $currency_id
 * @property int|null $payment_method
 * @property int|null $shipping_address_id
 * @property int|null $billing_address_id
 * @property float $total
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Address|null $address
 * @property Currency|null $currency
 * @property PaymentMethod|null $paymentMethod
 * @property ShippingMethod|null $shippingMethod
 * @property User|null $user
 * @property Collection|OrderItem[] $orderItems
 * @property Collection|OrderShipment[] $orderShipments
 *
 * @package App\Models
 */

#[ObservedBy([OrderObserver::class])]

class Order extends Model
{
    use HasFactory;
    public static $snakeAttributes = false;

    protected $casts = [
        'user_id' => 'int',
        'currency_id' => 'int',
        'payment_method' => PaymentMethodEnum::class,
        'shipping_address_id' => 'int',
        'billing_address_id' => 'int',
        'total' => 'float',
        'status' => OrderStatusEnum::class
    ];

    protected $fillable = [
        'user_id',
        'order_no',
        'currency_id',
        'payment_method',
        'shipping_address_id',
        'billing_address_id',
        'total',
        'status'
    ];

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderShipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }
}
