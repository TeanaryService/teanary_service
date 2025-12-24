<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentService;
use App\Models\Order;
use App\Models\Address;
use App\Enums\PaymentMethodEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService();
    }

    public function test_get_available_methods_returns_empty_array_when_no_address(): void
    {
        $methods = $this->service->getAvailableMethods(null);

        $this->assertIsArray($methods);
        $this->assertEmpty($methods);
    }

    public function test_get_available_methods_returns_all_payment_methods_when_address_provided(): void
    {
        $address = Address::factory()->create();

        $methods = $this->service->getAvailableMethods($address);

        $this->assertIsArray($methods);
        $this->assertNotEmpty($methods);
        $this->assertContainsOnlyInstancesOf(PaymentMethodEnum::class, $methods);
    }

    public function test_handle_payment_success_updates_order_status(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending
        ]);

        $this->service->handlePaymentSuccess($order);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Paid->value, $order->status->value);
    }

    public function test_handle_payment_success_does_not_update_when_already_paid(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Paid
        ]);

        $this->service->handlePaymentSuccess($order);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Paid->value, $order->status->value);
    }

    public function test_handle_payment_success_does_not_update_when_not_pending(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Shipped
        ]);

        $this->service->handlePaymentSuccess($order);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Shipped->value, $order->status->value);
    }
}

