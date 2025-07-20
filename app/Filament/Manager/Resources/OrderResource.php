<?php

namespace App\Filament\Manager\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Filament\Manager\Resources\OrderResource\Pages;
use App\Filament\Manager\Resources\OrderResource\RelationManagers;
use App\Filament\Manager\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Manager\Resources\OrderResource\RelationManagers\OrderShipmentsRelationManager;
use App\Models\Order;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Order::class;
    protected static ?int $navigationSort = 100;

    public static function getLabel(): string
    {
        return __('filament.OrderResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.OrderResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.OrderResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.OrderResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.OrderResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_no')
                    ->label(__('filament_order.order_no'))
                    ->disabled(),
                Forms\Components\Select::make('payment_method')
                    ->label(__('filament_order.payment_method'))
                    ->options(PaymentMethodEnum::options()),
                Forms\Components\Select::make('user_id')
                    ->label(__('filament_order.user_id'))
                    ->relationship('user', 'name')
                    ->default(null)
                    ->live()
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // 用户变更时，清空收货地址和帐单地址
                        $set('shipping_address_id', null);
                        $set('billing_address_id', null);
                    }),
                Forms\Components\Select::make('shipping_address_id')
                    ->label(__('filament_order.shipping_address_id'))
                    ->relationship(
                        'shippingAddress',
                        'id',
                        fn($query, $get) => $query->when($get('user_id'), fn($q, $userId) => $q->where('user_id', $userId))
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        if (!$record) return '';
                        return "{$record->firstname} {$record->lastname} ({$record->address_1}, {$record->city})";
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\Select::make('billing_address_id')
                    ->label(__('filament_order.billing_address_id'))
                    ->relationship(
                        'billingAddress',
                        'id',
                        fn($query, $get) => $query->when($get('user_id'), fn($q, $userId) => $q->where('user_id', $userId))
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        if (!$record) return '';
                        return "{$record->firstname} {$record->lastname} ({$record->address_1}, {$record->city})";
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\Select::make('currency_id')
                    ->label(__('filament_order.currency_id'))
                    ->live()
                    ->searchable()
                    ->preload()
                    ->relationship('currency', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('total')
                    ->label(__('filament_order.total'))
                    ->required()
                    ->prefix(fn($get) => optional(\App\Models\Currency::find($get('currency_id')))->symbol ?? '¥')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Select::make('status')
                    ->label(__('filament_order.status'))
                    ->options(OrderStatusEnum::options())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->query(
                fn() => static::getEloquentQuery()
                    ->with([
                        'currency',
                        'shippingAddress',
                        'billingAddress'
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament_order.user_id')),
                Tables\Columns\TextColumn::make('order_no')
                    ->label(__('filament_order.order_no'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('shippingAddress')
                    ->label(__('filament_order.shipping_address_id'))
                    ->formatStateUsing(function ($record) {
                        $addr = $record->shippingAddress;
                        if (!$addr) return '';
                        return "{$addr->firstname} {$addr->lastname} ({$addr->address_1}, {$addr->city})";
                    }),
                Tables\Columns\TextColumn::make('billingAddress')
                    ->label(__('filament_order.billing_address_id'))
                    ->formatStateUsing(function ($record) {
                        $addr = $record->billingAddress;
                        if (!$addr) return '';
                        return "{$addr->firstname} {$addr->lastname} ({$addr->address_1}, {$addr->city})";
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('filament_order.total'))
                    ->prefix(fn($record): string => $record->currency->symbol)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label(__('filament_order.status')),
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions()
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
            OrderItemsRelationManager::class,
            OrderShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
