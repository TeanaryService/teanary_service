<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages;
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
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('filament.address.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('firstname')
                    ->label(__('filament.address.firstname'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('lastname')
                    ->label(__('filament.address.lastname'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament.address.email'))
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('telephone')
                    ->label(__('filament.address.telephone'))
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('company')
                    ->label(__('filament.address.company'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('country_id')
                    ->label(__('filament.address.country_id'))
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
                    ->label(__('filament.address.zone_id'))
                    ->options(function ($get) {
                        $countryId = $get('country_id');
                        if (! $countryId) {
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
                    ->label(__('filament.address.address_1'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address_2')
                    ->label(__('filament.address.address_2'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('city')
                    ->label(__('filament.address.city'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postcode')
                    ->label(__('filament.address.postcode'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'country.countryTranslations',
                        'zone.zoneTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.address.user_id')),
                Tables\Columns\TextColumn::make('address_1')
                    ->label(__('filament.address.full_address'))
                    ->formatStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);

                        // 国家多语言
                        $countryName = '';
                        if ($record->country) {
                            $countryTranslations = $record->country->countryTranslations ?? collect();
                            $translation = $countryTranslations->where('language_id', $lang?->id)->first();
                            $countryName = $translation && $translation->name
                                ? $translation->name
                                : ($countryTranslations->first()->name ?? $record->country->name ?? '');
                        }

                        // 地区多语言
                        $zoneName = '';
                        if ($record->zone) {
                            $zoneTranslations = $record->zone->zoneTranslations ?? collect();
                            $translation = $zoneTranslations->where('language_id', $lang?->id)->first();
                            $zoneName = $translation && $translation->name
                                ? $translation->name
                                : ($zoneTranslations->first()->name ?? $record->zone->name ?? '');
                        }

                        // 拼接完整地址
                        $parts = [];
                        $fullname = trim("{$record->firstname} {$record->lastname}");
                        if ($fullname || $record->email) {
                            $parts[] = trim($fullname.($record->email ? " ({$record->email})" : ''));
                        }
                        if ($record->telephone) {
                            $parts[] = __('filament.address.telephone').": {$record->telephone}";
                        }
                        if ($record->company) {
                            $parts[] = __('filament.address.company').": {$record->company}";
                        }
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

                        // 防止所有字段都为空时返回空字符串
                        return count($parts) ? implode("\n", $parts) : '-';
                    })
                    ->limit(100)
                    ->toggleable(false)
                    ->wrap(),
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
