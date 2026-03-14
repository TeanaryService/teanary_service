<?php

namespace App\Services\Shipping\Contracts;

use App\Models\Address;
use App\Models\Warehouse;

interface ShippingCalculatorInterface
{
    /**
     * @param  array  $processedItems  订单商品（含 weight、qty 等）
     * @param  Address|null  $destination  收货地址（目的地）
     * @param  Warehouse|null  $originWarehouse  发货仓库（邮费计算起点）
     */
    public function calculate(array $processedItems, ?Address $destination, ?Warehouse $originWarehouse = null): array;
}
