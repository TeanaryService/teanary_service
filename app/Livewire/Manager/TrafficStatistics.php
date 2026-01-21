<?php

namespace App\Livewire\Manager;

use App\Models\TrafficStatistic;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TrafficStatistics extends Component
{
    use WithPagination;

    public string $dateRange = '7days';
    public string $visitorType = 'all';
    public string $search = '';
    public array $filterSpiderSources = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSpiderSources(): void
    {
        $this->resetPage();
    }

    public function updatingDateRange(): void
    {
        $this->resetPage();
    }

    public function updatingVisitorType(): void
    {
        $this->resetPage();
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

    public function getRecordsProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $query = TrafficStatistic::query()
            ->whereBetween('stat_date', [$startDate, $endDate]);

        // 访客类型（真人 / 机器人 / 全部）
        $query = match ($this->visitorType) {
            'human' => $query->where('is_bot', false),
            'bot' => $query->where('is_bot', true),
            default => $query,
        };

        // 爬虫来源过滤
        if (! empty($this->filterSpiderSources)) {
            $query->whereIn('spider_source', $this->filterSpiderSources);
        }

        // 搜索 path / ip / referer
        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', '%' . $search . '%')
                    ->orWhere('ip', 'like', '%' . $search . '%')
                    ->orWhere('referer', 'like', '%' . $search . '%');
            });
        }

        return $query->orderByDesc('stat_date')->paginate(20);
    }

    public function render()
    {
        return view('livewire.manager.traffic-statistics', [
            'stats' => $this->stats,
            'topPages' => $this->topPages,
            'records' => $this->records,
            'spiderSourceOptions' => [
                'google' => 'Google',
                'bing' => 'Bing',
                'baidu' => 'Baidu',
                'yandex' => 'Yandex',
                'yahoo' => 'Yahoo',
                'duckduckgo' => 'DuckDuckGo',
                'facebook' => 'Facebook',
                'twitter' => 'Twitter',
                'linkedin' => 'LinkedIn',
                'other' => __('manager.TrafficStatisticResource.other'),
                'unknown' => __('manager.TrafficStatisticResource.unknown'),
            ],
        ])->layout('components.layouts.manager');
    }
}
