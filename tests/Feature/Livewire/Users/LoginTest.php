<?php

namespace Tests\Feature\Livewire\Users;

use Illuminate\Support\Facades\Auth;
use Tests\Feature\LivewireTestCase;

class LoginTest extends LivewireTestCase
{
    public function test_login_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Users\Login::class);

        $component->assertSuccessful();
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->call('login');

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertFalse(Auth::check());
    }

    public function test_login_validates_email_required()
    {
        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_login_validates_password_required()
    {
        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('email', 'test@example.com')
            ->call('login')
            ->assertHasErrors(['password']);
    }

    public function test_login_validates_email_format()
    {
        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('email', 'invalid-email')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_remember_me_functionality()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = $this->livewire(\App\Livewire\Users\Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('login');

        $this->assertTrue(Auth::check());
    }
}
