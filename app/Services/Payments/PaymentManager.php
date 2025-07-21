<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethodEnum;
use App\Services\Payments\Contracts\PaymentGatewayInterface;

class PaymentManager
{
    public static function gateway(PaymentMethodEnum $method): PaymentGatewayInterface
    {
        return match ($method) {
            PaymentMethodEnum::PAYPAL => new PaypalGateway(),
        };
    }
}
