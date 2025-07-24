<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Filament\Resources\CountryResource\RelationManagers;
use App\Filament\Resources\CountryResource\RelationManagers\ZonesRelationManager;
use App\Models\Country;
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
use App\Services\LocaleCurrencyService;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;

class CountryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Country::class;
    protected static ?int $navigationSort = 405;

    public static function getLabel(): string
    {
        return __('filament.CountryResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CountryResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CountryResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CountryResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CountryResource.icon');
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
                            $translation = $model->countryTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament.country.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament.country.name')),

                Forms\Components\TextInput::make('iso_code_2')
                    ->label(__('filament.country.iso_code_2'))
                    ->maxLength(2)
                    ->default(null),
                Forms\Components\TextInput::make('iso_code_3')
                    ->label(__('filament.country.iso_code_3'))
                    ->maxLength(3)
                    ->default(null),
                Forms\Components\Toggle::make('postcode_required')
                    ->label(__('filament.country.postcode_required'))
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament.country.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 多语言 name 列
                Tables\Columns\TextColumn::make('countryTranslations.name')
                    ->label(__('filament.country.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->countryTranslations->first();
                        return $first ? $first->name : '';
                    }),
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->label(__('filament.country.iso_code_2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso_code_3')
                    ->label(__('filament.country.iso_code_3'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('postcode_required')
                    ->label(__('filament.country.postcode_required'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.country.active'))
                    ->boolean(),
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
            ZonesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
