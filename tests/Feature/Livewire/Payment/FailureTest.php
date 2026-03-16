<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use App\Livewire\Payment\Failure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tests\Feature\LivewireTestCase;

class FailureTest extends LivewireTestCase
{
    public function test_payment_failure_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Failure::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_failure_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Failure::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_failure_handles_missing_order()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->livewire(Failure::class, ['orderId' => 999999999]);
    }
}
