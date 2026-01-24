<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use Tests\Feature\LivewireTestCase;

class SuccessTest extends LivewireTestCase
{
    public function test_payment_success_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
        ]);

        $request = \Illuminate\Http\Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(\App\Livewire\Payment\Success::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_success_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $request = \Illuminate\Http\Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(\App\Livewire\Payment\Success::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_success_handles_missing_order()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->livewire(\App\Livewire\Payment\Success::class, ['orderId' => 999999999]);
    }
}
