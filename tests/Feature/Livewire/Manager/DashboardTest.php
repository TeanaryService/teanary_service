<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Livewire\Manager\Dashboard;
use Tests\Feature\LivewireTestCase;

class DashboardTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_dashboard_page_can_be_rendered()
    {
        $component = $this->livewire(Dashboard::class);
        $component->assertSuccessful();
    }

    public function test_dashboard_displays_total_users()
    {
        $this->createUser();
        $this->createUser();

        $component = $this->livewire(Dashboard::class);

        $this->assertEquals(2, $component->get('totalUsers'));
    }

    public function test_dashboard_displays_total_orders()
    {
        $this->createOrder();
        $this->createOrder();

        $component = $this->livewire(Dashboard::class);

        $this->assertEquals(2, $component->get('totalOrders'));
    }

    public function test_dashboard_calculates_total_revenue()
    {
        $currency = $this->createCurrency(['code' => 'USD', 'exchange_rate' => 1.0]);

        $this->createOrder([
            'status' => OrderStatusEnum::Paid,
            'total' => 100.00,
            'currency_id' => $currency->id,
        ]);
        $this->createOrder([
            'status' => OrderStatusEnum::Completed,
            'total' => 200.00,
            'currency_id' => $currency->id,
        ]);

        $component = $this->livewire(Dashboard::class);

        $this->assertGreaterThan(0, $component->get('totalRevenue'));
    }

    public function test_dashboard_only_counts_revenue_from_paid_orders()
    {
        $currency = $this->createCurrency(['code' => 'USD', 'exchange_rate' => 1.0]);

        $paidOrder = $this->createOrder([
            'status' => OrderStatusEnum::Paid,
            'total' => 100.00,
            'currency_id' => $currency->id,
        ]);
        $pendingOrder = $this->createOrder([
            'status' => OrderStatusEnum::Pending,
            'total' => 200.00,
            'currency_id' => $currency->id,
        ]);

        $component = $this->livewire(Dashboard::class);

        $revenue = $component->get('totalRevenue');
        $this->assertGreaterThanOrEqual(100.00, $revenue);
        $this->assertLessThan(300.00, $revenue);
    }

    public function test_dashboard_displays_recent_orders()
    {
        $order1 = $this->createOrder(['created_at' => now()->subDays(2)]);
        $order2 = $this->createOrder(['created_at' => now()->subDays(1)]);

        $component = $this->livewire(Dashboard::class);

        $recentOrders = $component->get('recentOrders');
        $this->assertGreaterThanOrEqual(2, $recentOrders->count());
    }

    public function test_dashboard_limits_recent_orders_to_5()
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->createOrder();
        }

        $component = $this->livewire(Dashboard::class);

        $recentOrders = $component->get('recentOrders');
        $this->assertLessThanOrEqual(5, $recentOrders->count());
    }
}
