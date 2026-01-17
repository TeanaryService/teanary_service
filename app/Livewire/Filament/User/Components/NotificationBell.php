<?php

namespace App\Livewire\Filament\User\Components;

use App\Filament\User\Pages\Notifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function getUnreadCountProperty(): int
    {
        return Auth::user()->unreadNotifications->count();
    }

    public function render()
    {
        return view('livewire.filament.user.components.notification-bell');
    }
}
