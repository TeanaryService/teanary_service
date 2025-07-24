<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;

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
