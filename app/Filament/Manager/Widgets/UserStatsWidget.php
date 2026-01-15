<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class UserStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
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

        // 平均订单价值（转换为当前货币）- 统计所有订单
        $service = app(LocaleCurrencyService::class);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();
        $defaultCurrencyCode = $service->getDefaultCurrencyCode();
        $orders = Order::with('currency')->get();
        
        $totalConverted = $orders->sum(function ($order) use ($service, $currentCurrencyCode, $defaultCurrencyCode) {
            if (!$order->total || $order->total == 0) {
                return 0;
            }
            // 如果订单没有货币，使用默认货币
            $orderCurrencyCode = $order->currency?->code ?? $defaultCurrencyCode;
            return $service->convert($order->total, $currentCurrencyCode, $orderCurrencyCode);
        });
        $avgOrderValue = $orders->count() > 0 ? $totalConverted / $orders->count() : 0;

        return [
            Stat::make(__('filament.dashboard.stats.new_users_today'), $usersToday)
                ->description(__('filament.dashboard.stats.new_users_today_desc', [
                    'change' => Number::percentage(abs($usersTodayDiff), 1),
                ]))
                ->descriptionIcon($usersTodayDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($usersTodayDiff >= 0 ? 'success' : 'danger')
                ->url(\App\Filament\Manager\Resources\UserResource::getUrl('index')),
            
            Stat::make(__('filament.dashboard.stats.total_users'), Number::format($totalUsers))
                ->description(__('filament.dashboard.stats.total_users_desc', [
                    'change' => Number::percentage(abs($totalUsersDiff), 1),
                ]))
                ->descriptionIcon($totalUsersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary')
                ->url(\App\Filament\Manager\Resources\UserResource::getUrl('index')),
            
            Stat::make(__('filament.dashboard.stats.active_users'), Number::format($activeUsers))
                ->description(__('filament.dashboard.stats.active_users_desc', [
                    'change' => Number::percentage(abs($activeUsersDiff), 1),
                ]))
                ->descriptionIcon($activeUsersDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('info'),
            
            Stat::make(__('filament.dashboard.stats.avg_order_value'), $service->convertWithSymbol($avgOrderValue, $currentCurrencyCode))
                ->description(__('filament.dashboard.stats.avg_order_value_desc'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
