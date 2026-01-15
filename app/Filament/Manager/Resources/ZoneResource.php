<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\CountryResource\RelationManagers\ZonesRelationManager;
use App\Filament\Manager\Resources\ZoneResource\Pages;
use App\Models\Zone;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ZoneResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Zone::class;

    protected static ?int $navigationSort = 406;

    public static function getLabel(): string
    {
        return __('filament.ZoneResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.ZoneResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.ZoneResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.ZoneResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.ZoneResource.icon');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.zone.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label(__('filament.zone.country'))
                            ->relationship('country', 'id', function ($query) {
                                return $query->with('countryTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($service) {
                                $locale = app()->getLocale();
                                $lang = $service->getLanguageByCode($locale);
                                $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                                if ($translation && $translation->name) {
                                    return $translation->name;
                                }
                                $first = $record->countryTranslations->first();
                                return $first ? $first->name : $record->iso_code_2;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([ZonesRelationManager::class])
                            ->helperText(__('filament.zone.country_helper')),
                        Forms\Components\TextInput::make('code')
                            ->label(__('filament.zone.code'))
                            ->maxLength(255)
                            ->default(null)
                            ->columnSpan(1)
                            ->helperText(__('filament.zone.code_helper')),
                        Forms\Components\Toggle::make('active')
                            ->label(__('filament.zone.active'))
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.zone.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([ZonesRelationManager::class]),
                    ])
                    ->columns(2)
                    ->hiddenOn([ZonesRelationManager::class]),
                Forms\Components\Section::make(__('filament.zone.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->zoneTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return Forms\Components\TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.zone.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.zone.name_helper') : null);
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
                fn (Builder $query): Builder => $query
                    ->with([
                        'country.countryTranslations',
                        'zoneTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.zone.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->zoneTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->zoneTranslations->first();
                        return $first ? $first->name : __('filament.zone.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('zoneTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('zone_translations', function ($join) use ($langId) {
                            $join->on('zones.id', '=', 'zone_translations.zone_id')
                                ->where('zone_translations.language_id', '=', $langId);
                        })
                        ->orderBy('zone_translations.name', $direction)
                        ->select('zones.*')
                        ->groupBy('zones.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('country.name')
                    ->label(__('filament.zone.country'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $country = $record->country;
                        if (! $country) {
                            return null;
                        }
                        $translation = $country->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $country->countryTranslations->first();
                        return $first ? $first->name : $country->iso_code_2;
                    })
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([ZonesRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('filament.zone.code'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.zone.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.zone.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->hiddenOn([ZonesRelationManager::class])
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country_id')
                    ->label(__('filament.zone.country'))
                    ->relationship('country', 'id', function ($query) {
                        return $query->with('countryTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->countryTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->countryTranslations->first();
                        return $first ? $first->name : $record->iso_code_2;
                    })
                    ->searchable()
                    ->preload()
                    ->hiddenOn([ZonesRelationManager::class]),
                Tables\Filters\SelectFilter::make('active')
                    ->label(__('filament.zone.active'))
                    ->options([
                        1 => __('filament.zone.active'),
                        0 => __('filament.zone.inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.zone.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple()
                    ->hiddenOn([ZonesRelationManager::class]),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
