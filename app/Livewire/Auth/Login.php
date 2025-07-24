<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    public function mount()
    {
        Auth::logout();
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $locale = app()->getLocale();
            return redirect()->route('home', ['locale' => $locale]);
        } else {
            $this->addError('email', '账号或密码错误');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
