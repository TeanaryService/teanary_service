<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\AttributeResource\Pages;
use App\Filament\Manager\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Models\Attribute;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

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
                Forms\Components\Section::make(__('filament.attribute.basic_info'))
                    ->schema([
                        Forms\Components\Toggle::make('is_filterable')
                            ->label(__('filament.attribute.is_filterable'))
                            ->default(false)
                            ->helperText(__('filament.attribute.is_filterable_helper'))
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.attribute.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('filament.attribute.translations'))
                    ->schema([
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
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($lang->is_default ? __('filament.attribute.name_helper') : null);
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
                        'attributeTranslations',
                        'attributeValues',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.attribute.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->attributeTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->attributeTranslations->first();
                        return $first ? $first->name : __('filament.attribute.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('attributeTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('attribute_translations', function ($join) use ($langId) {
                            $join->on('attributes.id', '=', 'attribute_translations.attribute_id')
                                ->where('attribute_translations.language_id', '=', $langId);
                        })
                        ->orderBy('attribute_translations.name', $direction)
                        ->select('attributes.*')
                        ->groupBy('attributes.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('values_count')
                    ->label(__('filament.attribute.values_count'))
                    ->getStateUsing(function ($record) {
                        return $record->attributeValues->count();
                    })
                    ->numeric()
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->withCount('attributeValues')
                            ->orderBy('attribute_values_count', $direction);
                    })
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.attribute.translation_status'))
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
                Tables\Filters\SelectFilter::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->options([
                        1 => __('filament.attribute.filterable'),
                        0 => __('filament.attribute.not_filterable'),
                    ]),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.attribute.translation_status'))
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
                    static::getIsFilterableBulkAction(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
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

    /**
     * 获取前台是否可见的批量操作.
     */
    public static function getIsFilterableBulkAction(): BulkAction
    {
        return BulkAction::make('set_is_filterable')
            ->label(__('filament.attribute.bulk_set_filterable'))
            ->icon('heroicon-o-eye')
            ->form([
                Toggle::make('is_filterable')
                    ->label(__('filament.attribute.is_filterable'))
                    ->default(true)
                    ->required(),
            ])
            ->action(function ($records, array $data) {
                $isFilterable = (bool) $data['is_filterable'];
                $count = 0;
                $syncService = app(\App\Services\SyncService::class);
                $sourceNode = config('sync.node');

                // 禁用同步，避免每个 save() 都触发同步
                Attribute::$syncDisabled = true;

                try {
                    $models = [];
                    foreach ($records as $record) {
                        $record->is_filterable = $isFilterable;
                        $record->save();
                        $models[] = ['model' => $record, 'action' => 'updated'];
                        ++$count;
                    }

                    // 批量记录同步
                    if (! empty($models)) {
                        $syncService->recordBatchSync($models, $sourceNode);
                    }
                } finally {
                    // 重新启用同步
                    Attribute::$syncDisabled = false;
                }

                // 清除属性缓存
                Cache::forget('attributes.with.translations');

                Notification::make()
                    ->title(__('filament.attribute.bulk_set_filterable_success', ['count' => $count]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }
}
