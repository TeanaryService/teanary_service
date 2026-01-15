<?php

namespace App\Filament\User\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\User\Resources\UserOrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UserOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function getLabel(): string
    {
        return __('orders.my_orders');
    }

    public static function getPluralLabel(): string
    {
        return __('orders.my_orders');
    }

    public static function getNavigationLabel(): string
    {
        return __('orders.my_orders');
    }

    // 只显示当前用户的订单
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_no')
                    ->label(__('orders.order_no'))
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label(__('orders.status_label'))
                    ->options(OrderStatusEnum::options())
                    ->disabled(), // 用户不能直接修改状态
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                    ->label(__('orders.order_no'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('orders.status_label'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('orders.total'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label(__('orders.cancel'))
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status->canBeCancelled())
                    ->action(function (Order $record) {
                        $record->update(['status' => OrderStatusEnum::Cancelled]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserOrders::route('/'),
            'view' => Pages\ViewUserOrder::route('/{record}'),
        ];
    }

    // 只在用户面板显示
    public static function shouldRegisterNavigation(): bool
    {
        return \Filament\Facades\Filament::getCurrentPanel()?->getId() === 'user';
    }
}
