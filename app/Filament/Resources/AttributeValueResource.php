<?php

namespace App\Filament\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Filament\Resources\AttributeValueResource\Pages;
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

        return $form
            ->schema([
                Forms\Components\Select::make('attribute_id')
                    ->label(__('filament.attribute_value.attribute'))
                    ->relationship('attribute', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
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
                    ->columnSpanFull()
                    ->hiddenOn([AttributeValuesRelationManager::class]),
                Forms\Components\Select::make('translation_status')
                    ->label('翻译状态')
                    ->options(TranslationStatusEnum::options())
                    ->default(TranslationStatusEnum::NotTranslated->value)
                    ->required()
                    ->hiddenOn([AttributeValuesRelationManager::class]),

                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->attributeValueTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return Forms\Components\TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament.attribute_value.name')." ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament.attribute_value.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 显示当前语言的 name
                Tables\Columns\TextColumn::make('attributeValueTranslations.name')
                    ->label(__('filament.attribute_value.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

                        return optional(
                            $record->attributeValueTranslations->where('language_id', $lang?->id)->first()
                        )->name;
                    }),
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label(__('filament.attribute_value.attribute'))
                    ->hiddenOn([AttributeValuesRelationManager::class])
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
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
                    ->hiddenOn([AttributeValuesRelationManager::class]),
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
            'index' => Pages\ListAttributeValues::route('/'),
            'create' => Pages\CreateAttributeValue::route('/create'),
            'edit' => Pages\EditAttributeValue::route('/{record}/edit'),
        ];
    }
}
