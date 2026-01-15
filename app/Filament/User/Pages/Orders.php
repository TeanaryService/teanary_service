<?php

namespace App\Filament\User\Pages;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class Orders extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.user.pages.orders';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    public static function getNavigationLabel(): string
    {
        return __('orders.my_orders');
    }

    public function getTitle(): string
    {
        return __('orders.my_orders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('user_id', Auth::id())->with(['orderItems.product', 'orderItems.productVariant']))
            ->columns([
                TextColumn::make('order_no')
                    ->label(__('orders.order_no'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('orders.status_label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('total')
                    ->label(__('orders.total'))
                    ->money('CNY')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                TableAction::make('view')
                    ->label(__('app.view'))
                    ->url(fn (Order $record) => \App\Filament\User\Pages\OrderDetail::getUrl(['record' => $record->id])),
                TableAction::make('cancel')
                    ->label(__('orders.cancel'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status->canBeCancelled())
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatusEnum::Cancelled]);
                        Notification::make()
                            ->title(__('orders.operation_success'))
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
