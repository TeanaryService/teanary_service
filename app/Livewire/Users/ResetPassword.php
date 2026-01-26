<?php

namespace App\Livewire\Users;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Livewire\Component;

class ResetPassword extends Component
{
    public $token;
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected function messages(): array
    {
        return [
            'email.required' => __('validation.custom.email.required'),
            'email.email' => __('validation.custom.email.email'),
            'password.required' => __('validation.custom.password.required'),
            'password.min' => __('validation.custom.password.min'),
            'password.confirmed' => __('validation.custom.password.confirmed'),
        ];
    }

    public function resetPassword()
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->dispatch('flash-message', type: 'success', message: __('passwords.reset'));

            return $this->redirect(locaRoute('auth.login'), navigate: true);
        }

        $this->addError('email', __($status));
    }

    public function render()
    {
        return view('livewire.users.reset-password')->layout('components.layouts.app');
    }
}
