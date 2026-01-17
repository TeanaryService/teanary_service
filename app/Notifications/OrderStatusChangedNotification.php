<?php

namespace App\Notifications;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
        public OrderStatusEnum $oldStatus,
        public OrderStatusEnum $newStatus
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
            'type' => 'order_status_changed',
            'title' => __('notifications.order_status_changed.title'),
            'message' => __('notifications.order_status_changed.message', [
                'order_no' => $this->order->order_no,
                'old_status' => $this->oldStatus->label(),
                'new_status' => $this->newStatus->label(),
            ]),
            'order_id' => $this->order->id,
            'order_no' => $this->order->order_no,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'created_at' => $this->order->updated_at->toDateTimeString(),
        ];
    }
}
