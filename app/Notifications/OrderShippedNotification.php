<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public OrderShipment $shipment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_shipped',
            'title' => __('notifications.order_shipped.title'),
            'message' => __('notifications.order_shipped.message', [
                'order_no' => $this->order->order_no,
                'tracking_number' => $this->shipment->tracking_number ?? __('app.not_available'),
                'shipping_method' => $this->shipment->shipping_method->label(),
            ]),
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'tracking_number' => $this->shipment->tracking_number,
            'shipping_method' => $this->shipment->shipping_method->value,
            'created_at' => $this->shipment->created_at->toDateTimeString(),
        ];
    }
}
