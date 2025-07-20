<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Services\LocaleCurrencyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return __('personal.address_manage');
    }

    public static function getLabel(): string
    {
        return __('personal.address');
    }

    public static function form(Form $form): Form
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        return $form
            ->schema([
                Forms\Components\TextInput::make('firstname')
                    ->label(__('personal.firstname'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('lastname')
                    ->label(__('personal.lastname'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label(__('personal.email'))
                    ->email()
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('telephone')
                    ->label(__('personal.telephone'))
                    ->tel()
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('company')
                    ->label(__('personal.company'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address_1')
                    ->label(__('personal.address_1'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('address_2')
                    ->label(__('personal.address_2'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('city')
                    ->label(__('personal.city'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('postcode')
                    ->label(__('personal.postcode'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('country_id')
                    ->label(__('personal.country'))
                    ->relationship('country', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name ? $translation->name : $record->iso_code_2;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('zone_id')
                    ->label(__('personal.zone'))
                    ->relationship('zone', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->zoneTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name ? $translation->name : $record->code;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        return $table
            // ->query(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->query(
                fn() => static::getEloquentQuery()->where('user_id', auth()->id())
            )
            ->columns([
                Tables\Columns\TextColumn::make('firstname')
                    ->label(__('personal.firstname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->label(__('personal.lastname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('personal.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label(__('personal.telephone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->label(__('personal.company'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_1')
                    ->label(__('personal.address_1'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_2')
                    ->label(__('personal.address_2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('personal.city'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('postcode')
                    ->label(__('personal.postcode'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('personal.country'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $country = $record->country;
                        if (!$country) return '';
                        $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name ? $translation->name : $country->iso_code_2;
                    }),
                Tables\Columns\TextColumn::make('zone.name')
                    ->label(__('personal.zone'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $zone = $record->zone;
                        if (!$zone) return '';
                        $translation = $zone->zoneTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name ? $translation->name : $zone->code;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('personal.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('personal.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAddresses::route('/'),
        ];
    }
}
