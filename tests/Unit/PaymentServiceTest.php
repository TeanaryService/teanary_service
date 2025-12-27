<?php

namespace Tests\Unit;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Address;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService;
    }

    public function test_get_available_methods_returns_empty_when_no_address()
    {
        $methods = $this->service->getAvailableMethods(null);

        $this->assertIsArray($methods);
        $this->assertCount(0, $methods);
    }

    public function test_get_available_methods_returns_payment_methods_when_address_provided()
    {
        $address = Address::factory()->create();

        $methods = $this->service->getAvailableMethods($address);

        $this->assertIsArray($methods);
        $this->assertGreaterThan(0, count($methods));
        $this->assertInstanceOf(PaymentMethodEnum::class, $methods[0]);
    }

    public function test_handle_payment_success_updates_order_status()
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
        ]);

        $this->service->handlePaymentSuccess($order);

        $this->assertEquals(OrderStatusEnum::Paid, $order->fresh()->status);
    }

    public function test_handle_payment_success_does_not_update_when_not_pending()
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Paid,
        ]);

        $this->service->handlePaymentSuccess($order);

        $this->assertEquals(OrderStatusEnum::Paid, $order->fresh()->status);
    }
}
