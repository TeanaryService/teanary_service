<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\CountryResource\Pages;
use App\Filament\Manager\Resources\CountryResource\RelationManagers\ZonesRelationManager;
use App\Models\Country;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

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
                Forms\Components\Section::make(__('filament.country.basic_info'))
                    ->schema([
                        Forms\Components\TextInput::make('iso_code_2')
                            ->label(__('filament.country.iso_code_2'))
                            ->maxLength(2)
                            ->default(null)
                            ->columnSpan(1)
                            ->helperText(__('filament.country.iso_code_2_helper')),
                        Forms\Components\TextInput::make('iso_code_3')
                            ->label(__('filament.country.iso_code_3'))
                            ->maxLength(3)
                            ->default(null)
                            ->columnSpan(1)
                            ->helperText(__('filament.country.iso_code_3_helper')),
                        Forms\Components\Toggle::make('postcode_required')
                            ->label(__('filament.country.postcode_required'))
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('active')
                            ->label(__('filament.country.active'))
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.country.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('filament.country.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->countryTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.country.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.country.name_helper') : null);
                            })->toArray()
                        )->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'countryTranslations',
                        'zones',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.country.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->countryTranslations->first();
                        return $first ? $first->name : __('filament.country.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('countryTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('country_translations', function ($join) use ($langId) {
                            $join->on('countries.id', '=', 'country_translations.country_id')
                                ->where('country_translations.language_id', '=', $langId);
                        })
                        ->orderBy('country_translations.name', $direction)
                        ->select('countries.*')
                        ->groupBy('countries.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->label(__('filament.country.iso_code_2'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('iso_code_3')
                    ->label(__('filament.country.iso_code_3'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('zones_count')
                    ->label(__('filament.country.zones_count'))
                    ->counts('zones')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('postcode_required')
                    ->label(__('filament.country.postcode_required'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.country.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.country.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('active')
                    ->label(__('filament.country.active'))
                    ->options([
                        1 => __('filament.country.active'),
                        0 => __('filament.country.inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.country.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                    ...static::getTranslationStatusBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
    }

    public static function getRelations(): array
    {
        return [
            //
            ZonesRelationManager::class,
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
