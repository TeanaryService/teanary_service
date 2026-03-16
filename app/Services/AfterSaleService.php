<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\AfterSale;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AfterSaleService
{
    /**
     * 创建售后单（后台或前台均可调用）.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AfterSale
    {
        return DB::transaction(function () use ($data) {
            /** @var Order $order */
            $order = Order::query()->findOrFail($data['order_id']);

            $orderItemId = $data['order_item_id'] ?? null;
            $quantity = (int) ($data['quantity'] ?? 1);

            if ($orderItemId) {
                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::query()->where('order_id', $order->id)->findOrFail($orderItemId);

                if ($quantity < 1) {
                    throw ValidationException::withMessages([
                        'quantity' => '售后数量必须大于 0。',
                    ]);
                }

                // 计算该订单行已申请的有效售后数量（排除已取消和已拒绝）
                $usedQty = AfterSale::query()
                    ->where('order_id', $order->id)
                    ->where('order_item_id', $orderItem->id)
                    ->whereNotIn('status', [AfterSale::STATUS_CANCELED, AfterSale::STATUS_REJECTED])
                    ->sum('quantity');

                if ($quantity + $usedQty > $orderItem->qty) {
                    throw ValidationException::withMessages([
                        'quantity' => '售后数量不能超过该商品的购买数量（已含之前的售后申请）。',
                    ]);
                }

                $data['product_id'] = $data['product_id'] ?? $orderItem->product_id;
            }

            $data['status'] = AfterSale::STATUS_PENDING;

            $afterSale = AfterSale::query()->create($data);

            // 订单进入“售后处理中”状态
            if ($order && ! $order->status->isAfterSaleProcessing() && ! $order->status->isAfterSaleCompleted()) {
                $order->update(['status' => OrderStatusEnum::AfterSale]);
            }

            return $afterSale;
        });
    }

    /**
     * 审核售后单（通过/拒绝）.
     */
    public function review(AfterSale $afterSale, bool $approved, ?string $remarks = null): AfterSale
    {
        if ($afterSale->status !== AfterSale::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => '只有待审核的售后单可以审核。',
            ]);
        }

        $afterSale->status = $approved ? AfterSale::STATUS_APPROVED : AfterSale::STATUS_REJECTED;
        if ($remarks !== null) {
            $afterSale->remarks = trim((string) $afterSale->remarks."\n".$remarks);
        }
        $afterSale->processed_at = now();
        $afterSale->save();

        // 审核通过时，订单标记为“售后处理中”
        if ($approved && $afterSale->order && ! $afterSale->order->status->isAfterSaleProcessing()) {
            $afterSale->order->update(['status' => OrderStatusEnum::AfterSale]);
        }

        return $afterSale;
    }

    /**
     * 更新退货物流信息，并将状态置为“退货中”.
     */
    public function updateReturnLogistics(AfterSale $afterSale, array $data): AfterSale
    {
        if (! in_array($afterSale->type, [AfterSale::TYPE_REFUND_AND_RETURN, AfterSale::TYPE_EXCHANGE], true)) {
            throw ValidationException::withMessages([
                'type' => '仅退货退款或换货的售后类型才需要填写退货物流信息。',
            ]);
        }

        $afterSale->fill([
            'logistics_company' => $data['logistics_company'] ?? $afterSale->logistics_company,
            'tracking_number' => $data['tracking_number'] ?? $afterSale->tracking_number,
        ]);

        if ($afterSale->status === AfterSale::STATUS_APPROVED) {
            $afterSale->status = AfterSale::STATUS_IN_RETURN;
        }

        $afterSale->save();

        return $afterSale;
    }

    /**
     * 完成售后单：仅退款 / 退货退款 / 换货.
     *
     * 实际退款、发货逻辑可在此方法中对接到支付和发货服务，这里先预留扩展点。
     */
    public function complete(AfterSale $afterSale, ?string $remarks = null): AfterSale
    {
        if (! in_array($afterSale->status, [AfterSale::STATUS_APPROVED, AfterSale::STATUS_IN_RETURN], true)) {
            throw ValidationException::withMessages([
                'status' => '只有已审核或退货中的售后单可以完成。',
            ]);
        }

        // TODO: 在这里对接实际退款逻辑（支付网关）与换货发货逻辑（创建发货/子订单）

        if ($remarks !== null) {
            $afterSale->remarks = trim((string) $afterSale->remarks."\n".$remarks);
        }

        $afterSale->status = AfterSale::STATUS_COMPLETED;
        $afterSale->processed_at = now();
        $afterSale->save();

        // 售后完成时，订单标记为“售后完成”
        if ($afterSale->order && ! $afterSale->order->status->isAfterSaleCompleted()) {
            $afterSale->order->update(['status' => OrderStatusEnum::AfterSaleCompleted]);
        }

        return $afterSale;
    }

    /**
     * 取消售后单.
     */
    public function cancel(AfterSale $afterSale, ?string $remarks = null): AfterSale
    {
        if (! in_array($afterSale->status, [AfterSale::STATUS_PENDING, AfterSale::STATUS_APPROVED, AfterSale::STATUS_IN_RETURN], true)) {
            throw ValidationException::withMessages([
                'status' => '仅待审核、已审核或退货中的售后单可以取消。',
            ]);
        }

        if ($remarks !== null) {
            $afterSale->remarks = trim((string) $afterSale->remarks."\n".$remarks);
        }

        $afterSale->status = AfterSale::STATUS_CANCELED;
        $afterSale->processed_at = now();
        $afterSale->save();

        // 如果订单当前处于售后处理中，且该售后被取消，则根据实际业务可选择恢复为已完成
        if ($afterSale->order && $afterSale->order->status->isAfterSaleProcessing()) {
            $afterSale->order->update(['status' => OrderStatusEnum::Completed]);
        }

        return $afterSale;
    }
}
