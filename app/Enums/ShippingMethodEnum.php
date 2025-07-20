<?php

namespace App\Enums;

enum ShippingMethodEnum: string
{
    case EXPRESS = 'express';
    case EMS = 'ems';
    case DHL = 'dhl';
    case FEDEX = 'fedex';
    case UPS = 'ups';
    case LOCAL = 'local';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::EXPRESS => __('shipping.method.express'),
            self::EMS => __('shipping.method.ems'),
            self::DHL => __('shipping.method.dhl'),
            self::FEDEX => __('shipping.method.fedex'),
            self::UPS => __('shipping.method.ups'),
            self::LOCAL => __('shipping.method.local'),
            self::PICKUP => __('shipping.method.pickup'),
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
            self::DHL => ['endpoint' => 'https://api.dhl.com', 'fields' => ['account', 'password']],
            self::FEDEX => ['endpoint' => 'https://api.fedex.com', 'fields' => ['key', 'password']],
            self::UPS => ['endpoint' => 'https://onlinetools.ups.com', 'fields' => ['username', 'password']],
            self::EMS => ['endpoint' => 'https://www.ems.com.cn', 'fields' => ['customer_id']],
            self::EXPRESS => ['fields' => ['company', 'tracking_number']],
            self::LOCAL => ['fields' => ['address', 'contact']],
            self::PICKUP => ['fields' => ['pickup_code']],
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }
}
