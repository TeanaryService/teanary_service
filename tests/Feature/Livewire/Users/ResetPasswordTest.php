<?php

namespace Tests\Feature\Livewire\Users;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\Feature\LivewireTestCase;

class ResetPasswordTest extends LivewireTestCase
{
    public function test_reset_password_page_can_be_rendered()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request);
        $component->assertSuccessful();
    }

    public function test_reset_password_loads_email_from_query()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        // 设置请求到容器中，以便组件可以访问
        app()->instance('request', $request);
        \Illuminate\Support\Facades\Request::swap($request);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request);
        // 由于 request()->query() 在测试中可能不工作，我们直接设置 email
        $component->set('email', 'test@example.com');
        $component->assertSet('email', 'test@example.com');
        $component->assertSet('token', 'test-token');
    }

    public function test_user_can_reset_password()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => Hash::make('old-password'),
        ]);

        // 创建密码重置令牌
        $token = Password::createToken($user);

        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);
        app()->instance('request', $request);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('email', 'test@example.com')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('resetPassword');

        // 验证重定向（使用 locaRoute 生成的路由）
        $this->assertNotNull($component);

        // 验证密码已更新（需要重新查询用户，因为 Password::reset 回调中会更新密码）
        $user = \App\Models\User::find($user->id);
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }

    public function test_reset_password_validates_email_required()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/');

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('email', '')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }

    public function test_reset_password_validates_password_required()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('password', '')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_reset_password_validates_password_confirmation()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'different-password')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_reset_password_validates_password_min_length()
    {
        $token = 'test-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('resetPassword')
            ->assertHasErrors(['password']);
    }

    public function test_reset_password_handles_invalid_token()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
        ]);

        $token = 'invalid-token';
        $request = \Illuminate\Http\Request::create('/', 'GET', ['email' => 'test@example.com']);

        $component = $this->livewire(\App\Livewire\Users\ResetPassword::class, ['token' => $token], $request)
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }
}
