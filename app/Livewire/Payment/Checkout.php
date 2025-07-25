<?php

namespace App\Livewire\Payment;

use App\Models\Order;
use App\Services\PaymentService;
use Livewire\Component;

class Checkout extends Component
{
    public int $orderId;
    
    public function mount(int $orderId)
    {
        $this->orderId = $orderId;
        $this->processPayment();
    }

    public function processPayment()
    {
        $order = Order::where('id', $this->orderId)->firstOrFail();
        $order->name = config('app.name') . __('app.order_items');
        
        $redirectUrl = app(PaymentService::class)->createPayment($order->payment_method, $order->toArray());
        
        return redirect()->away($redirectUrl);
    }

    public function render()
    {
        return view('livewire.payment.checkout');
    }
}
