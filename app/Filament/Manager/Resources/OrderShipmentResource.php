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
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'order',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('order.order_no')
                    ->label(__('filament.order_shipment.order_no'))
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([OrderShipmentsRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label(__('filament.order_shipment.shipping_method'))
                    ->getStateUsing(function ($record) {
                        return $record->shipping_method->label();
                    })
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label(__('filament.order_shipment.tracking_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('filament.order_shipment.notes'))
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shipping_method')
                    ->label(__('filament.order_shipment.shipping_method'))
                    ->options(ShippingMethodEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('order_id')
                    ->label(__('filament.order_shipment.order_id'))
                    ->relationship('order', 'order_no')
                    ->searchable()
                    ->preload()
                    ->hiddenOn([OrderShipmentsRelationManager::class]),
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
