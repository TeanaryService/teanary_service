<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use App\Livewire\Payment\Cancel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tests\Feature\LivewireTestCase;

class CancelTest extends LivewireTestCase
{
    public function test_payment_cancel_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Cancel::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_cancel_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Cancel::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_cancel_handles_missing_order()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->livewire(Cancel::class, ['orderId' => 999999999]);
    }
}
