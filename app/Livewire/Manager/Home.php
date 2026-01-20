<?php

namespace App\Livewire\Manager;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.manager.home')->layout('components.layouts.manager');
    }
}
