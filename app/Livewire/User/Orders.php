<?php

namespace App\Livewire\User;

use App\Enums\OrderStatusEnum;
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

        $localeService = app(\App\Services\LocaleCurrencyService::class);
        $lang = $localeService->getLanguageByCode(session('lang'));

        return view('livewire.user.orders', [
            'orders' => $orders,
            'localeService' => $localeService,
            'lang' => $lang,
        ]);
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        if ($order->status->canBeCancelled()) {
            $order->update(['status' => OrderStatusEnum::Cancelled]);
            $this->dispatch('notify', [
                'message' => __('orders.operation_success'),
                'type' => 'success',
            ]);
        }
    }
}
