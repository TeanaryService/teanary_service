<?php

namespace App\Livewire\User;

use App\Enums\OrderStatusEnum;
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
            'orderShipments',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
            'shippingAddress.country.countryTranslations',
            'shippingAddress.zone.zoneTranslations',
            'billingAddress',
            'currency',
        ]);
    }

    public function cancelOrder(): void
    {
        if ($this->order->status->canBeCancelled()) {
            $this->order->update(['status' => OrderStatusEnum::Cancelled]);
            $this->dispatch('notify', [
                'message' => __('orders.operation_success'),
                'type' => 'success'
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.user.order-detail');
    }
}
