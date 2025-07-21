<?php

namespace App\Services;

use App\Models\Address;
use App\Enums\ShippingMethodEnum;

class ShippingService
{
    /**
     * 获取可用配送方式列表（只返回 value 和 label，fee 与 description 占位）
     *
     * @param Address|null $address
     * @return array
     */
    public function getAvailableMethods(?Address $address = null): array
    {
        $methods = [];

        foreach (ShippingMethodEnum::cases() as $method) {
            $methods[] = [
                'value' => $method->value,
                'label' => $method->label(),             // 多语言名称
                'description' => '预计3-5天送达',                     // 占位，后续调接口填充
                'fee' => 5.00,                           // 占位，后续调接口填充
            ];
        }

        return $methods;
    }

    /**
     * 调用物流接口获取费用和时效信息
     *
     * @param ShippingMethodEnum $method
     * @param Address|null $address
     * @return array ['fee' => float, 'description' => string]
     */
    public function fetchShippingQuote(ShippingMethodEnum $method, ?Address $address = null): array
    {
        // TODO: 在此集成第三方物流接口，获取 fee 和时效说明 description
        return [
            'fee' => 0.00,
            'description' => '',
        ];
    }
}
