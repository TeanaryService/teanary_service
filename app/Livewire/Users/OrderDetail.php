<?php

namespace App\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetail extends Component
{
    public ?Order $order = null;

    protected LocaleCurrencyService $localeService;

    public function mount($order)
    {
        $this->localeService = app(LocaleCurrencyService::class);

        $this->order = Order::query()
            ->where('user_id', Auth::id())
            ->with([
                'orderItems.product.productTranslations',
                'orderItems.product.media',
                'orderShipments',
                'orderItems.productVariant.specificationValues.specificationValueTranslations',
                'orderItems.productVariant.media',
                'shippingAddress.country.countryTranslations',
                'shippingAddress.zone.zoneTranslations',
                'billingAddress',
                'currency',
            ])
            ->findOrFail($order);
    }

    public function cancelOrder(): void
    {
        if ($this->order->status->canBeCancelled()) {
            $this->order->update(['status' => OrderStatusEnum::Cancelled]);
            session()->flash('message', __('orders.operation_success'));
            $this->order->refresh();
        } else {
            session()->flash('error', __('orders.cannot_cancel'));
        }
    }

    public function payOrder()
    {
        if ($this->order->status->canBePaid()) {
            return $this->redirect(route('payment.checkout', ['orderId' => $this->order->id]));
        }
    }

    public function render()
    {
        return view('livewire.users.order-detail')->layout('components.layouts.app');
    }
}
