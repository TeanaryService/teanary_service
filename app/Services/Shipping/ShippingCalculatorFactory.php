<?php

namespace App\Services\Shipping;

use App\Enums\ShippingMethodEnum;
use App\Services\Shipping\Calculators\EMSCalculator;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use App\Services\Shipping\Calculators\SFExpressCalculator;

class ShippingCalculatorFactory
{
    public static function make(ShippingMethodEnum $method): ?ShippingCalculatorInterface
    {
        return match ($method) {
            ShippingMethodEnum::SF_INTERNATIONAL => new SFExpressCalculator(),
            ShippingMethodEnum::EMS_INTERNATIONAL => new EMSCalculator(),
            default => null,
        };
    }
}
