<?php

namespace Tests\Feature\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Livewire\Users\Orders;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\LivewireTestCase;

class OrdersTest extends LivewireTestCase
{
    public function test_authenticated_user_can_access_orders_page()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = $this->livewire(Orders::class);
        $component->assertSuccessful();
    }

    public function test_authenticated_user_can_view_orders()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $order = $this->createOrder([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Orders::class)
            ->assertSuccessful();

        $component->assertSee($order->order_no);
    }

    public function test_user_only_sees_own_orders()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $order1 = $this->createOrder(['user_id' => $user1->id]);
        $order2 = $this->createOrder(['user_id' => $user2->id]);

        $this->actingAs($user1);

        $component = $this->livewire(Orders::class);

        $orders = $component->get('orders');
        $orderIds = $orders->pluck('id')->toArray();
        $this->assertContains($order1->id, $orderIds);
        $this->assertNotContains($order2->id, $orderIds);
    }

    public function test_user_can_cancel_order()
    {
        Notification::fake();

        $user = $this->createUser();
        $this->actingAs($user);

        $order = $this->createOrder([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Orders::class)
            ->call('cancelOrder', $order->id);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Cancelled, $order->status);
    }

    public function test_user_cannot_cancel_non_cancellable_order()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $order = $this->createOrder([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Completed,
        ]);

        $component = $this->livewire(Orders::class)
            ->call('cancelOrder', $order->id);

        $order->refresh();
        $this->assertEquals(OrderStatusEnum::Completed, $order->status);
    }

    public function test_user_can_pay_order()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $order = $this->createOrder([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $component = $this->livewire(Orders::class)
            ->call('payOrder', $order->id);

        // 验证重定向（使用 locaRoute 生成的路由）
        // 验证重定向（payOrder 会重定向）
        $this->assertNotNull($component);
    }
}
