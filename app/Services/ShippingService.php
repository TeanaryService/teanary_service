<?php

namespace App\Services;

use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Services\Shipping\ShippingCalculatorFactory;
use App\Services\WarehouseService;

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

    protected function getCurrentWarehouse(): ?\App\Models\Warehouse
    {
        $warehouseId = session('warehouse_id');
        if (! $warehouseId) {
            return null;
        }

        return app(WarehouseService::class)->getWarehouseById($warehouseId);
    }
}
