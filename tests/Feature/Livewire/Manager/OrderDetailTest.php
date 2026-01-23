<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\OrderShipment;
use Tests\Feature\LivewireTestCase;

class OrderDetailTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_order_detail_page_can_be_rendered()
    {
        $order = $this->createOrder();

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id]);
        $component->assertSuccessful();
    }

    public function test_order_detail_displays_order_information()
    {
        $order = $this->createOrder([
            'order_no' => 'TEST-ORDER-001',
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id]);
        $component->assertSuccessful();
        $this->assertEquals($order->id, $component->get('order')->id);
    }

    public function test_can_update_order_status()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->call('updateStatus', OrderStatusEnum::Paid->value);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Paid, $order->status);
    }

    public function test_can_create_shipment()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->set('shippingMethod', ShippingMethodEnum::SF_INTERNATIONAL->value)
            ->set('trackingNumber', 'TRACK123')
            ->set('notes', 'Test notes')
            ->call('createShipment');

        $this->assertDatabaseHas('order_shipments', [
            'order_id' => $order->id,
            'tracking_number' => 'TRACK123',
        ]);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Shipped, $order->status);
    }

    public function test_creating_shipment_auto_updates_status_to_shipped()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->set('shippingMethod', ShippingMethodEnum::SF_INTERNATIONAL->value)
            ->set('trackingNumber', 'TRACK123')
            ->call('createShipment');

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Shipped, $order->status);
    }

    public function test_can_delete_shipment()
    {
        $order = $this->createOrder();
        $shipment = OrderShipment::factory()->create([
            'order_id' => $order->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->call('deleteShipment', $shipment->id);

        $this->assertDatabaseMissing('order_shipments', ['id' => $shipment->id]);
    }

    public function test_shipment_form_validates_required_fields()
    {
        $order = $this->createOrder();

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->call('createShipment')
            ->assertHasErrors(['shippingMethod']);
    }

    public function test_can_toggle_shipment_form()
    {
        $order = $this->createOrder();

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->call('toggleShipmentForm')
            ->assertSet('showShipmentForm', true)
            ->call('toggleShipmentForm')
            ->assertSet('showShipmentForm', false);
    }

    public function test_reset_shipment_form_clears_fields()
    {
        $order = $this->createOrder();

        $component = $this->livewire(\App\Livewire\Manager\OrderDetail::class, ['id' => $order->id])
            ->set('shippingMethod', ShippingMethodEnum::SF_INTERNATIONAL->value)
            ->set('trackingNumber', 'TRACK123')
            ->set('notes', 'Test notes')
            ->call('resetShipmentForm')
            ->assertSet('shippingMethod', '')
            ->assertSet('trackingNumber', null)
            ->assertSet('notes', null);
    }
}
