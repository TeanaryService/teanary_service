<?php

namespace App\Services;

use App\Models\Address;
use App\Enums\PaymentMethodEnum;

class PaymentService
{
    /**
     * 获取可用支付方式列表
     * @param Address|null $address
     * @return array
     */
    public function getAvailableMethods(?Address $address = null): array
    {
        // 目前不做任何判断，返回所有支付方式
        return PaymentMethodEnum::cases();
    }
}
