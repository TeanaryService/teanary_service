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

    protected function messages(): array
    {
        return [
            'email.required' => __('validation.custom.email.required'),
            'email.email' => __('validation.custom.email.email'),
        ];
    }

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
