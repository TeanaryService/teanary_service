<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\OrderResource\RelationManagers\OrderShipmentsRelationManager;
use App\Filament\Manager\Resources\OrderShipmentResource\Pages;
use App\Filament\Manager\Resources\OrderShipmentResource\RelationManagers;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->label(__('filament_order_shipment.order_id'))
                    ->relationship('order', 'id')
                    ->hiddenOn([OrderShipmentsRelationManager::class])
                    ->required(),
                Forms\Components\Select::make('shipping_method_id')
                    ->label(__('filament_order_shipment.shipping_method_id'))
                    ->relationship('shippingMethod', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->shippingMethodTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->shippingMethodTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('tracking_number')
                    ->label(__('filament_order_shipment.tracking_number'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('notes')
                    ->label(__('filament_order_shipment.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label(__('filament_order_shipment.order_id'))
                    ->numeric()
                    ->hiddenOn([OrderShipmentsRelationManager::class])
                    ->sortable(),
                Tables\Columns\TextColumn::make('shippingMethod.name')
                    ->label(__('filament_order_shipment.shipping_method_id'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $shippingMethod = $record->shippingMethod;
                        if (!$shippingMethod) return null;
                        $translation = $shippingMethod->shippingMethodTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $shippingMethod->shippingMethodTranslations->first();
                        return $first ? $first->name : $shippingMethod->id;
                    }),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->label(__('filament_order_shipment.tracking_number'))
                    ->searchable(),
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
