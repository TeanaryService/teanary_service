<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Filament\Manager\Resources\AttributeValueResource\Pages;
use App\Models\AttributeValue;
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

class AttributeValueResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = AttributeValue::class;

    protected static ?int $navigationSort = 999;

    public static function getLabel(): string
    {
        return __('filament.AttributeValueResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.AttributeValueResource.pluralLabel');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.AttributeValueResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.AttributeValueResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.AttributeValueResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.attribute_value.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('attribute_id')
                            ->label(__('filament.attribute_value.attribute'))
                            ->relationship('attribute', 'id', function ($query) {
                                return $query->with('attributeTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                                $translation = $record->attributeTranslations->where('language_id', $lang?->id)->first();
                                if ($translation && $translation->name) {
                                    return $translation->name;
                                }
                                $first = $record->attributeTranslations->first();
                                return $first ? $first->name : $record->id;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([AttributeValuesRelationManager::class])
                            ->helperText(__('filament.attribute_value.attribute_helper')),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.attribute_value.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1)
                            ->hiddenOn([AttributeValuesRelationManager::class]),
                    ])
                    ->columns(2)
                    ->hiddenOn([AttributeValuesRelationManager::class]),
                Forms\Components\Section::make(__('filament.attribute_value.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->attributeValueTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return Forms\Components\TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.attribute_value.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.attribute_value.name_helper') : null);
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
                        'attribute.attributeTranslations',
                        'attributeValueTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.attribute_value.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->attributeValueTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->attributeValueTranslations->first();
                        return $first ? $first->name : __('filament.attribute_value.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('attributeValueTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('attribute_value_translations', function ($join) use ($langId) {
                            $join->on('attribute_values.id', '=', 'attribute_value_translations.attribute_value_id')
                                ->where('attribute_value_translations.language_id', '=', $langId);
                        })
                        ->orderBy('attribute_value_translations.name', $direction)
                        ->select('attribute_values.*')
                        ->groupBy('attribute_values.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label(__('filament.attribute_value.attribute'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $attribute = $record->attribute;
                        if (! $attribute) {
                            return null;
                        }
                        $translation = $attribute->attributeTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $attribute->attributeTranslations->first();
                        return $first ? $first->name : $attribute->id;
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('attribute.attributeTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
                            ->leftJoin('attribute_translations', function ($join) use ($langId) {
                                $join->on('attributes.id', '=', 'attribute_translations.attribute_id')
                                    ->where('attribute_translations.language_id', '=', $langId);
                            })
                            ->orderBy('attribute_translations.name', $direction)
                            ->select('attribute_values.*')
                            ->groupBy('attribute_values.id');
                    })
                    ->hiddenOn([AttributeValuesRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('filament.attribute_value.products_count'))
                    ->counts('products')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.attribute_value.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->hiddenOn([AttributeValuesRelationManager::class])
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('attribute_id')
                    ->label(__('filament.attribute_value.attribute'))
                    ->relationship('attribute', 'id', function ($query) use ($lang) {
                        return $query->with('attributeTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->attributeTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->attributeTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->hiddenOn([AttributeValuesRelationManager::class]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.attribute_value.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple()
                    ->hiddenOn([AttributeValuesRelationManager::class]),
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
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
