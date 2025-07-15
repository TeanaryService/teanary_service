<?php

namespace App\Enums;

enum PromotionTypeEnum: string
{
    case Coupon = 'coupon';
    case Automatic = 'automatic';

    public function label(): string
    {
        return match ($this) {
            self::Coupon => '优惠券',
            self::Automatic => '自动促销',
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
