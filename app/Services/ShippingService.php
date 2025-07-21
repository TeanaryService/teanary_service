<?php

namespace App\Services;

use App\Models\Address;
use App\Enums\ShippingMethodEnum;
use App\Services\Shipping\ShippingCalculatorFactory;

class ShippingService
{
    public function getAvailableMethods(array $processedItems, ?Address $address = null): array
    {
        $methods = [];

        foreach (ShippingMethodEnum::cases() as $method) {
            $calculator = ShippingCalculatorFactory::make($method);

            $calculated = $calculator && $address
                ? $calculator->calculate($processedItems, $address)
                : ['description' => __('shipping.description.placeholder'), 'fee' => 0.00];

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
