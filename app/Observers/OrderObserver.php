<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        //
        $order->order_no = strtolower(uniqid(prefix: 'ORD-'));
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleting" event.
     *
     * 级联删除所有关联数据（替代数据库外键约束）
     */
    public function deleting(Order $order): void
    {
        // 删除订单项
        $order->orderItems()->each(function ($item) {
            $item->delete();
        });

        // 删除订单发货记录
        $order->orderShipments()->each(function ($shipment) {
            $shipment->delete();
        });
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }
}
