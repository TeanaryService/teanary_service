<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Order;
use App\Models\CartItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DailyStatsWidget extends Widget
{
    protected static string $view = 'filament.manager.widgets.daily-stats-widget';
    protected static ?int $sort = 200;

    protected static ?string $pollingInterval = null;

    public $stats = [];

    public function mount()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $this->stats = [
            'users_today' => User::whereDate('created_at', $today)->count(),
            'users_yesterday' => User::whereDate('created_at', $yesterday)->count(),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'orders_yesterday' => Order::whereDate('created_at', $yesterday)->count(),
            'cart_items_today' => CartItem::whereDate('created_at', $today)->count(),
            'cart_items_yesterday' => CartItem::whereDate('created_at', $yesterday)->count(),
        ];
    }

    /**
     * @return int | string | array<string, int | null>
     */
    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}
