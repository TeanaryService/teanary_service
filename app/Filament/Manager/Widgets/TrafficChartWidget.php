<?php

namespace App\Filament\Manager\Widgets;

use App\Models\TrafficStatistic;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TrafficChartWidget extends ChartWidget
{
    public ?string $filter = 'all'; // all, human, bot

    public string $dateRange = '7days';

    protected int|string|array $columnSpan = 'full';

    public function mount(?string $filter = 'all', ?string $dateRange = '7days'): void
    {
        $this->filter = $filter ?? 'all';
        $this->dateRange = $dateRange ?? '7days';
    }

    public function getHeading(): string
    {
        return __('filament.TrafficStatistics.visit_trend');
    }

    public function getDescription(): ?string
    {
        return match ($this->filter) {
            'human' => __('filament.TrafficStatistics.show_human_only'),
            'bot' => __('filament.TrafficStatistics.show_bot_only'),
            default => __('filament.TrafficStatistics.show_all_data'),
        };
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->getDateRange();

        // 获取全部、真人和爬虫的数据
        $allStats = TrafficStatistic::getStatsByDateRange($startDate, $endDate);
        $humanStats = TrafficStatistic::getStatsByDateRange($startDate, $endDate, false);
        $botStats = TrafficStatistic::getStatsByDateRange($startDate, $endDate, true);

        // 创建时间索引
        $statsMap = [];
        foreach ($allStats as $stat) {
            $key = $stat->date . '_' . $stat->hour;
            $statsMap[$key] = [
                'label' => Carbon::parse($stat->date)->format('m/d') . ' ' . str_pad($stat->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'all_visits' => $stat->total_visits,
                'all_page_views' => $stat->page_views,
                'all_ips' => $stat->unique_ips,
            ];
        }

        foreach ($humanStats as $stat) {
            $key = $stat->date . '_' . $stat->hour;
            if (isset($statsMap[$key])) {
                $statsMap[$key]['human_visits'] = $stat->total_visits;
                $statsMap[$key]['human_page_views'] = $stat->page_views;
                $statsMap[$key]['human_ips'] = $stat->unique_ips;
            }
        }

        foreach ($botStats as $stat) {
            $key = $stat->date . '_' . $stat->hour;
            if (isset($statsMap[$key])) {
                $statsMap[$key]['bot_visits'] = $stat->total_visits;
                $statsMap[$key]['bot_page_views'] = $stat->page_views;
                $statsMap[$key]['bot_ips'] = $stat->unique_ips;
            }
        }

        $labels = [];
        $allVisitsData = [];
        $humanVisitsData = [];
        $botVisitsData = [];

        foreach ($statsMap as $stat) {
            $labels[] = $stat['label'];
            $allVisitsData[] = $stat['all_visits'] ?? 0;
            $humanVisitsData[] = $stat['human_visits'] ?? 0;
            $botVisitsData[] = $stat['bot_visits'] ?? 0;
        }

        $datasets = [];

        if ($this->filter === 'all' || $this->filter === 'human') {
            $datasets[] = [
                'label' => __('filament.TrafficStatistics.human_visits_label'),
                'data' => $humanVisitsData,
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderColor' => 'rgb(16, 185, 129)',
                'fill' => true,
                'tension' => 0.4,
            ];
        }

        if ($this->filter === 'all' || $this->filter === 'bot') {
            $datasets[] = [
                'label' => __('filament.TrafficStatistics.bot_visits_label'),
                'data' => $botVisitsData,
                'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                'borderColor' => 'rgb(239, 68, 68)',
                'fill' => true,
                'tension' => 0.4,
            ];
        }

        if ($this->filter === 'all') {
            $datasets[] = [
                'label' => __('filament.TrafficStatistics.total_visits_label'),
                'data' => $allVisitsData,
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'borderColor' => 'rgb(59, 130, 246)',
                'fill' => false,
                'tension' => 0.4,
                'borderDash' => [5, 5],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'all' => __('filament.TrafficStatistics.all'),
            'human' => __('filament.TrafficStatistics.human'),
            'bot' => __('filament.TrafficStatistics.bot'),
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
