<?php

namespace App\Enums;

enum ShippingMethodEnum: string
{
    case SF_INTERNATIONAL = 'sf_international';

    public function label(): string
    {
        return match ($this) {
            self::SF_INTERNATIONAL => __('app.shipping.method.sf_international'),
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
                'sandBox' => [
                    'partnerId' => 'YL7U5E9T',
                    'checkword' => 'YphQd6pGMyLtHAZxo2TNrrGA3XxK9oTS',
                    'endpoint' => 'http://sfapi-sbox.sf-express.com/std/service'
                ],
                'prod' => [
                    'partnerId' => 'YL7U5E9T',
                    'checkword' => 'dZyLxDx5Z5b68gUCo5sdxUSbeT9tGAqb',
                    'endpoint' => 'https://sfapi.sf-express.com/std/service'
                ]
            ],
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}
