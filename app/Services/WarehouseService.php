<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WarehouseService
{
    public function getWarehouses()
    {
        return Cache::rememberForever(CacheKeys::WAREHOUSES_ALL, function () {
            if (! Schema::hasTable((new Warehouse)->getTable())) {
                return collect();
            }

            return Warehouse::where('active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        });
    }

    public function clearWarehousesCache(): void
    {
        Cache::forget(CacheKeys::WAREHOUSES_ALL);
    }

    public function getWarehouseById($id): ?Warehouse
    {
        $warehouses = $this->getWarehouses();

        return $warehouses->firstWhere('id', $id);
    }

    public function getDefaultWarehouse(): ?Warehouse
    {
        $warehouses = $this->getWarehouses();

        return $warehouses->firstWhere('is_default', true) ?? $warehouses->first();
    }

    public function getDefaultWarehouseId(): ?int
    {
        $warehouse = $this->getDefaultWarehouse();

        return $warehouse?->id;
    }
}
