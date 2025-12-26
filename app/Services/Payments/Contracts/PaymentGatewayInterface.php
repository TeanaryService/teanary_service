<?php

namespace App\Services\Payments\Contracts;

interface PaymentGatewayInterface
{
    /**
     * 创建支付链接.
     *
     * @param  array  $order  ['name' => string, 'amount' => float]
     */
    public function create(array $order): string;
}
