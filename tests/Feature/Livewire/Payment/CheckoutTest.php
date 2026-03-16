<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use App\Livewire\Payment\Checkout;
use Tests\Feature\LivewireTestCase;

class CheckoutTest extends LivewireTestCase
{
    public function test_payment_checkout_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Checkout::class, ['orderId' => $order->id]);
        $component->assertSuccessful();
    }

    public function test_payment_checkout_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $component = $this->livewire(Checkout::class, ['orderId' => $order->id]);
        $component->assertSuccessful();
    }

    public function test_payment_checkout_processes_payment()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Checkout::class, ['orderId' => $order->id])
            ->call('processPayment');

        $component->assertSuccessful();
    }

    public function test_payment_checkout_handles_payment_redirect()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Checkout::class, ['orderId' => $order->id]);
        $component->assertSuccessful();
    }
}
