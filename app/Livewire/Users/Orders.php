<?php

namespace App\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

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
            session()->flash('message', __('orders.operation_success'));
            $this->resetPage();
        } else {
            session()->flash('error', __('orders.cannot_cancel'));
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
