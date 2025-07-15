<?php

namespace App\Enums;

enum PromotionConditionTypeEnum: string
{
    case OrderTotalMin = 'order_total_min';
    case OrderQtyMin = 'order_qty_min';

    public function label(): string
    {
        return match ($this) {
            self::OrderTotalMin => '订单金额下限',
            self::OrderQtyMin => '订单数量下限',
        };
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
