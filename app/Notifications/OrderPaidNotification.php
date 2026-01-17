<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order
    ) {
    }

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
            'type' => 'order_paid',
            'title' => __('notifications.order_paid.title'),
            'message' => __('notifications.order_paid.message', [
                'order_no' => $this->order->order_no,
                'total' => number_format($this->order->total, 2),
            ]),
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'created_at' => $this->order->updated_at->toDateTimeString(),
        ];
    }
}
