<?php

namespace App\Livewire\Manager;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    protected $messages = [
        'email.required' => '请输入邮箱地址',
        'email.email' => '请输入有效的邮箱地址',
        'password.required' => '请输入密码',
    ];

    public function login()
    {
        $this->validate();

        if (! Auth::guard('manager')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        return redirect()->intended(locaRoute('manager.home'));
    }

    public function render()
    {
        return view('livewire.manager.login')->layout('components.layouts.manager');
    }
}
