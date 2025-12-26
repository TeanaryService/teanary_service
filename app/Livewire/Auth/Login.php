<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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
            $this->addError('email', __('auth.failed'));
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
