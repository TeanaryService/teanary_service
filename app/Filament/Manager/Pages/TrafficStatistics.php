<?php

namespace App\Filament\Manager\Pages;

use App\Models\TrafficStatistic;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class TrafficStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 50;

    protected static string $view = 'filament.manager.pages.traffic-statistics';

    protected static ?string $slug = 'traffic-statistics';

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    public string $dateRange = '7days';

    public string $visitorType = 'all';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Manager\Widgets\TrafficStatsWidget::class,
        ];
    }

    public function getDateRange(): array
    {
        return match ($this->dateRange) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            '7days' => [Carbon::today()->subDays(6), Carbon::today()->endOfDay()],
            '30days' => [Carbon::today()->subDays(29), Carbon::today()->endOfDay()],
            '90days' => [Carbon::today()->subDays(89), Carbon::today()->endOfDay()],
            default => [Carbon::today()->subDays(6), Carbon::today()->endOfDay()],
        };
    }

    public function getStats(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $baseQuery = TrafficStatistic::whereBetween('stat_date', [$startDate, $endDate]);
        
        $query = match ($this->visitorType) {
            'human' => (clone $baseQuery)->where('is_bot', false),
            'bot' => (clone $baseQuery)->where('is_bot', true),
            default => clone $baseQuery,
        };

        $humanQuery = (clone $baseQuery)->where('is_bot', false);
        $botQuery = (clone $baseQuery)->where('is_bot', true);

        return [
            'total_visits' => $query->sum('count'),
            'total_page_views' => $query->count(),
            'unique_ips' => $query->distinct('ip')->count('ip'),
            'unique_pages' => $query->distinct('path')->count('path'),
            'human_visits' => $humanQuery->sum('count'),
            'bot_visits' => $botQuery->sum('count'),
            'human_page_views' => $humanQuery->count(),
            'bot_page_views' => $botQuery->count(),
            'human_unique_ips' => $humanQuery->distinct('ip')->count('ip'),
            'bot_unique_ips' => $botQuery->distinct('ip')->count('ip'),
        ];
    }

    public function getTopPages(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $isBot = match ($this->visitorType) {
            'human' => false,
            'bot' => true,
            default => null,
        };

        return TrafficStatistic::getTopPages($startDate, $endDate, 20, $isBot)->toArray();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.TrafficStatistics.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.TrafficStatistics.navigation_label');
    }
}
