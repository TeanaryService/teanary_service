<?php

namespace App\Livewire\User;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public function render(): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->with(['orderItems.product', 'orderItems.productVariant'])
            ->latest()
            ->paginate(10);

        return view('livewire.user.orders', compact('orders'));
    }
}
