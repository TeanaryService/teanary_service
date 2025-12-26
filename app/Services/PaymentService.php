<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Address;
use App\Models\Order;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * 获取可用支付方式列表.
     */
    public function getAvailableMethods(?Address $address = null): array
    {
        if (! $address) {
            return [];
        }

        return PaymentMethodEnum::cases();
    }

    public function createPayment(PaymentMethodEnum $method, array $order): string
    {
        try {
            $gateway = PaymentManager::gateway($method);

            return $gateway->create($order);
        } catch (\Throwable $e) {
            Log::error('支付创建失败', [
                'method' => $method->value,
                'order' => $order,
                'message' => $e->getMessage(),
            ]);

            return locaRoute('payment.failure');
        }
    }

    public function handlePaymentSuccess(Order $order): void
    {
        if ($order->status === OrderStatusEnum::Pending) {
            $order->update([
                'status' => OrderStatusEnum::Paid,
            ]);

            // 可以在这里触发订单支付成功的事件
            // event(new OrderPaidEvent($order));
        }
    }
}
