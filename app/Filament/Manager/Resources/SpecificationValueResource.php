<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\SpecificationResource\RelationManagers\SpecificationValuesRelationManager;
use App\Filament\Manager\Resources\SpecificationValueResource\Pages;
use App\Models\SpecificationValue;
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

class SpecificationValueResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = SpecificationValue::class;

    protected static ?int $navigationSort = 998;

    public static function getLabel(): string
    {
        return __('filament.SpecificationValueResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.SpecificationValueResource.pluralLabel');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.SpecificationValueResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.SpecificationValueResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.SpecificationValueResource.icon');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.specification_value.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('specification_id')
                            ->label(__('filament.specification_value.specification'))
                            ->relationship('specification', 'id', function ($query) {
                                return $query->with('specificationTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($service) {
                                $locale = app()->getLocale();
                                $lang = $service->getLanguageByCode($locale);
                                $translation = $record->specificationTranslations->where('language_id', $lang?->id)->first();
                                if ($translation && $translation->name) {
                                    return $translation->name;
                                }
                                $first = $record->specificationTranslations->first();
                                return $first ? $first->name : $record->id;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([SpecificationValuesRelationManager::class])
                            ->helperText(__('filament.specification_value.specification_helper')),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.specification_value.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([SpecificationValuesRelationManager::class]),
                    ])
                    ->columns(2)
                    ->hiddenOn([SpecificationValuesRelationManager::class]),
                Forms\Components\Section::make(__('filament.specification_value.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->specificationValueTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return Forms\Components\TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.specification_value.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.specification_value.name_helper') : null);
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
                        'specification.specificationTranslations',
                        'specificationValueTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.specification_value.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->specificationValueTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationValueTranslations->first();
                        return $first ? $first->name : __('filament.specification_value.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('specificationValueTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('specification_value_translations', function ($join) use ($langId) {
                            $join->on('specification_values.id', '=', 'specification_value_translations.specification_value_id')
                                ->where('specification_value_translations.language_id', '=', $langId);
                        })
                        ->orderBy('specification_value_translations.name', $direction)
                        ->select('specification_values.*')
                        ->groupBy('specification_values.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('specification.name')
                    ->label(__('filament.specification_value.specification'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $spec = $record->specification;
                        if (! $spec) {
                            return null;
                        }
                        $translation = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $spec->specificationTranslations->first();
                        return $first ? $first->name : $spec->id;
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('specification.specificationTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->hiddenOn([SpecificationValuesRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.specification_value.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->hiddenOn([SpecificationValuesRelationManager::class])
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('specification_id')
                    ->label(__('filament.specification_value.specification'))
                    ->relationship('specification', 'id', function ($query) {
                        return $query->with('specificationTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->specificationTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->hiddenOn([SpecificationValuesRelationManager::class]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.specification_value.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple()
                    ->hiddenOn([SpecificationValuesRelationManager::class]),
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
            'index' => Pages\ListSpecificationValues::route('/'),
            'create' => Pages\CreateSpecificationValue::route('/create'),
            'edit' => Pages\EditSpecificationValue::route('/{record}/edit'),
        ];
    }
}
