<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\SpecificationResource\Pages;
use App\Filament\Manager\Resources\SpecificationResource\RelationManagers\SpecificationValuesRelationManager;
use App\Models\Specification;
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

class SpecificationResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Specification::class;

    protected static ?int $navigationSort = 205;

    public static function getLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.SpecificationResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.SpecificationResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.SpecificationResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.specification.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.specification.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(1),
                Forms\Components\Section::make(__('filament.specification.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->specificationTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.specification.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.specification.name_helper') : null);
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
                        'specificationTranslations',
                        'specificationValues',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.specification.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->specificationTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationTranslations->first();
                        return $first ? $first->name : __('filament.specification.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('specificationTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('specification_translations', function ($join) use ($langId) {
                            $join->on('specifications.id', '=', 'specification_translations.specification_id')
                                ->where('specification_translations.language_id', '=', $langId);
                        })
                        ->orderBy('specification_translations.name', $direction)
                        ->select('specifications.*')
                        ->groupBy('specifications.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('values_count')
                    ->label(__('filament.specification.values_count'))
                    ->getStateUsing(function ($record) {
                        return $record->specificationValues->count();
                    })
                    ->numeric()
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->withCount('specificationValues')
                            ->orderBy('specification_values_count', $direction);
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.specification.translation_status'))
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
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.specification.translation_status'))
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
            SpecificationValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecifications::route('/'),
            'create' => Pages\CreateSpecification::route('/create'),
            'edit' => Pages\EditSpecification::route('/{record}/edit'),
        ];
    }
}
