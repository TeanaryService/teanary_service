<?php

namespace App\Livewire\Manager;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Component;

class Dashboard extends Component
{
    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

    public function getUserStatsProperty(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // 今日新增用户
        $usersToday = User::whereDate('created_at', $today)->count();
        $usersYesterday = User::whereDate('created_at', $yesterday)->count();
        $usersTodayDiff = $usersYesterday > 0 
            ? (($usersToday - $usersYesterday) / $usersYesterday) * 100 
            : ($usersToday > 0 ? 100 : 0);

        // 总用户数
        $totalUsers = User::count();
        $totalUsersLastMonth = User::where('created_at', '<', $thisMonth)->count();
        $totalUsersDiff = $totalUsersLastMonth > 0 
            ? (($totalUsers - $totalUsersLastMonth) / $totalUsersLastMonth) * 100 
            : ($totalUsers > 0 ? 100 : 0);

        // 活跃用户（有订单的用户）
        $activeUsers = User::whereHas('orders')->count();
        $activeUsersLastMonth = User::whereHas('orders', function ($query) use ($thisMonth) {
            $query->where('created_at', '<', $thisMonth);
        })->count();
        $activeUsersDiff = $activeUsersLastMonth > 0 
            ? (($activeUsers - $activeUsersLastMonth) / $activeUsersLastMonth) * 100 
            : ($activeUsers > 0 ? 100 : 0);

        // 平均订单价值
        $currentCurrencyCode = session('currency') ?? $this->localeService->getDefaultCurrencyCode();
        $defaultCurrencyCode = $this->localeService->getDefaultCurrencyCode();
        $orders = Order::with('currency')->get();
        
        $totalConverted = $orders->sum(function ($order) use ($currentCurrencyCode, $defaultCurrencyCode) {
            if (!$order->total || $order->total == 0) {
                return 0;
            }
            $orderCurrencyCode = $order->currency?->code ?? $defaultCurrencyCode;
            return $this->localeService->convert($order->total, $currentCurrencyCode, $orderCurrencyCode);
        });
        $avgOrderValue = $orders->count() > 0 ? $totalConverted / $orders->count() : 0;

        return [
            'users_today' => $usersToday,
            'users_today_diff' => $usersTodayDiff,
            'total_users' => $totalUsers,
            'total_users_diff' => $totalUsersDiff,
            'active_users' => $activeUsers,
            'active_users_diff' => $activeUsersDiff,
            'avg_order_value' => $avgOrderValue,
            'currency_code' => $currentCurrencyCode,
        ];
    }

    public function getOrderStatsProperty(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // 今日订单
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersYesterday = Order::whereDate('created_at', $yesterday)->count();
        $ordersTodayDiff = $ordersYesterday > 0 
            ? (($ordersToday - $ordersYesterday) / $ordersYesterday) * 100 
            : ($ordersToday > 0 ? 100 : 0);

        // 今日销售额
        $currentCurrencyCode = session('currency') ?? $this->localeService->getDefaultCurrencyCode();
        $revenueToday = $this->calculateRevenueInCurrency(
            Order::whereDate('created_at', $today)->with('currency')->get(),
            $currentCurrencyCode
        );
        $revenueYesterday = $this->calculateRevenueInCurrency(
            Order::whereDate('created_at', $yesterday)->with('currency')->get(),
            $currentCurrencyCode
        );
        $revenueTodayDiff = $revenueYesterday > 0 
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100 
            : ($revenueToday > 0 ? 100 : 0);

        // 待处理订单
        $pendingOrders = Order::whereIn('status', [\App\Enums\OrderStatusEnum::Pending, \App\Enums\OrderStatusEnum::Paid])->count();

        // 本月销售额
        $revenueThisMonth = $this->calculateRevenueInCurrency(
            Order::where('created_at', '>=', $thisMonth)->with('currency')->get(),
            $currentCurrencyCode
        );
        $revenueLastMonth = $this->calculateRevenueInCurrency(
            Order::whereBetween('created_at', [$lastMonth, $thisMonth->copy()->subDay()])->with('currency')->get(),
            $currentCurrencyCode
        );
        $revenueMonthDiff = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 
            : ($revenueThisMonth > 0 ? 100 : 0);

        return [
            'orders_today' => $ordersToday,
            'orders_today_diff' => $ordersTodayDiff,
            'revenue_today' => $revenueToday,
            'revenue_today_diff' => $revenueTodayDiff,
            'pending_orders' => $pendingOrders,
            'revenue_month' => $revenueThisMonth,
            'revenue_month_diff' => $revenueMonthDiff,
            'currency_code' => $currentCurrencyCode,
        ];
    }


    public function getTopProductsProperty(): array
    {
        $currentCurrencyCode = session('currency') ?? $this->localeService->getDefaultCurrencyCode();
        $defaultCurrencyCode = $this->localeService->getDefaultCurrencyCode();
        $locale = app()->getLocale();
        $lang = $this->localeService->getLanguageByCode($locale);

        $topProducts = OrderItem::query()
            ->selectRaw('MIN(order_items.id) as id, order_items.product_id, SUM(order_items.qty) as total_qty')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->groupBy('order_items.product_id')
            ->with(['product.productTranslations'])
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($lang, $currentCurrencyCode, $defaultCurrencyCode) {
                $productName = $item->product?->productTranslations
                    ->where('language_id', $lang?->id)
                    ->first()?->name 
                    ?? $item->product?->productTranslations->first()?->name 
                    ?? $item->product?->slug 
                    ?? '';

                $totalRevenue = OrderItem::query()
                    ->with(['order.currency'])
                    ->where('product_id', $item->product_id)
                    ->get()
                    ->sum(function ($orderItem) use ($currentCurrencyCode, $defaultCurrencyCode) {
                        if (!$orderItem->order || !$orderItem->price || !$orderItem->qty) {
                            return 0;
                        }
                        $orderCurrencyCode = $orderItem->order->currency?->code ?? $defaultCurrencyCode;
                        $itemTotal = $orderItem->price * $orderItem->qty;
                        return $this->localeService->convert($itemTotal, $currentCurrencyCode, $orderCurrencyCode);
                    });

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $productName,
                    'total_qty' => $item->total_qty,
                    'total_revenue' => $totalRevenue,
                ];
            })
            ->toArray();

        return $topProducts;
    }

    protected function calculateRevenueInCurrency($orders, string $targetCurrencyCode): float
    {
        if ($orders->isEmpty()) {
            return 0;
        }
        
        $defaultCurrencyCode = $this->localeService->getDefaultCurrencyCode();
        
        return $orders->sum(function ($order) use ($targetCurrencyCode, $defaultCurrencyCode) {
            if (!$order->total || $order->total == 0) {
                return 0;
            }
            $orderCurrencyCode = $order->currency?->code ?? $defaultCurrencyCode;
            return $this->localeService->convert($order->total, $targetCurrencyCode, $orderCurrencyCode);
        });
    }

    public function render()
    {
        return view('livewire.manager.dashboard', [
            'userStats' => $this->userStats,
            'orderStats' => $this->orderStats,
            'topProducts' => $this->topProducts,
        ])->layout('components.layouts.manager');
    }
}
