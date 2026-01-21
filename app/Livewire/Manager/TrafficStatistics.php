<?php

namespace App\Livewire\Manager;

use App\Models\TrafficStatistic;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TrafficStatistics extends Component
{
    public string $dateRange = '7days';
    public string $visitorType = 'all';

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

    public function getStatsProperty(): array
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

    public function getTopPagesProperty(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        $isBot = match ($this->visitorType) {
            'human' => false,
            'bot' => true,
            default => null,
        };

        return TrafficStatistic::getTopPages($startDate, $endDate, 20, $isBot)->toArray();
    }

    public function updatedDateRange(): void
    {
        // 当日期范围改变时，Livewire 会自动重新渲染
    }

    public function updatedVisitorType(): void
    {
        // 当访问者类型改变时，Livewire 会自动重新渲染
    }

    public function render()
    {
        return view('livewire.manager.traffic-statistics', [
            'stats' => $this->stats,
            'topPages' => $this->topPages,
        ])->layout('components.layouts.manager');
    }
}
