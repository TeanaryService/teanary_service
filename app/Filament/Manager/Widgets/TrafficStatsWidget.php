<?php

namespace App\Filament\Manager\Widgets;

use App\Models\TrafficStatistic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class TrafficStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public string $dateRange = '7days';

    public string $visitorType = 'all';

    protected function getStats(): array
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

        $stats = [
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

        return [
            Stat::make(__('filament.TrafficStatistics.total_visits'), Number::format($stats['total_visits']))
                ->description(__('filament.TrafficStatistics.human_visits') . ': ' . Number::format($stats['human_visits']) . ' | ' . __('filament.TrafficStatistics.bot_visits') . ': ' . Number::format($stats['bot_visits']))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            
            Stat::make(__('filament.TrafficStatistics.page_views'), Number::format($stats['total_page_views']))
                ->description(__('filament.TrafficStatistics.human_visits') . ': ' . Number::format($stats['human_page_views']) . ' | ' . __('filament.TrafficStatistics.bot_visits') . ': ' . Number::format($stats['bot_page_views']))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),
            
            Stat::make(__('filament.TrafficStatistics.unique_ips'), Number::format($stats['unique_ips']))
                ->description(__('filament.TrafficStatistics.human_visits') . ': ' . Number::format($stats['human_unique_ips']) . ' | ' . __('filament.TrafficStatistics.bot_visits') . ': ' . Number::format($stats['bot_unique_ips']))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
            
            Stat::make(__('filament.TrafficStatistics.unique_pages'), Number::format($stats['unique_pages']))
                ->description(__('filament.TrafficStatistics.pages_visited'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
        ];
    }

    protected function getDateRange(): array
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
}
