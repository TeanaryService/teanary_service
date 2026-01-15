<?php

namespace App\Filament\Manager\Resources;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use App\Filament\Manager\Resources\OrderResource\Pages;
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
                    ->label(__('filament.order.order_no'))
                    ->disabled(),
                Forms\Components\Select::make('payment_method')
                    ->label(__('filament.order.payment_method'))
                    ->options(PaymentMethodEnum::options()),
                Forms\Components\Select::make('shipping_method')
                    ->label(__('filament.order.shipping_method'))
                    ->options(ShippingMethodEnum::options()),
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.order.user_id'))
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
                    ->label(__('filament.order.shipping_address_id'))
                    ->relationship(
                        'shippingAddress',
                        'id',
                        fn ($query, $get) => $query->when($get('user_id'), fn ($q, $userId) => $q->where('user_id', $userId))
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        if (! $record) {
                            return '';
                        }

                        return "{$record->firstname} {$record->lastname} ({$record->address_1}, {$record->city})";
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\Select::make('billing_address_id')
                    ->label(__('filament.order.billing_address_id'))
                    ->relationship(
                        'billingAddress',
                        'id',
                        fn ($query, $get) => $query->when($get('user_id'), fn ($q, $userId) => $q->where('user_id', $userId))
                    )
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        if (! $record) {
                            return '';
                        }

                        return "{$record->firstname} {$record->lastname} ({$record->address_1}, {$record->city})";
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\Select::make('currency_id')
                    ->label(__('filament.order.currency_id'))
                    ->live()
                    ->searchable()
                    ->preload()
                    ->relationship('currency', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('total')
                    ->label(__('filament.order.total'))
                    ->required()
                    ->prefix(fn ($get) => optional(\App\Models\Currency::find($get('currency_id')))->symbol ?? '¥')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('shipping_fee')
                    ->label(__('filament.order.shipping_fee'))
                    ->required()
                    ->prefix(fn ($get) => optional(\App\Models\Currency::find($get('currency_id')))->symbol ?? '¥')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Select::make('status')
                    ->label(__('filament.order.status'))
                    ->options(OrderStatusEnum::options())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'user',
                        'currency',
                        'shippingAddress',
                        'billingAddress',
                        'orderItems',
                    ])
                    ->withCount('orderItems')
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                    ->label(__('filament.order.order_no'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.order.user_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.order.status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        \App\Enums\OrderStatusEnum::Pending => 'gray',
                        \App\Enums\OrderStatusEnum::Paid => 'info',
                        \App\Enums\OrderStatusEnum::Shipped => 'warning',
                        \App\Enums\OrderStatusEnum::Completed => 'success',
                        \App\Enums\OrderStatusEnum::Cancelled => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('filament.order.total'))
                    ->prefix(fn ($record): string => $record->currency ? $record->currency->symbol : '')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_fee')
                    ->label(__('filament.order.shipping_fee'))
                    ->prefix(fn ($record): string => $record->currency ? $record->currency->symbol : '')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('order_items_count')
                    ->label(__('filament.order.items_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shippingAddress')
                    ->label(__('filament.order.shipping_address_id'))
                    ->formatStateUsing(function ($record) {
                        $addr = $record->shippingAddress;
                        if (! $addr) {
                            return '-';
                        }
                        return "{$addr->firstname} {$addr->lastname} ({$addr->city})";
                    })
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('billingAddress')
                    ->label(__('filament.order.billing_address_id'))
                    ->formatStateUsing(function ($record) {
                        $addr = $record->billingAddress;
                        if (! $addr) {
                            return '-';
                        }
                        return "{$addr->firstname} {$addr->lastname} ({$addr->city})";
                    })
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament.order.status'))
                    ->options(OrderStatusEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('filament.order.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('currency_id')
                    ->label(__('filament.order.currency_id'))
                    ->relationship('currency', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('filament.order.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('filament.order.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
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
