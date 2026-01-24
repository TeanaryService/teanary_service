<?php

namespace Tests\Feature\Livewire;

use Tests\Feature\LivewireTestCase;

class ContactFormTest extends LivewireTestCase
{
    public function test_contact_form_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class);
        $component->assertSuccessful();
    }

    public function test_user_can_submit_contact_form()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('message', 'This is a test message')
            ->call('save');

        $this->assertDatabaseHas('contacts', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message',
        ]);
    }

    public function test_contact_form_validates_name_required()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('email', 'john@example.com')
            ->set('message', 'This is a test message')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_contact_form_validates_email_required()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('name', 'John Doe')
            ->set('message', 'This is a test message')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_contact_form_validates_email_format()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('name', 'John Doe')
            ->set('email', 'invalid-email')
            ->set('message', 'This is a test message')
            ->call('save')
            ->assertHasErrors(['email']);
    }

    public function test_contact_form_validates_message_required()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->call('save')
            ->assertHasErrors(['message']);
    }

    public function test_contact_form_resets_after_submission()
    {
        $component = $this->livewire(\App\Livewire\ContactForm::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('message', 'This is a test message')
            ->call('save')
            ->assertSet('name', '')
            ->assertSet('email', '')
            ->assertSet('message', '');
    }
}
