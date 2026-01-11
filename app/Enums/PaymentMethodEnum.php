<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case PAYPAL = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::PAYPAL => __('payment.method.paypal'),
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
            self::PAYPAL => [
                'sandBox' => [
                    'client_id' => config('payments.paypal.sandbox.client_id', ''),
                    'secret' => config('payments.paypal.sandbox.secret', ''),
                ],
                'prod' => [
                    'client_id' => config('payments.paypal.production.client_id', ''),
                    'secret' => config('payments.paypal.production.secret', ''),
                ],
            ],
        };
    }

    public static function random(): self
    {
        return self::cases()[array_rand(self::cases())];
    }

    /**
     * 根据字符串值获取枚举对象
     */
    public static function fromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null; // 或者抛出异常
    }
}
