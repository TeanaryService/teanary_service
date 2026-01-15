<?php

namespace App\Filament\Manager\Resources;

use App\Enums\ProductStatusEnum;
use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

    protected static ?string $model = Product::class;

    protected static ?int $navigationSort = 200;

    public static function getLabel(): string
    {
        return __('filament.ProductResource.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.ProductResource.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.ProductResource.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.ProductResource.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.ProductResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('product_tabs')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('basic')
                        ->label(__('filament.product.basic_info'))
                        ->schema([
                            ...static::getProductBaseFields(),
                        ]),
                    Forms\Components\Tabs\Tab::make('images')
                        ->label(__('filament.product.images'))
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('images')
                                ->label(__('filament.product.images'))
                                ->multiple()
                                ->columnSpanFull()
                                ->collection('images')
                                ->reorderable(),
                        ]),
                    Forms\Components\Tabs\Tab::make('attributes')
                        ->label(__('filament.product.attribute_values'))
                        ->schema([
                            ...static::getAttributeValuesRepeater(),
                        ]),
                    Forms\Components\Tabs\Tab::make('categories')
                        ->label(__('filament.product.categories'))
                        ->schema([
                            ...static::getProductCategoriesRepeater(),
                        ]),
                    Forms\Components\Tabs\Tab::make('variants')
                        ->label(__('filament.product.product_variants'))
                        ->schema([
                            Forms\Components\View::make('filament.forms.product-variants-manager')
                                ->columnSpanFull()
                                ->viewData(fn ($get, $livewire) => [
                                    'productId' => $livewire instanceof \Filament\Resources\Pages\EditRecord ? $livewire->record->id : null,
                                ]),
                        ])
                        ->visible(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord),
                    Forms\Components\Tabs\Tab::make('translations')
                        ->label(__('filament.product.translations'))
                        ->schema(static::getProductTranslationsTabs($form)),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function getAttributeValuesRepeater(): array
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $attributeOptions = \App\Models\Attribute::with('attributeTranslations')->get()->mapWithKeys(function ($attr) {
            $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());
            $translation = $attr->attributeTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name ? $translation->name : ($attr->attributeTranslations->first()->name ?? $attr->id);

            // 确保ID是字符串类型，以便在Select中正确匹配
            return [(string) $attr->id => $name];
        })->toArray();

        $attributeValueOptions = [];
        $allAttrValues = \App\Models\AttributeValue::with('attributeValueTranslations')->get();
        foreach ($allAttrValues as $av) {
            $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());
            $translation = $av->attributeValueTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name ? $translation->name : ($av->attributeValueTranslations->first()->name ?? $av->id);
            // 确保ID是字符串类型，以便在Select中正确匹配
            $attributeValueOptions[(string) $av->attribute_id][(string) $av->id] = $name;
        }

        return [
            Forms\Components\Repeater::make('attributeValues')
                ->label(__('filament.product.attribute_values'))
                ->schema([
                    Forms\Components\Select::make('attribute_id')
                        ->label(__('filament.product.attribute'))
                        ->options($attributeOptions)
                        ->required()
                        ->reactive()
                        ->live(),
                    Forms\Components\Select::make('attribute_value_id')
                        ->label(__('filament.product.attribute_value'))
                        ->options(function ($get) use ($attributeValueOptions) {
                            $attrId = $get('attribute_id');
                            // 确保ID是字符串类型，以便正确匹配
                            $attrId = $attrId ? (string) $attrId : null;

                            return $attrId && isset($attributeValueOptions[$attrId])
                                ? $attributeValueOptions[$attrId]
                                : [];
                        })
                        ->required()
                        ->searchable()
                        ->reactive(),
                ]),
        ];
    }

    protected static function getProductCategoriesRepeater(): array
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $categoryOptions = \App\Models\Category::with('categoryTranslations')->get()->mapWithKeys(function ($cat) {
            $lang = app(LocaleCurrencyService::class)->getLanguageByCode(app()->getLocale());
            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name ? $translation->name : ($cat->categoryTranslations->first()->name ?? $cat->id);

            // 确保ID是字符串类型，以便在Select中正确匹配
            return [(string) $cat->id => $name];
        })->toArray();

        return [
            Forms\Components\Repeater::make('productCategories')
                ->label(__('filament.product.categories'))
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label(__('filament.product.category'))
                        ->options($categoryOptions)
                        ->required()
                        ->searchable(),
                ]),
        ];
    }

    protected static function getProductBaseFields(): array
    {
        return [
            Forms\Components\TextInput::make('slug')
                ->label(__('filament.product.slug'))
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label(__('filament.product.status'))
                ->options(ProductStatusEnum::options())
                ->required(),
            Forms\Components\Select::make('translation_status')
                ->label(__('filament.product.translation_status'))
                ->options(TranslationStatusEnum::options())
                ->default(TranslationStatusEnum::NotTranslated->value)
                ->required(),
            Forms\Components\TextInput::make('source_url')
                ->label(__('filament.product.source_url'))
                ->url()
                ->maxLength(255)
                ->hidden(function ($livewire) {
                    if (! $livewire instanceof \Filament\Resources\Pages\EditRecord) {
                        return true; // 创建页面默认隐藏
                    }

                    return empty($livewire->record->source_url); // 编辑页面：如果没有值则隐藏
                }),
        ];
    }

    protected static function getProductTranslationsTabs(Form $form): array
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return [
            Forms\Components\Tabs::make('translations_tabs')
                ->tabs(
                    $languages->map(function ($lang) use ($model) {
                        $translation = null;
                        if ($model && $model->exists) {
                            $translation = $model->productTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                        }

                        return Forms\Components\Tabs\Tab::make($lang->name)
                            ->schema([
                                Forms\Components\TextInput::make("translations.{$lang->id}.name")
                                    ->label(__('filament.product.name'))
                                    ->required($lang->is_default ?? false)
                                    ->default($translation ? $translation->name : ''),

                                reusableRichEditor("translations.{$lang->id}.description", $translation ? ($translation->description ?? '') : '', __('filament.product.description'), $lang->id),

                                Forms\Components\Textarea::make("translations.{$lang->id}.short_description")
                                    ->label(__('filament.product.short_description'))
                                    ->default($translation ? $translation->short_description : ''),
                            ]);
                    })->toArray()
                )
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'productCategories.categoryTranslations',
                        'productVariants',
                        'productTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('filament.product.images'))
                    ->stacked()
                    ->collection('images')
                    ->conversion('thumb')
                    ->limit(3)
                    ->circular(),
                Tables\Columns\TextColumn::make('productTranslations.name')
                    ->label(__('filament.product.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : $record->slug;
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('productTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction) use ($lang): Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('product_translations', function ($join) use ($langId) {
                            $join->on('products.id', '=', 'product_translations.product_id')
                                ->where('product_translations.language_id', '=', $langId);
                        })
                        ->orderBy('product_translations.name', $direction)
                        ->select('products.*')
                        ->groupBy('products.id');
                    })
                    ->limit(40)
                    ->wrap(),
                Tables\Columns\TextColumn::make('categories')
                    ->label(__('filament.product.categories'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $names = [];
                        foreach ($record->productCategories as $cat) {
                            $translation = $cat->categoryTranslations->where('language_id', $lang?->id)->first();
                            $names[] = $translation && $translation->name
                                ? $translation->name
                                : ($cat->categoryTranslations->first()->name ?? '');
                        }
                        return implode(', ', array_filter($names)) ?: '-';
                    })
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_range')
                    ->label(__('filament.product.price_range'))
                    ->getStateUsing(function ($record) use ($service, $currentCurrencyCode) {
                        $variants = $record->productVariants;
                        if ($variants->isEmpty()) {
                            return '-';
                        }
                        $prices = $variants->pluck('price')->filter()->sort()->values();
                        if ($prices->isEmpty()) {
                            return '-';
                        }
                        if ($prices->count() === 1) {
                            return $service->convertWithSymbol($prices->first(), $currentCurrencyCode);
                        }
                        $min = $service->convertWithSymbol($prices->first(), $currentCurrencyCode);
                        $max = $service->convertWithSymbol($prices->last(), $currentCurrencyCode);
                        return "{$min} - {$max}";
                    })
                    ->sortable(false)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label(__('filament.product.total_stock'))
                    ->getStateUsing(function ($record) {
                        return $record->productVariants->sum('stock');
                    })
                    ->numeric()
                    ->sortable(false)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label(__('filament.product.variants_count'))
                    ->getStateUsing(function ($record) {
                        return $record->productVariants->count();
                    })
                    ->numeric()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('productVariants')
                            ->orderBy('product_variants_count', $direction);
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament.product.slug'))
                    ->searchable()
                    ->limit(20)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.product.status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        ProductStatusEnum::Active => 'success',
                        ProductStatusEnum::Inactive => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.product.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('source_url')
                    ->label(__('filament.product.source_url'))
                    ->limit(40)
                    ->url(fn ($record) => $record->source_url)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament.product.status'))
                    ->options(ProductStatusEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.product.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('filament.product.categories'))
                    ->relationship('productCategories', 'id')
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->categoryTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($record->categoryTranslations->first()->name ?? $record->id);
                    })
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\Filter::make('low_stock')
                    ->label(__('filament.product.low_stock'))
                    ->query(function (Builder $query): Builder {
                        return $query->whereIn('id', function ($subQuery) {
                            $subQuery->select('product_id')
                                ->from('product_variants')
                                ->groupBy('product_id')
                                ->havingRaw('SUM(stock) <= 10');
                        });
                    }),
                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('filament.product.out_of_stock'))
                    ->query(function (Builder $query): Builder {
                        return $query->whereIn('id', function ($subQuery) {
                            $subQuery->select('product_id')
                                ->from('product_variants')
                                ->groupBy('product_id')
                                ->havingRaw('SUM(stock) = 0');
                        });
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
                    static::getBulkEnableAction(),
                    static::getBulkDisableAction(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * 获取批量启用商品的批量操作.
     */
    public static function getBulkEnableAction(): BulkAction
    {
        return BulkAction::make('bulk_enable')
            ->label(__('filament.product.bulk_enable'))
            ->icon('heroicon-o-check-circle')
            ->action(function ($records) {
                $count = 0;
                $syncService = app(\App\Services\SyncService::class);
                $sourceNode = config('sync.node');

                // 禁用同步，避免每个 save() 都触发同步
                Product::$syncDisabled = true;

                try {
                    $models = [];
                    foreach ($records as $record) {
                        $record->status = ProductStatusEnum::Active;
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
                    Product::$syncDisabled = false;
                }

                Notification::make()
                    ->title(__('filament.product.bulk_enable_success', ['count' => $count]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }

    /**
     * 获取批量禁用商品的批量操作.
     */
    public static function getBulkDisableAction(): BulkAction
    {
        return BulkAction::make('bulk_disable')
            ->label(__('filament.product.bulk_disable'))
            ->icon('heroicon-o-x-circle')
            ->action(function ($records) {
                $count = 0;
                $syncService = app(\App\Services\SyncService::class);
                $sourceNode = config('sync.node');

                // 禁用同步，避免每个 save() 都触发同步
                Product::$syncDisabled = true;

                try {
                    $models = [];
                    foreach ($records as $record) {
                        $record->status = ProductStatusEnum::Inactive;
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
                    Product::$syncDisabled = false;
                }

                Notification::make()
                    ->title(__('filament.product.bulk_disable_success', ['count' => $count]))
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion()
            ->requiresConfirmation();
    }
}
