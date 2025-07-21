<?php

namespace App\Services;

use App\Models\Address;
use App\Enums\ShippingMethodEnum;

class ShippingService
{
    /**
     * 获取可用配送方式列表
     * @param Address|null $address
     * @return array
     */
    public function getAvailableMethods(?Address $address = null): array
    {
        // 目前不做任何判断，返回所有配送方式
        return ShippingMethodEnum::cases();
    }
}
