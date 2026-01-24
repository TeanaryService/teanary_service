<?php

namespace App\Livewire\Users;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public $email = '';
    public $status = '';

    protected $rules = [
        'email' => 'required|email',
    ];

    protected $messages = [
        'email.required' => '请输入邮箱地址',
        'email.email' => '请输入有效的邮箱地址',
    ];

    public function sendResetLink()
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __('passwords.sent');
        } else {
            $this->addError('email', __('passwords.user'));
        }
    }

    public function render()
    {
        return view('livewire.users.forgot-password')->layout('components.layouts.app');
    }
}
