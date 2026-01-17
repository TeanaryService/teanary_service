<?php

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Models\Manager;
use App\Models\Order;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;

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
        // 通知用户订单已创建
        if ($order->user) {
            $order->user->notify(new OrderCreatedNotification($order));
        }

        // 通知所有管理员有新订单
        Manager::chunk(100, function ($managers) use ($order) {
            foreach ($managers as $manager) {
                $manager->notify(new OrderCreatedNotification($order));
            }
        });
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // 检查订单状态是否发生变化
        if ($order->wasChanged('status')) {
            $oldStatus = OrderStatusEnum::from($order->getOriginal('status'));
            $newStatus = $order->status;

            // 通知用户订单状态变更
            if ($order->user) {
                $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus, $newStatus));
            }

            // 通知所有管理员订单状态变更
            Manager::chunk(100, function ($managers) use ($order, $oldStatus, $newStatus) {
                foreach ($managers as $manager) {
                    $manager->notify(new OrderStatusChangedNotification($order, $oldStatus, $newStatus));
                }
            });

            // 如果订单被取消，发送取消通知
            if ($newStatus === OrderStatusEnum::Cancelled) {
                if ($order->user) {
                    $order->user->notify(new OrderCancelledNotification($order));
                }

                Manager::chunk(100, function ($managers) use ($order) {
                    foreach ($managers as $manager) {
                        $manager->notify(new OrderCancelledNotification($order));
                    }
                });
            }
        }
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
