<?php

namespace App\Filament\Manager\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Notifications extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.manager.pages.notifications';

    protected static ?string $navigationLabel = null;

    protected ?string $heading = null;

    public function mount(): void
    {
        // 标记所有通知为已读
        Auth::guard('manager')->user()->unreadNotifications->markAsRead();
    }

    public function getNotificationsProperty(): LengthAwarePaginator
    {
        return Auth::guard('manager')->user()
            ->notifications()
            ->latest()
            ->paginate(15);
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::guard('manager')->user()->notifications()->find($notificationId);
        if ($notification && $notification->unread()) {
            $notification->markAsRead();
            FilamentNotification::make()
                ->title(__('notifications.marked_as_read'))
                ->success()
                ->send();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::guard('manager')->user()->unreadNotifications->markAsRead();
        FilamentNotification::make()
            ->title(__('notifications.all_marked_as_read'))
            ->success()
            ->send();
    }

    public function deleteNotification(string $notificationId): void
    {
        Auth::guard('manager')->user()->notifications()->find($notificationId)?->delete();
        FilamentNotification::make()
            ->title(__('notifications.deleted'))
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return __('notifications.my_notifications');
    }

    public function getTitle(): string
    {
        return __('notifications.my_notifications');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAllAsRead')
                ->label(__('notifications.mark_all_as_read'))
                ->icon('heroicon-o-check-circle')
                ->action('markAllAsRead')
                ->visible(fn () => Auth::guard('manager')->user()->unreadNotifications->count() > 0),
        ];
    }
}
