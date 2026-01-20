<?php

namespace App\Livewire\Manager;

use App\Models\TrafficStatistic;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Livewire\Component;

class TrafficStatistics extends Component
{
    public string $dateRange = '7days';
    public string $visitorType = 'all';

    public function getDateRangeArray(): array
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
        [$startDate, $endDate] = $this->getDateRangeArray();

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
        [$startDate, $endDate] = $this->getDateRangeArray();

        $isBot = match ($this->visitorType) {
            'human' => false,
            'bot' => true,
            default => null,
        };

        return TrafficStatistic::getTopPages($startDate, $endDate, 20, $isBot)->toArray();
    }


    public function render()
    {
        return view('livewire.manager.traffic-statistics', [
            'stats' => $this->stats,
            'topPages' => $this->topPages,
        ])->layout('components.layouts.manager');
    }
}
