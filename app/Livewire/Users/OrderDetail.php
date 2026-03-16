<?php

namespace App\Livewire\Users;

use App\Enums\OrderStatusEnum;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\AfterSale;
use App\Models\Order;
use App\Services\AfterSaleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetail extends Component
{
    use HasNavigationRedirect;
    use UsesLocaleCurrency;

    public ?Order $order = null;

    // 售后申请表单
    public string $afterSaleType = AfterSale::TYPE_REFUND_ONLY;
    public ?string $afterSaleReason = null;
    public ?string $afterSaleDescription = null;
    public int $afterSaleQuantity = 1;
    public ?int $afterSaleTargetItemId = null;
    public ?string $afterSaleTargetMode = null; // item | order
    public bool $showAfterSaleDialog = false;

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
                'warehouse',
                'afterSales',
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

    /**
     * 打开售后申请弹窗.
     */
    public function openAfterSaleDialog(string $mode, ?int $orderItemId = null): void
    {
        if (! $this->order || $this->order->id === null) {
            abort(404, 'Order not found');
        }

        if (! $this->order->status->canRequestAfterSale()) {
            $this->dispatch('flash-message', type: 'error', message: '当前订单状态不支持售后申请');

            return;
        }

        $this->afterSaleTargetMode = $mode;
        $this->afterSaleTargetItemId = $orderItemId;
        $this->showAfterSaleDialog = true;
    }

    /**
     * 实际提交售后（从弹窗确认）。
     */
    public function confirmAfterSale(): void
    {
        if (! $this->order || $this->order->id === null) {
            abort(404, 'Order not found');
        }

        if (! $this->order->status->canRequestAfterSale()) {
            $this->dispatch('flash-message', type: 'error', message: '当前订单状态不支持售后申请');

            return;
        }

        if (! $this->afterSaleReason) {
            $this->dispatch('flash-message', type: 'error', message: '请先填写售后理由');

            return;
        }

        if ($this->afterSaleTargetMode === 'item' && $this->afterSaleTargetItemId) {
            $this->submitAfterSaleInternal($this->afterSaleTargetItemId);
        } else {
            $this->submitAfterSaleForOrderInternal();
        }

        $this->showAfterSaleDialog = false;
        $this->afterSaleTargetMode = null;
        $this->afterSaleTargetItemId = null;
    }

    /**
     * 提交单个商品的售后申请（按订单行）。
     */
    protected function submitAfterSaleInternal(int $orderItemId): void
    {
        if (! $this->order || $this->order->id === null) {
            abort(404, 'Order not found');
        }

        $service = app(AfterSaleService::class);

        $service->create([
            'order_id' => $this->order->id,
            'order_item_id' => $orderItemId,
            'user_id' => Auth::id(),
            'warehouse_id' => $this->order->warehouse_id,
            'type' => $this->afterSaleType ?: AfterSale::TYPE_REFUND_ONLY,
            'reason' => $this->afterSaleReason,
            'description' => $this->afterSaleDescription,
            'quantity' => $this->afterSaleQuantity > 0 ? $this->afterSaleQuantity : 1,
        ]);

        $this->afterSaleType = AfterSale::TYPE_REFUND_ONLY;
        $this->afterSaleReason = null;
        $this->afterSaleDescription = null;
        $this->afterSaleQuantity = 1;

        $this->dispatch('flash-message', type: 'success', message: '售后申请已提交，等待客服审核');
    }

    /**
     * 提交整单售后申请（不指定具体订单行）。
     */
    protected function submitAfterSaleForOrderInternal(): void
    {
        $service = app(AfterSaleService::class);

        $service->create([
            'order_id' => $this->order->id,
            'order_item_id' => null,
            'user_id' => Auth::id(),
            'warehouse_id' => $this->order->warehouse_id,
            'type' => $this->afterSaleType ?: AfterSale::TYPE_REFUND_ONLY,
            'reason' => $this->afterSaleReason,
            'description' => $this->afterSaleDescription,
            // 整单申请不强制数量，可根据后续需要扩展为总件数
            'quantity' => 1,
        ]);

        $this->afterSaleType = AfterSale::TYPE_REFUND_ONLY;
        $this->afterSaleReason = null;
        $this->afterSaleDescription = null;
        $this->afterSaleQuantity = 1;

        $this->dispatch('flash-message', type: 'success', message: '整单售后申请已提交，等待客服审核');
    }
}
