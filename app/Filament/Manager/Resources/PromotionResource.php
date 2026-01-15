<?php

namespace App\Filament\Manager\Resources;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\PromotionResource\Pages;
use App\Filament\Manager\Resources\PromotionResource\RelationManagers\ProductVariantsRelationManager;
use App\Filament\Manager\Resources\PromotionResource\RelationManagers\PromotionRulesRelationManager;
use App\Filament\Manager\Resources\PromotionResource\RelationManagers\UserGroupsRelationManager;
use App\Models\Promotion;
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

class PromotionResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

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
                Forms\Components\Section::make(__('filament.promotion.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('filament.promotion.type'))
                            ->options(PromotionTypeEnum::options())
                            ->required()
                            ->columnSpan(1)
                            ->helperText(__('filament.promotion.type_helper')),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.promotion.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('filament.promotion.starts_at'))
                            ->displayFormat('Y-m-d H:i:s')
                            ->columnSpan(1),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label(__('filament.promotion.ends_at'))
                            ->displayFormat('Y-m-d H:i:s')
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('active')
                            ->label(__('filament.promotion.active'))
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1)
                            ->helperText(__('filament.promotion.active_helper')),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('filament.promotion.translations'))
                    ->schema([
                        Forms\Components\Tabs::make('translations_tabs')
                            ->tabs(
                                $languages->map(function ($language) use ($model) {
                                    $translation = null;
                                    if ($model && $model->exists) {
                                        $translation = $model->promotionTranslations
                                            ->where('language_id', $language->id)
                                            ->first();
                                    }

                                    return Forms\Components\Tabs\Tab::make($language->name)
                                        ->schema([
                                            Forms\Components\TextInput::make("translations.{$language->id}.name")
                                                ->label(__('filament.promotion.name'))
                                                ->required($language->is_default ?? false)
                                                ->maxLength(255)
                                                ->default($translation ? $translation->name : '')
                                                ->columnSpanFull(),
                                            Forms\Components\Textarea::make("translations.{$language->id}.description")
                                                ->label(__('filament.promotion.description'))
                                                ->rows(4)
                                                ->default($translation ? $translation->description : '')
                                                ->columnSpanFull(),
                                        ]);
                                })->toArray()
                            )
                            ->columnSpanFull(),
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
                        'promotionTranslations',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.promotion.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->promotionTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->promotionTranslations->first();
                        return $first ? $first->name : __('filament.promotion.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('promotionTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('promotion_translations', function ($join) use ($langId) {
                            $join->on('promotions.id', '=', 'promotion_translations.promotion_id')
                                ->where('promotion_translations.language_id', '=', $langId);
                        })
                        ->orderBy('promotion_translations.name', $direction)
                        ->select('promotions.*')
                        ->groupBy('promotions.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.promotion.type'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('filament.promotion.starts_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('filament.promotion.ends_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament.promotion.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.promotion.translation_status'))
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
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament.promotion.type'))
                    ->options(PromotionTypeEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('active')
                    ->label(__('filament.promotion.active'))
                    ->options([
                        1 => __('filament.promotion.active'),
                        0 => __('filament.promotion.inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.promotion.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
                Tables\Filters\Filter::make('starts_at')
                    ->form([
                        Forms\Components\DatePicker::make('starts_from')
                            ->label(__('filament.promotion.starts_from')),
                        Forms\Components\DatePicker::make('starts_until')
                            ->label(__('filament.promotion.starts_until')),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['starts_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('starts_at', '>=', $date),
                            )
                            ->when(
                                $data['starts_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('starts_at', '<=', $date),
                            );
                    }),
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
            ProductVariantsRelationManager::class,
            UserGroupsRelationManager::class,
            PromotionRulesRelationManager::class,
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
