<?php

namespace App\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use HasNavigationRedirect;
    use UsesLocaleCurrency;
    use WithPagination;

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->with([
                'orderItems.product.productTranslations',
                'orderItems.productVariant.specificationValues.specificationValueTranslations',
                'orderItems.productVariant.media',
                'orderItems.product.media',
                'currency',
                'warehouse',
            ])
            ->latest()
            ->paginate(10);
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        if ($order->status->canBeCancelled()) {
            $order->update(['status' => OrderStatusEnum::Cancelled]);
            $this->flashMessage('operation_success');
            $this->resetPage();
        } else {
            $this->dispatch('flash-message', type: 'error', message: __('orders.cannot_cancel'));
        }
    }

    public function payOrder(int $orderId)
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        if ($order->status->canBePaid()) {
            return $this->redirect(locaRoute('payment.checkout', ['orderId' => $order->id]));
        }
    }

    public function render()
    {
        return view('livewire.users.orders', [
            'orders' => $this->orders,
        ])->layout('components.layouts.app');
    }
}
