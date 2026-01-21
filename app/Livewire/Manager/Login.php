<?php

namespace App\Livewire\Manager;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    protected array $messages = [
        'email.required' => '请输入邮箱地址',
        'email.email' => '请输入有效的邮箱地址',
        'password.required' => '请输入密码',
    ];

    public function mount(): void
    {
        // 如果已登录，重定向到首页
        if (Auth::guard('manager')->check()) {
            redirect()->to(locaRoute('manager.home'));
        }
    }

    public function login()
    {
        $this->validate();

        if (! Auth::guard('manager')->attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember
        )) {
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
