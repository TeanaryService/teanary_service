<?php

namespace App\Models;

use App\Traits\HasSnowflakeId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfterSale extends Model
{
    use HasSnowflakeId;

    public static $snakeAttributes = false;

    protected $table = 'after_sales';

    protected $casts = [
        'order_id' => 'int',
        'order_item_id' => 'int',
        'product_id' => 'int',
        'exchange_product_id' => 'int',
        'warehouse_id' => 'int',
        'user_id' => 'int',
        'quantity' => 'int',
        'refund_amount' => 'float',
        'processed_at' => 'datetime',
        'images' => 'array',
    ];

    protected $fillable = [
        'order_id',
        'order_item_id',
        'product_id',
        'warehouse_id',
        'user_id',
        'type',
        'status',
        'reason',
        'description',
        'quantity',
        'refund_amount',
        'exchange_product_id',
        'images',
        'remarks',
        'logistics_company',
        'tracking_number',
        'processed_at',
    ];

    // 类型常量
    public const TYPE_REFUND_ONLY = 'refund_only';
    public const TYPE_REFUND_AND_RETURN = 'refund_and_return';
    public const TYPE_EXCHANGE = 'exchange';

    // 状态常量
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IN_RETURN = 'in_return';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function exchangeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'exchange_product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
