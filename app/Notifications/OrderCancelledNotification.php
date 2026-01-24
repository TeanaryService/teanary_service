<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public ?string $reason = null
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
            'type' => 'order_cancelled',
            'title' => __('notifications.order_cancelled.title'),
            'message' => __('notifications.order_cancelled.message', [
                'order_no' => $this->order->order_no,
                'reason' => $this->reason ?? __('app.no_reason_provided'),
            ]),
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'reason' => $this->reason,
            'created_at' => $this->order->updated_at->toDateTimeString(),
        ];
    }
}
