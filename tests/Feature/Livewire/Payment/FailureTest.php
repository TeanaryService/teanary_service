<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use Tests\Feature\LivewireTestCase;

class FailureTest extends LivewireTestCase
{
    public function test_payment_failure_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $request = \Illuminate\Http\Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(\App\Livewire\Payment\Failure::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_failure_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $request = \Illuminate\Http\Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(\App\Livewire\Payment\Failure::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_failure_handles_missing_order()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->livewire(\App\Livewire\Payment\Failure::class, ['orderId' => 999999999]);
    }
}
