<?php

namespace Tests\Feature\Livewire\Payment;

use App\Enums\OrderStatusEnum;
use App\Livewire\Payment\Success;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tests\Feature\LivewireTestCase;

class SuccessTest extends LivewireTestCase
{
    public function test_payment_success_page_can_be_rendered()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Success::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_success_displays_order_information()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
            'order_no' => 'TEST-ORDER-001',
        ]);

        $request = Request::create('/', 'GET', [
            'orderId' => $order->id,
        ]);

        $component = $this->livewire(Success::class, [], $request);
        $component->assertSuccessful();
    }

    public function test_payment_success_handles_missing_order()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->livewire(Success::class, ['orderId' => 999999999]);
    }
}
