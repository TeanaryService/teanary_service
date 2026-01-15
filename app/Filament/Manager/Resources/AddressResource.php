<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\AddressResource\Pages;
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

class AddressResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Address::class;

    protected static ?int $navigationSort = 302;

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

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.address.user_info'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.address.user_id'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->helperText(__('filament.address.user_id_helper')),
                        Forms\Components\TextInput::make('firstname')
                            ->label(__('filament.address.firstname'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('lastname')
                            ->label(__('filament.address.lastname'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label(__('filament.address.email'))
                            ->email()
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('telephone')
                            ->label(__('filament.address.telephone'))
                            ->tel()
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('company')
                            ->label(__('filament.address.company'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make(__('filament.address.address_info'))
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label(__('filament.address.country_id'))
                            ->relationship('country', 'name')
                            ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                                $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                                if ($translation && $translation->name) {
                                    return $translation->name;
                                }
                                $first = $record->countryTranslations->first();
                                return $first ? $first->name : $record->name;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->afterStateUpdated(function ($set) {
                                $set('zone_id', null);
                            })
                            ->reactive(),
                        Forms\Components\Select::make('zone_id')
                            ->label(__('filament.address.zone_id'))
                            ->options(function ($get) use ($lang) {
                                $countryId = $get('country_id');
                                if (!$countryId) {
                                    return [];
                                }
                                $zones = \App\Models\Zone::where('country_id', $countryId)
                                    ->with('zoneTranslations')
                                    ->get();
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
                            ->reactive(),
                        Forms\Components\TextInput::make('address_1')
                            ->label(__('filament.address.address_1'))
                            ->maxLength(255)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('address_2')
                            ->label(__('filament.address.address_2'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('city')
                            ->label(__('filament.address.city'))
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\TextInput::make('postcode')
                            ->label(__('filament.address.postcode'))
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'user',
                        'country.countryTranslations',
                        'zone.zoneTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.address.user_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fullname')
                    ->label(__('filament.address.fullname'))
                    ->getStateUsing(function ($record) {
                        return trim("{$record->firstname} {$record->lastname}") ?: '-';
                    })
                    ->searchable(['firstname', 'lastname'])
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.address.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label(__('filament.address.telephone'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('filament.address.country_id'))
                    ->getStateUsing(function ($record) use ($lang) {
                        if (!$record->country) {
                            return '-';
                        }
                        $translation = $record->country->countryTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($record->country->countryTranslations->first()->name ?? $record->country->name ?? '-');
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label(__('filament.address.zone_id'))
                    ->getStateUsing(function ($record) use ($lang) {
                        if (!$record->zone) {
                            return '-';
                        }
                        $translation = $record->zone->zoneTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($record->zone->zoneTranslations->first()->name ?? $record->zone->name ?? '-');
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('full_address')
                    ->label(__('filament.address.full_address'))
                    ->formatStateUsing(function ($record) use ($lang) {
                        // 国家多语言
                        $countryName = '';
                        if ($record->country) {
                            $translation = $record->country->countryTranslations->where('language_id', $lang?->id)->first();
                            $countryName = $translation && $translation->name
                                ? $translation->name
                                : ($record->country->countryTranslations->first()->name ?? $record->country->name ?? '');
                        }

                        // 地区多语言
                        $zoneName = '';
                        if ($record->zone) {
                            $translation = $record->zone->zoneTranslations->where('language_id', $lang?->id)->first();
                            $zoneName = $translation && $translation->name
                                ? $translation->name
                                : ($record->zone->zoneTranslations->first()->name ?? $record->zone->name ?? '');
                        }

                        // 拼接完整地址
                        $parts = [];
                        $addressLine = trim("{$record->address_1} {$record->address_2}");
                        if ($addressLine) {
                            $parts[] = $addressLine;
                        }
                        $cityLine = trim(implode(' ', array_filter([
                            $record->city,
                            $zoneName,
                            $countryName,
                            $record->postcode,
                        ])));
                        if ($cityLine) {
                            $parts[] = $cityLine;
                        }

                        return count($parts) ? implode(', ', $parts) : '-';
                    })
                    ->searchable(['address_1', 'address_2', 'city', 'postcode'])
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('filament.address.orders_count'))
                    ->counts('orders')
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('filament.address.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('filament.address.country_id'))
                    ->relationship('country', 'name')
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($record->countryTranslations->first()->name ?? $record->name);
                    })
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
