<?php

namespace App\Livewire\Payment;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Checkout extends Component
{
    public int $orderId;

    public bool $isProcessing = false;

    public string $errorMessage = '';

    public function mount(int $orderId)
    {
        $this->orderId = $orderId;
        // 页面加载完成后立即开始支付流程
        $this->isProcessing = true;
    }

    public function processPayment()
    {
        try {
            $this->isProcessing = true;
            $this->errorMessage = '';

            $order = Order::where('id', $this->orderId)->firstOrFail();
            $order->name = config('app.name').__('app.order_items');

            if (!$order->payment_method instanceof \App\Enums\PaymentMethodEnum) {
                throw new \RuntimeException('Invalid payment method');
            }

            $redirectUrl = app(PaymentService::class)->createPayment($order->payment_method, $order->toArray());

            // 立即跳转到支付页面
            $this->dispatch('redirect-to-payment', url: $redirectUrl);
        } catch (\Exception $e) {
            $this->isProcessing = false;
            $this->errorMessage = __('payment.payment_error');
            Log::error('Payment processing error: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.payment.checkout');
    }
}
