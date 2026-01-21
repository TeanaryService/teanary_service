<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderQueryVerificationCode extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $verificationCode,
        public string $orderNo
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('orders.query_verification_subject'))
            ->line(__('orders.query_verification_line1'))
            ->line(__('orders.query_verification_line2', ['order_no' => $this->orderNo]))
            ->line(__('orders.query_verification_code_line', ['code' => $this->verificationCode]))
            ->line(__('orders.query_verification_line3'))
            ->line(__('orders.query_verification_line4'));
    }
}
