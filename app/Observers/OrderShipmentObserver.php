<?php

namespace App\Observers;

use App\Models\Manager;
use App\Models\OrderShipment;
use App\Notifications\OrderShippedNotification;

class OrderShipmentObserver
{
    /**
     * Handle the OrderShipment "created" event.
     */
    public function created(OrderShipment $orderShipment): void
    {
        $order = $orderShipment->order;
        
        // 通知用户订单已发货
        if ($order && $order->user) {
            $order->user->notify(new OrderShippedNotification($order, $orderShipment));
        }

        // 通知所有管理员订单已发货
        Manager::chunk(100, function ($managers) use ($order, $orderShipment) {
            foreach ($managers as $manager) {
                $manager->notify(new OrderShippedNotification($order, $orderShipment));
            }
        });
    }

    /**
     * Handle the OrderShipment "updated" event.
     */
    public function updated(OrderShipment $orderShipment): void
    {
        //
    }

    /**
     * Handle the OrderShipment "deleted" event.
     */
    public function deleted(OrderShipment $orderShipment): void
    {
        //
    }

    /**
     * Handle the OrderShipment "restored" event.
     */
    public function restored(OrderShipment $orderShipment): void
    {
        //
    }

    /**
     * Handle the OrderShipment "force deleted" event.
     */
    public function forceDeleted(OrderShipment $orderShipment): void
    {
        //
    }
}
