<?php

namespace App\Filament\Manager\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrderStatusChartWidget extends ChartWidget
{
    protected static ?int $sort = 55;

    public function getHeading(): string
    {
        return __('filament.dashboard.charts.order_status');
    }

    protected function getData(): array
    {
        $statuses = OrderStatusEnum::cases();
        $labels = [];
        $data = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // 待支付 - 蓝色
            'rgba(16, 185, 129, 0.8)',  // 已支付 - 绿色
            'rgba(245, 158, 11, 0.8)',  // 已发货 - 黄色
            'rgba(34, 197, 94, 0.8)',   // 已完成 - 深绿色
            'rgba(239, 68, 68, 0.8)',   // 已取消 - 红色
            'rgba(139, 92, 246, 0.8)',  // 售后处理中 - 紫色
            'rgba(107, 114, 128, 0.8)', // 售后完成 - 灰色
        ];

        foreach ($statuses as $index => $status) {
            $labels[] = $status->label();
            $data[] = Order::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('filament.dashboard.charts.order_count'),
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($statuses)),
                    'borderColor' => array_slice($colors, 0, count($statuses)),
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' ' + '" . __('filament.dashboard.charts.orders') . "';
                            return label;
                        }",
                    ],
                ],
            ],
        ];
    }
}
