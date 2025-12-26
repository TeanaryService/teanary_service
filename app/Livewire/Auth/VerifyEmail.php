<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VerifyEmail extends Component
{
    public $id;

    public $hash;

    public function mount($locale, $id, $hash)
    {
        $user = Auth::user();

        if (! $user || $user->getKey() != $id) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // 可选：验证成功后自动登录跳转
        session()->flash('message', __('auth.verify_email_success'));
        redirect()->route('home', ['locale' => $locale]);
    }

    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
