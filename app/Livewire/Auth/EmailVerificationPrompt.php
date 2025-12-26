<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmailVerificationPrompt extends Component
{
    public $resent = false;

    public function sendVerificationEmail()
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return redirect()->route('home', ['locale' => app()->getLocale()]);
        }

        Auth::user()?->sendEmailVerificationNotification();
        $this->resent = true;
    }

    public function render()
    {
        return view('livewire.auth.email-verification-prompt');
    }
}
