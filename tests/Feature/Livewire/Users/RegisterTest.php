<?php

namespace Tests\Feature\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\LivewireTestCase;

class RegisterTest extends LivewireTestCase
{
    public function test_register_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Users\Register::class);

        $component->assertSuccessful();
    }

    public function test_user_can_register_with_valid_data()
    {
        $component = $this->livewire(\App\Livewire\Users\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_register_validates_name_required()
    {
        $component = $this->livewire(\App\Livewire\Users\Register::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['name']);
    }

    public function test_register_validates_email_required()
    {
        $component = $this->livewire(\App\Livewire\Users\Register::class)
            ->set('name', 'Test User')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);
    }

    public function test_register_validates_email_unique()
    {
        $this->createUser(['email' => 'existing@example.com']);

        $component = $this->livewire(\App\Livewire\Users\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);
    }

    public function test_register_validates_password_confirmation()
    {
        $component = $this->livewire(\App\Livewire\Users\Register::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different-password')
            ->call('register')
            ->assertHasErrors(['password']);
    }
}
