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

        $userId = Auth::id();
        if (! $userId) {
            abort(403, 'Unauthorized');
        }

        // 如果 $order 已经是 Order 模型实例，直接使用；否则通过 ID 查找
        if ($order instanceof Order) {
            $orderId = $order->id;
        } else {
            // Snowflake ID 可能是字符串或整数
            $orderId = $order;
        }

        $this->order = Order::query()
            ->where('user_id', $userId)
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
            ->findOrFail($orderId);
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
            return $this->redirect(locaRoute('payment.checkout', ['orderId' => $this->order->id]));
        }
    }

    public function render()
    {
        return view('livewire.users.order-detail')->layout('components.layouts.app');
    }
}
