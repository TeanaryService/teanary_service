<?php

namespace App\Livewire\Filament\Manager\Components;

use App\Filament\Manager\Pages\Notifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getUnreadCountProperty(): int
    {
        return Auth::guard('manager')->user()->unreadNotifications->count();
    }

    public function render()
    {
        return view('livewire.filament.manager.components.notification-bell');
    }
}
