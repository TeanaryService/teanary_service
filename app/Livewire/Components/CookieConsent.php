<?php

namespace App\Livewire\Components;

use Livewire\Component;

class CookieConsent extends Component
{
    public $show = true;

    public function mount()
    {
        $this->show = ! request()->cookies->has('cookie_consent');
    }

    public function accept()
    {
        cookie()->queue('cookie_consent', '1', 60 * 24 * 365); // 1年
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.components.cookie-consent');
    }
}
