<?php

namespace App\Filament\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Models\Attribute;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Attribute::class;

    protected static ?int $navigationSort = 203;

    public static function getLabel(): string
    {
        return __('filament.AttributeResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.AttributeResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.AttributeResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.AttributeResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.AttributeResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([
                Forms\Components\Toggle::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->default(true)
                    ->helperText(__('filament.attribute.is_filterable_helper'))
                    ->columnSpanFull(),
                Forms\Components\Select::make('translation_status')
                    ->label('翻译状态')
                    ->options(TranslationStatusEnum::options())
                    ->default(TranslationStatusEnum::NotTranslated->value)
                    ->required(),
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->attributeTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }

                        return TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament.attribute.name')." ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                    ->label(__('filament.attribute.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 显示当前语言的 name
                TextColumn::make('attributeTranslations.name')
                    ->label(__('filament.attribute.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

                        return optional(
                            $record->attributeTranslations->where('language_id', $lang?->id)->first()
                        )->name;
                    }),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label('翻译状态')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    }),
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
            AttributeValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
