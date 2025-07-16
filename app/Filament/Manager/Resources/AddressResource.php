<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\AddressResource\Pages;
use App\Filament\Manager\Resources\AddressResource\RelationManagers;
use App\Models\Address;
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

class AddressResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Address::class;

    public static function getLabel(): string
    {
        return __('filament.AddressResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.AddressResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.AddressResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.AddressResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.AddressResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.AddressResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('filament_address.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('firstname')
                    ->label(__('filament_address.firstname'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('lastname')
                    ->label(__('filament_address.lastname'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament_address.email'))
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('telephone')
                    ->label(__('filament_address.telephone'))
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('company')
                    ->label(__('filament_address.company'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('country_id')
                    ->label(__('filament_address.country_id'))
                    ->relationship('country', 'name')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        // 没有当前语言翻译时，显示第一个翻译的 name
                        $first = $record->countryTranslations->first();
                        return $first ? $first->name : $record->name;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null)
                    ->afterStateUpdated(function ($set) {
                        // 选国家后清空地区
                        $set('zone_id', null);
                    })
                    ->reactive(),
                Forms\Components\Select::make('zone_id')
                    ->label(__('filament_address.zone_id'))
                    ->options(function ($get) {
                        $countryId = $get('country_id');
                        if (!$countryId) {
                            return [];
                        }
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $zones = \App\Models\Zone::where('country_id', $countryId)->with('zoneTranslations')->get();
                        $options = [];
                        foreach ($zones as $zone) {
                            $translation = $zone->zoneTranslations->where('language_id', $lang?->id)->first();
                            if ($translation && $translation->name) {
                                $options[$zone->id] = $translation->name;
                            } else {
                                $first = $zone->zoneTranslations->first();
                                $options[$zone->id] = $first ? $first->name : $zone->name;
                            }
                        }
                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null)
                    ->reactive(),
                Forms\Components\TextInput::make('address_1')
                    ->label(__('filament_address.address_1'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address_2')
                    ->label(__('filament_address.address_2'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('city')
                    ->label(__('filament_address.city'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postcode')
                    ->label(__('filament_address.postcode'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament_address.user_id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->label(__('filament_address.firstname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->label(__('filament_address.lastname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament_address.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label(__('filament_address.telephone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->label(__('filament_address.company'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_1')
                    ->label(__('filament_address.address_1'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_2')
                    ->label(__('filament_address.address_2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('filament_address.city'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('postcode')
                    ->label(__('filament_address.postcode'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('filament_address.country_id'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->country?->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->country?->countryTranslations->first();
                        return $first ? $first->name : $record->country?->name;
                    }),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label(__('filament_address.zone_id'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->zone?->zoneTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->zone?->zoneTranslations->first();
                        return $first ? $first->name : $record->zone?->name;
                    }),
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
