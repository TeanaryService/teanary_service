<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class Dashboard extends Component
{
    public int $totalUsers = 0;
    public int $totalOrders = 0;
    public float $totalRevenue = 0.0;
    public $recentOrders;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        // 总用户数
        $this->totalUsers = User::count();

        // 总订单数
        $this->totalOrders = Order::count();

        // 总收入：只计算已支付、已发货、已完成状态的订单，并进行汇率转换
        $orders = Order::query()
            ->with('currency')
            ->whereIn('status', [
                OrderStatusEnum::Paid,
                OrderStatusEnum::Shipped,
                OrderStatusEnum::Completed,
            ])
            ->get();

        $service = app(LocaleCurrencyService::class);
        $defaultCurrencyCode = $service->getDefaultCurrencyCode();
        
        $this->totalRevenue = $orders->sum(function ($order) use ($service, $defaultCurrencyCode) {
            if (!$order->total || $order->total == 0) {
                return 0;
            }
            
            // 如果订单没有货币，使用默认货币
            $orderCurrencyCode = $order->currency?->code ?? $defaultCurrencyCode;
            
            // 将所有订单金额转换为默认货币后求和
            // convert(amount, toCode, fromCode)
            return $service->convert($order->total, $defaultCurrencyCode, $orderCurrencyCode);
        });
            
        // 最近订单：获取最新的5个订单
        $this->recentOrders = Order::query()
            ->with(['orderItems.product', 'user', 'currency'])
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.manager.dashboard')->layout('components.layouts.manager');
    }
}
