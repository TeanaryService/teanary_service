<?php

namespace App\Services\Shipping\Contracts;

use App\Models\Address;

interface ShippingCalculatorInterface
{
    public function calculate(array $processedItems, ?Address $address): array;
}
