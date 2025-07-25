<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('product.status.active'),
            self::Inactive => __('product.status.inactive'),
        };
    }

    public static function default(): self
    {
        return self::Active;
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
