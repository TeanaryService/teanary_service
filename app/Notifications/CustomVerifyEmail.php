<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $locale = app()->getLocale(); // 获取当前语言

        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify', // 确保这个路由支持 {locale}
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
                'locale' => $locale, // 注入 locale 参数
            ]
        );

        return $temporarySignedURL;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('app.email_verify_subject'))
            ->line(__('app.email_verify_line1'))
            ->action(__('app.email_verify_action'), $this->verificationUrl($notifiable))
            ->line(__('app.email_verify_line2'));
    }
}
