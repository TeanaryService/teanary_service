<?php

namespace App\Livewire\User;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load([
            'orderItems.product.productTranslations',
            'orderItems.productVariant',
            'shippingAddress',
            'billingAddress',
            'currency'
        ]);
    }

    public function render(): View
    {
        return view('livewire.user.order-detail');
    }
}
