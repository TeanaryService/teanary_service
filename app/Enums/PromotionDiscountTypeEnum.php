<?php

namespace App\Enums;

enum PromotionDiscountTypeEnum: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => __('app.promotion.discount_type.fixed'),
            self::Percentage => __('app.promotion.discount_type.percentage'),
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
