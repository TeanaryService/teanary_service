<?php

namespace App\Services;

use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Services\Shipping\ShippingCalculatorFactory;

class ShippingService
{
    public function getAvailableMethods(array $processedItems, ?Address $address = null): array
    {
        $methods = [];

        foreach (ShippingMethodEnum::cases() as $method) {
            $calculator = ShippingCalculatorFactory::make($method);

            $calculated = $calculator->calculate($processedItems, $address);

            if (empty($calculated)) {
                continue;
            }

            $methods[] = [
                'value' => $method->value,
                'label' => $method->label(),
                'description' => $calculated['description'],
                'fee' => $calculated['fee'],
            ];
        }

        return $methods;
    }
}
