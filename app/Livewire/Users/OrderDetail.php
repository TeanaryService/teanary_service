<?php

namespace App\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetail extends Component
{
    use HasNavigationRedirect;
    use UsesLocaleCurrency;

    public ?Order $order = null;

    public function mount($orderId = null)
    {
        $userId = Auth::id();
        if (! $userId) {
            abort(403, 'Unauthorized');
        }

        if (! $orderId) {
            abort(404, 'Order not found');
        }

        // 如果 $orderId 已经是 Order 模型实例，直接使用；否则通过 ID 查找
        if ($orderId instanceof Order) {
            $id = $orderId->id;
        } else {
            // Snowflake ID 可能是字符串或整数
            $id = $orderId;
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
            ->findOrFail($id);
    }

    public function cancelOrder(): void
    {
        if ($this->order->status->canBeCancelled()) {
            $this->order->update(['status' => OrderStatusEnum::Cancelled]);
            $this->flashMessage('operation_success');
            $this->order->refresh();
        } else {
            $this->dispatch('flash-message', type: 'error', message: __('orders.cannot_cancel'));
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
