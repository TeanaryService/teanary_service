<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\ShippingMethodEnum;
use App\Traits\Syncable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderShipment.
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $tracking_number
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Order $order
 */
class OrderShipment extends Model
{
    use HasFactory;
    use Syncable;

    public static $snakeAttributes = false;

    protected $casts = [
        'order_id' => 'int',
        'shipping_method' => ShippingMethodEnum::class,
    ];

    protected $fillable = [
        'order_id',
        'shipping_method',
        'tracking_number',
        'notes',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
