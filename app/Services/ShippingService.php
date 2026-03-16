<?php

namespace App\Services;

use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Models\Warehouse;
use App\Services\Shipping\ShippingCalculatorFactory;

class ShippingService
{
    public function getAvailableMethods(array $processedItems, ?Address $address = null): array
    {
        $methods = [];
        $originWarehouse = $this->getCurrentWarehouse();

        foreach (ShippingMethodEnum::cases() as $method) {
            $calculator = ShippingCalculatorFactory::make($method);

            $calculated = $calculator->calculate($processedItems, $address, $originWarehouse);

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

    protected function getCurrentWarehouse(): ?Warehouse
    {
        $warehouseId = session('warehouse_id');
        if (! $warehouseId) {
            return null;
        }

        return app(WarehouseService::class)->getWarehouseById($warehouseId);
    }
}
