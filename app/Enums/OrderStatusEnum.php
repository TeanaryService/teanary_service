<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case AfterSale = 'after_sale';                // 新增：售后处理中
    case AfterSaleCompleted = 'after_sale_done';  // 新增：售后完成

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('orders.status.pending'),
            self::Paid => __('orders.status.paid'),
            self::Shipped => __('orders.status.shipped'),
            self::Completed => __('orders.status.completed'),
            self::Cancelled => __('orders.status.cancelled'),
            self::AfterSale => __('orders.status.after_sale'),
            self::AfterSaleCompleted => __('orders.status.after_sale_done'),
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
     * 判断是否可以取消订单.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Pending, self::Paid]);
    }

    /**
     * 判断是否可以去支付.
     */
    public function canBePaid(): bool
    {
        return $this === self::Pending;
    }

    /**
     * 判断是否可以申请售后.
     */
    public function canRequestAfterSale(): bool
    {
        return in_array($this, [self::Shipped, self::Completed]);
    }

    /**
     * 判断是否正在售后处理中.
     */
    public function isAfterSaleProcessing(): bool
    {
        return $this === self::AfterSale;
    }

    /**
     * 判断是否已完成售后.
     */
    public function isAfterSaleCompleted(): bool
    {
        return $this === self::AfterSaleCompleted;
    }
}
