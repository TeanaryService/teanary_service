<?php

namespace App\Filament\Manager\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class OrderStatsWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getStats(): array
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

        // 今日销售额（转换为当前货币）- 统计所有订单
        $service = app(LocaleCurrencyService::class);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();
        $revenueToday = $this->calculateRevenueInCurrency(
            Order::whereDate('created_at', $today)
                ->with('currency')
                ->get(),
            $service,
            $currentCurrencyCode
        );
        $revenueYesterday = $this->calculateRevenueInCurrency(
            Order::whereDate('created_at', $yesterday)
                ->with('currency')
                ->get(),
            $service,
            $currentCurrencyCode
        );
        $revenueTodayDiff = $revenueYesterday > 0 
            ? (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100 
            : ($revenueToday > 0 ? 100 : 0);

        // 待处理订单
        $pendingOrders = Order::whereIn('status', [OrderStatusEnum::Pending, OrderStatusEnum::Paid])
            ->count();

        // 本月销售额（转换为当前货币）- 统计所有订单
        $revenueThisMonth = $this->calculateRevenueInCurrency(
            Order::where('created_at', '>=', $thisMonth)
                ->with('currency')
                ->get(),
            $service,
            $currentCurrencyCode
        );
        $revenueLastMonth = $this->calculateRevenueInCurrency(
            Order::whereBetween('created_at', [
                $lastMonth,
                $thisMonth->copy()->subDay()
            ])
                ->with('currency')
                ->get(),
            $service,
            $currentCurrencyCode
        );
        $revenueMonthDiff = $revenueLastMonth > 0 
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100 
            : ($revenueThisMonth > 0 ? 100 : 0);

        return [
            Stat::make(__('filament.dashboard.stats.orders_today'), $ordersToday)
                ->description(__('filament.dashboard.stats.orders_today_desc', [
                    'change' => Number::percentage(abs($ordersTodayDiff), 1),
                ]))
                ->descriptionIcon($ordersTodayDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersTodayDiff >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChartData()),
            
            Stat::make(__('filament.dashboard.stats.revenue_today'), $service->convertWithSymbol($revenueToday, $currentCurrencyCode))
                ->description(__('filament.dashboard.stats.revenue_today_desc', [
                    'change' => Number::percentage(abs($revenueTodayDiff), 1),
                ]))
                ->descriptionIcon($revenueTodayDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTodayDiff >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChartData()),
            
            Stat::make(__('filament.dashboard.stats.pending_orders'), $pendingOrders)
                ->description(__('filament.dashboard.stats.pending_orders_desc'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(\App\Filament\Manager\Resources\OrderResource::getUrl('index')),
            
            Stat::make(__('filament.dashboard.stats.revenue_month'), $service->convertWithSymbol($revenueThisMonth, $currentCurrencyCode))
                ->description(__('filament.dashboard.stats.revenue_month_desc', [
                    'change' => Number::percentage(abs($revenueMonthDiff), 1),
                ]))
                ->descriptionIcon($revenueMonthDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueMonthDiff >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyRevenueChartData()),
        ];
    }

    protected function getOrdersChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = Order::whereDate('created_at', $date)->count();
        }
        return $data;
    }

    protected function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = Order::whereDate('created_at', $date)
                ->whereIn('status', [OrderStatusEnum::Paid, OrderStatusEnum::Shipped, OrderStatusEnum::Completed])
                ->sum('total');
        }
        return $data;
    }

    protected function getMonthlyRevenueChartData(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            $data[] = Order::whereBetween('created_at', [$start, $end])
                ->whereIn('status', [OrderStatusEnum::Paid, OrderStatusEnum::Shipped, OrderStatusEnum::Completed])
                ->sum('total');
        }
        return $data;
    }

    protected function calculateRevenueInCurrency($orders, LocaleCurrencyService $service, string $targetCurrencyCode): float
    {
        if ($orders->isEmpty()) {
            return 0;
        }
        
        $defaultCurrencyCode = $service->getDefaultCurrencyCode();
        
        return $orders->sum(function ($order) use ($service, $targetCurrencyCode, $defaultCurrencyCode) {
            if (!$order->total || $order->total == 0) {
                return 0;
            }
            
            // 如果订单没有货币，使用默认货币
            $orderCurrencyCode = $order->currency?->code ?? $defaultCurrencyCode;
            
            // convert(amount, toCode, fromCode)
            return $service->convert($order->total, $targetCurrencyCode, $orderCurrencyCode);
        });
    }
}
