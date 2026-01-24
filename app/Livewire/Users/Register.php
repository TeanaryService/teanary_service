<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected $messages = [
        'name.required' => '请输入姓名',
        'email.required' => '请输入邮箱地址',
        'email.email' => '请输入有效的邮箱地址',
        'email.unique' => '该邮箱已被注册',
        'password.required' => '请输入密码',
        'password.min' => '密码至少需要8个字符',
        'password.confirmed' => '两次输入的密码不一致',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        // 发送邮箱验证通知
        $user->sendEmailVerificationNotification();

        session()->flash('message', __('auth.registration_success'));

        return redirect()->to(locaRoute('verification.notice'));
    }

    public function render()
    {
        return view('livewire.users.register')->layout('components.layouts.app');
    }
}
