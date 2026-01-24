<?php

namespace App\Livewire\Users;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Notifications extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // 标记所有通知为已读
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
    }

    #[Computed]
    public function notifications()
    {
        $user = Auth::user();
        if (! $user) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1);
        }

        return $user->notifications()
            ->latest()
            ->paginate(15);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
            session()->flash('message', __('notifications.marked_as_read'));
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        session()->flash('message', __('notifications.all_marked_as_read'));
    }

    public function deleteNotification(string $notificationId): void
    {
        Auth::user()->notifications()->find($notificationId)?->delete();
        session()->flash('message', __('notifications.deleted'));
    }

    public function render()
    {
        return view('livewire.users.notifications', [
            'notifications' => $this->notifications,
        ])->layout('components.layouts.app');
    }
}
