<?php

namespace App\Livewire;

use App\Models\Contact;
use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class ContactForm extends Component
{
    public $name = '';
    public $email = '';
    public $message = '';

    public function mount() {}

    public function save()
    {
        $this->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|max:255',
            'message' => 'required'
        ]);

        Contact::create([
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message
        ]);

        $this->reset(['name', 'email', 'message']);
        session()->flash('message', '消息已发送！');
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
