<?php

namespace App\Filament\Manager\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return __('filament.dashboard.title');
    }

    public function getHeading(): string
    {
        return __('filament.dashboard.heading');
    }

    public function getWidgets(): array
    {
        // 明确指定要显示的 widget，排除流量统计相关的 widget
        return [
            \App\Filament\Manager\Widgets\UserStatsWidget::class,
            \App\Filament\Manager\Widgets\OrderStatsWidget::class,
            \App\Filament\Manager\Widgets\SalesChartWidget::class,
            \App\Filament\Manager\Widgets\OrderStatusChartWidget::class,
            \App\Filament\Manager\Widgets\TopProductsWidget::class,
        ];
    }
}
