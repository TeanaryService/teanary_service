<?php

namespace App\Livewire\Filament\Manager\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getUnreadCountProperty(): int
    {
        return Auth::guard('manager')->user()->unreadNotifications->count();
    }

    public function getUrl(): string
    {
        return locaRoute('manager.notifications');
    }

    public function render()
    {
        return view('livewire.filament.manager.components.notification-bell');
    }
}
