<?php

namespace App\Filament\Manager\Resources;

use App\Enums\ShippingMethodEnum;
use App\Filament\Manager\Resources\OrderResource\RelationManagers\OrderShipmentsRelationManager;
use App\Filament\Manager\Resources\OrderShipmentResource\Pages;
use App\Models\OrderShipment;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderShipmentResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = OrderShipment::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 100;

    public static function getLabel(): string
    {
        return __('filament.OrderShipmentResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.OrderShipmentResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.OrderShipmentResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.OrderShipmentResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.OrderShipmentResource.icon');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);

        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label(__('filament.order_shipment.order_id'))
                    ->relationship('order', 'id')
                    ->hiddenOn([OrderShipmentsRelationManager::class])
                    ->required(),
                Forms\Components\Select::make('shipping_method')
                    ->label(__('filament.order_shipment.shipping_method'))
                    ->options(ShippingMethodEnum::options())
                    ->required(),
                Forms\Components\TextInput::make('tracking_number')
                    ->label(__('filament.order_shipment.tracking_number'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('notes')
                    ->label(__('filament.order_shipment.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label(__('filament.order_shipment.order_id'))
                    ->numeric()
                    ->hiddenOn([OrderShipmentsRelationManager::class])
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label(__('filament.order_shipment.shipping_method'))
                    // ->getStateUsing(fn(ShippingMethodEnum $state):string => $state->label()),
                    ->getStateUsing(function ($record) {
                        return $record->shipping_method->label();
                    }),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label(__('filament.order_shipment.tracking_number'))
                    ->searchable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions(),
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderShipments::route('/'),
            'create' => Pages\CreateOrderShipment::route('/create'),
            'edit' => Pages\EditOrderShipment::route('/{record}/edit'),
        ];
    }
}
