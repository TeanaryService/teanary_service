<?php

namespace App\Services\Payments\Contracts;

interface PaymentGatewayInterface
{
    /**
     * 创建支付链接
     * @param array $order ['name' => string, 'amount' => float]
     * @return string
     */
    public function create(array $order): string;
}