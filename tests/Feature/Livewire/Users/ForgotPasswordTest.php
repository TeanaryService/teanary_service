<?php

namespace Tests\Feature\Livewire\Users;

use Illuminate\Support\Facades\Password;
use Tests\Feature\LivewireTestCase;

class ForgotPasswordTest extends LivewireTestCase
{
    public function test_forgot_password_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Users\ForgotPassword::class);
        $component->assertSuccessful();
    }

    public function test_user_can_request_password_reset()
    {
        $user = $this->createUser(['email' => 'test@example.com']);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $component = $this->livewire(\App\Livewire\Users\ForgotPassword::class)
            ->set('email', 'test@example.com')
            ->call('sendResetLink');

        $component->assertSet('status', __('passwords.sent'));
    }

    public function test_forgot_password_validates_email_required()
    {
        $component = $this->livewire(\App\Livewire\Users\ForgotPassword::class)
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_forgot_password_validates_email_format()
    {
        $component = $this->livewire(\App\Livewire\Users\ForgotPassword::class)
            ->set('email', 'invalid-email')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_forgot_password_handles_invalid_user()
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::INVALID_USER);

        $component = $this->livewire(\App\Livewire\Users\ForgotPassword::class)
            ->set('email', 'nonexistent@example.com')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }
}
