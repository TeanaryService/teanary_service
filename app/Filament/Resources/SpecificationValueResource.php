<?php

namespace App\Filament\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Resources\SpecificationResource\RelationManagers\SpecificationValuesRelationManager;
use App\Filament\Resources\SpecificationValueResource\Pages;
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

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 206;

    public static function getLabel(): string
    {
        return __('filament.SpecificationValueResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.SpecificationValueResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
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
                Forms\Components\Select::make('specification_id')
                    ->label(__('filament.specification_value.specification_id'))
                    ->relationship('specification', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->specificationTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationTranslations->first();

                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->hiddenOn([SpecificationValuesRelationManager::class])
                    ->required(),
                Forms\Components\Select::make('translation_status')
                    ->label('翻译状态')
                    ->options(TranslationStatusEnum::options())
                    ->default(TranslationStatusEnum::NotTranslated->value)
                    ->required()
                    ->hiddenOn([SpecificationValuesRelationManager::class]),
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->specificationValueTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return Forms\Components\TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament.specification_value.name')." ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament.specification_value.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('specification.name')
                    ->label(__('filament.specification_value.specification_id'))
                    ->hiddenOn([SpecificationValuesRelationManager::class])
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
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
                    }),
                Tables\Columns\TextColumn::make('specificationValueTranslations.name')
                    ->label(__('filament.specification_value.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->specificationValueTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->specificationValueTranslations->first();

                        return $first ? $first->name : '';
                    }),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label('翻译状态')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->hiddenOn([SpecificationValuesRelationManager::class]),
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
                    ...static::getTranslationStatusBulkActions(),
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
            'index' => Pages\ListSpecificationValues::route('/'),
            'create' => Pages\CreateSpecificationValue::route('/create'),
            'edit' => Pages\EditSpecificationValue::route('/{record}/edit'),
        ];
    }
}
