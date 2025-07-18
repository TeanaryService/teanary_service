<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ShippingMethodResource\Pages;
use App\Filament\Manager\Resources\ShippingMethodResource\RelationManagers;
use App\Models\ShippingMethod;
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

class ShippingMethodResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ShippingMethod::class;
    protected static ?int $navigationSort = 404;

    public static function getLabel(): string
    {
        return __('filament.ShippingMethodResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ShippingMethodResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ShippingMethodResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ShippingMethodResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ShippingMethodResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->shippingMethodTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return Forms\Components\TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament_shipping_method.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament_shipping_method.name')),

                Forms\Components\TextInput::make('code')
                    ->label(__('filament_shipping_method.code'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('api_url')
                    ->label(__('filament_shipping_method.api_url'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_shipping_method.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('shippingMethodTranslations.name')
                    ->label(__('filament_shipping_method.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->shippingMethodTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->shippingMethodTranslations->first();
                        return $first ? $first->name : '';
                    }),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament_shipping_method.code'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_shipping_method.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('api_url')
                    ->label(__('filament_shipping_method.api_url'))
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
            'index' => getFilamentUrl(Pages\ListShippingMethods::class, '/'),
            'create' => getFilamentUrl(Pages\CreateShippingMethod::class, '/create'),
            'edit' => getFilamentUrl(Pages\EditShippingMethod::class, '/{record}/edit'),
        ];
    }
}
