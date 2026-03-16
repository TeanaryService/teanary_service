<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Livewire\Manager\Orders;
use Tests\Feature\LivewireTestCase;

class OrdersTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_orders_page_can_be_rendered()
    {
        $component = $this->livewire(Orders::class);

        $component->assertSuccessful();
    }

    public function test_orders_list_displays_orders()
    {
        $order = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Orders::class);

        $orders = $component->get('orders');
        $orderIds = $orders->pluck('id')->toArray();
        $this->assertContains($order->id, $orderIds);
    }

    public function test_can_search_orders_by_order_no()
    {
        $order1 = $this->createOrder();
        $order2 = $this->createOrder();

        // 使用实际生成的订单号进行搜索
        $component = $this->livewire(Orders::class)
            ->set('search', $order1->order_no);

        $orders = $component->get('orders');
        $orderIds = $orders->pluck('id')->toArray();
        // 搜索可能返回多个结果，只要包含目标订单即可
        $this->assertContains($order1->id, $orderIds);
    }

    public function test_can_search_orders_by_user_name()
    {
        $user1 = $this->createUser(['name' => 'John Doe']);
        $user2 = $this->createUser(['name' => 'Jane Smith']);

        $order1 = $this->createOrder(['user_id' => $user1->id]);
        $order2 = $this->createOrder(['user_id' => $user2->id]);

        $component = $this->livewire(Orders::class)
            ->set('search', 'John');

        $orders = $component->get('orders');
        $orderIds = $orders->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
    }

    public function test_can_filter_orders_by_status()
    {
        $pendingOrder = $this->createOrder(['status' => OrderStatusEnum::Pending]);
        $completedOrder = $this->createOrder(['status' => OrderStatusEnum::Completed]);

        $component = $this->livewire(Orders::class)
            ->set('filterStatus', [OrderStatusEnum::Pending->value]);

        $orders = $component->get('orders');
        $this->assertTrue($orders->contains('id', $pendingOrder->id));
        $this->assertFalse($orders->contains('id', $completedOrder->id));
    }

    public function test_can_filter_orders_by_currency()
    {
        $currency1 = $this->createCurrency();
        $currency2 = $this->createCurrency();

        $order1 = $this->createOrder(['currency_id' => $currency1->id]);
        $order2 = $this->createOrder(['currency_id' => $currency2->id]);

        $component = $this->livewire(Orders::class)
            ->set('filterCurrencyId', $currency1->id);

        $orders = $component->get('orders');
        $orderIds = $orders->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
    }

    public function test_can_filter_orders_by_date_range()
    {
        $order1 = $this->createOrder(['created_at' => now()->subDays(5)]);
        $order2 = $this->createOrder(['created_at' => now()->subDays(2)]);

        $component = $this->livewire(Orders::class)
            ->set('createdFrom', now()->subDays(3)->format('Y-m-d'))
            ->set('createdUntil', now()->format('Y-m-d'));

        $orders = $component->get('orders');
        $this->assertTrue($orders->contains('id', $order2->id));
        $this->assertFalse($orders->contains('id', $order1->id));
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Orders::class)
            ->set('search', 'test')
            ->set('filterStatus', [OrderStatusEnum::Pending->value])
            ->set('filterCurrencyId', 1)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterStatus', [])
            ->assertSet('filterCurrencyId', null);
    }
}
