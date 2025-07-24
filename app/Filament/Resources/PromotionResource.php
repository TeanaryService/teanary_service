<?php

namespace App\Filament\Resources;

use App\Enums\PromotionTypeEnum;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Filament\Resources\PromotionResource\RelationManagers\ProductVariantsRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\PromotionRulesRelationManager;
use App\Filament\Resources\PromotionResource\RelationManagers\UserGroupsRelationManager;
use App\Models\Promotion;
use App\Services\LocaleCurrencyService;
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

class PromotionResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Promotion::class;
    protected static ?int $navigationSort = 102;

    public static function getLabel(): string
    {
        return __('filament.PromotionResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.PromotionResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.PromotionResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.PromotionResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.PromotionResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([

                Forms\Components\Select::make('type')
                    ->label(__('filament.promotion.type'))
                    ->options(PromotionTypeEnum::options())
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label(__('filament.promotion.starts_at'))
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label(__('filament.promotion.ends_at'))
                    ->displayFormat('Y-m-d H:i:s'),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament.promotion.active'))
                    ->required(),
                Forms\Components\Tabs::make('translations_tabs')
                    ->tabs(
                        $languages->map(function ($lang) use ($model) {
                            $translation = null;
                            if ($model && $model->exists) {
                                $translation = $model->promotionTranslations
                                    ->where('language_id', $lang->id)
                                    ->first();
                            }
                            return Forms\Components\Tabs\Tab::make($lang->name)
                                ->schema([
                                    Forms\Components\TextInput::make("translations.{$lang->id}.name")
                                        ->label(__('filament.promotion.name'))
                                        ->required($lang->is_default ?? false)
                                        ->default($translation ? $translation->name : ''),
                                    Forms\Components\Textarea::make("translations.{$lang->id}.description")
                                        ->label(__('filament.promotion.description'))
                                        ->default($translation ? $translation->description : ''),
                                ]);
                        })->toArray()
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 多语言 name 列
                Tables\Columns\TextColumn::make('promotionTranslations.name')
                    ->label(__('filament.promotion.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->promotionTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->promotionTranslations->first();
                        return $first ? $first->name : '';
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label(__('filament.promotion.type')),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('filament.promotion.starts_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('filament.promotion.ends_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.promotion.active'))
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
            ProductVariantsRelationManager::class,
            UserGroupsRelationManager::class,
            PromotionRulesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
