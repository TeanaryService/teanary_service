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
            self::Pending => __('app.order.status.pending'),
            self::Paid => __('app.order.status.paid'),
            self::Shipped => __('app.order.status.shipped'),
            self::Completed => __('app.order.status.completed'),
            self::Cancelled => __('app.order.status.cancelled'),
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
}