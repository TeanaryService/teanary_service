<?php

namespace App\Enums;

enum ShippingMethodEnum: string
{
    case SF_INTERNATIONAL = 'sf_international';

    public function label(): string
    {
        return match ($this) {
            self::SF_INTERNATIONAL => __('shipping.method.sf_international'),
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

    public function apiParams(): array
    {
        return match ($this) {
            self::SF_INTERNATIONAL => [
                'endpoint' => 'https://api.sf-express.com',
                'fields' => ['access_code', 'checkword', 'account']
            ],
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}