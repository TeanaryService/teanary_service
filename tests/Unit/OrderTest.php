<?php

namespace Tests\Unit;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_be_created_using_factory()
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertIsString($order->order_no);
    }

    public function test_status_attribute_casting()
    {
        $order = Order::factory()->create([
            'status' => OrderStatusEnum::Pending,
        ]);

        $this->assertInstanceOf(OrderStatusEnum::class, $order->status);
        $this->assertEquals(OrderStatusEnum::Pending, $order->status);
    }

    public function test_payment_method_attribute_casting()
    {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethodEnum::PAYPAL,
        ]);

        $this->assertInstanceOf(PaymentMethodEnum::class, $order->payment_method);
        $this->assertEquals(PaymentMethodEnum::PAYPAL, $order->payment_method);
    }

    public function test_shipping_method_attribute_casting()
    {
        $order = Order::factory()->create([
            'shipping_method' => ShippingMethodEnum::SF_INTERNATIONAL,
        ]);

        $this->assertInstanceOf(ShippingMethodEnum::class, $order->shipping_method);
        $this->assertEquals(ShippingMethodEnum::SF_INTERNATIONAL, $order->shipping_method);
    }

    public function test_shipping_address_relationship()
    {
        $order = new Order;
        $relation = $order->shippingAddress();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('shipping_address_id', $relation->getForeignKeyName());
    }

    public function test_billing_address_relationship()
    {
        $order = new Order;
        $relation = $order->billingAddress();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('billing_address_id', $relation->getForeignKeyName());
    }

    public function test_currency_relationship()
    {
        $order = new Order;
        $relation = $order->currency();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('currency_id', $relation->getForeignKeyName());
    }

    public function test_user_relationship()
    {
        $order = new Order;
        $relation = $order->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    public function test_order_items_relationship()
    {
        $order = new Order;
        $relation = $order->orderItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('order_id', $relation->getForeignKeyName());
    }

    public function test_order_shipments_relationship()
    {
        $order = new Order;
        $relation = $order->orderShipments();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('order_id', $relation->getForeignKeyName());
    }
}
