<?php

namespace App\Filament\User\Resources\UserOrderResource\Pages;

use App\Filament\User\Resources\UserOrderResource;
use App\Enums\OrderStatusEnum;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewUserOrder extends ViewRecord
{
    protected static string $resource = UserOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label(__('orders.cancel'))
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status->canBeCancelled())
                ->action(function () {
                    $this->record->update(['status' => OrderStatusEnum::Cancelled]);
                    Notification::make()
                        ->title(__('orders.operation_success'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
