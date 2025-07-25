<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('order.status.pending'),
            self::Paid => __('order.status.paid'),
            self::Shipped => __('order.status.shipped'),
            self::Completed => __('order.status.completed'),
            self::Cancelled => __('order.status.cancelled'),
        };
    }

    public static function default(): self
    {
        return self::Pending;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * 判断是否可以取消订单
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Paid]);
    }

    /**
     * 判断是否可以去支付
     */
    public function canBePaid(): bool
    {
        return $this === self::Pending;
    }

    /**
     * 判断是否可以申请售后
     */
    public function canRequestAfterSale(): bool
    {
        return in_array($this, [self::Shipped, self::Completed]);
    }
}