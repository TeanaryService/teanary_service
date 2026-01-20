<?php

namespace App\Livewire\Manager;

use App\Models\User;
use App\Models\Order;
use Livewire\Component;

class Home extends Component
{
    public $totalUsers;
    public $totalOrders;
    public $totalRevenue;
    public $topProducts;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->totalUsers = User::count();
        $this->totalOrders = Order::count();
        $this->totalRevenue = Order::sum('total');
        $this->topProducts = Order::query()
            ->with('orderItems.product')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.manager.home')->layout('components.layouts.manager');
    }
}
