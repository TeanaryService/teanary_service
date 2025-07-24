<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Password;

class ForgotPassword extends Component
{
    public $email = '';
    public $success = false;

    public function sendResetLink()
    {
        $this->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            ['email' => $this->email],
            function ($user, $token) {
                // 返回自定义重置密码链接
                // 获取当前语言
                $locale = app()->getLocale();
                return url("{$locale}/reset-password/{$token}");
            }
        );
        
        if ($status === Password::RESET_LINK_SENT) {
            $this->success = true;
        } else {
            $message = __("passwords.{$status}");
            $this->addError('email', $message);
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
