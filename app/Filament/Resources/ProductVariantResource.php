<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\RelationManagers\ProductVariantsRelationManager;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Filament\Resources\ProductVariantResource\RelationManagers;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\SpecificationValue;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProductVariantResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ProductVariant::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 201;

    public static function getLabel(): string
    {
        return __('filament.ProductVariantResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ProductVariantResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ProductVariantResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ProductVariantResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ProductVariantResource.icon');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        // 规格选项
        $specs = Specification::with('specificationTranslations')->get();
        $specOptions = [];
        foreach ($specs as $spec) {
            $translation = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
            $specOptions[$spec->id] = $translation && $translation->name
                ? $translation->name
                : ($spec->specificationTranslations->first()->name ?? $spec->id);
        }

        // 规格值选项（按规格分组）
        $specValueOptions = [];
        $allSpecValues = SpecificationValue::with('specificationValueTranslations')->get();
        foreach ($allSpecValues as $sv) {
            $translation = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name
                ? $translation->name
                : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
            $specValueOptions[$sv->specification_id][$sv->id] = $name;
        }

        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('filament.product_variant.image'))
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->columnSpanFull()
                    ->required()
                    ->collection('image'),
                Repeater::make('specificationValues')
                    ->label(__('filament.product_variant.specification_values'))
                    ->schema([
                        Select::make('specification_id')
                            ->label(__('filament.product_variant.specification'))
                            ->options(function ($get) use ($specOptions) {
                                // 禁止重复选择同一规格
                                $all = $get('../../specificationValues') ?? [];
                                $currentId = $get('specification_id');
                                $usedIds = [];
                                foreach ($all as $item) {
                                    if (isset($item['specification_id']) && $item['specification_id'] !== $currentId) {
                                        $usedIds[] = $item['specification_id'];
                                    }
                                }
                                return collect($specOptions)->except($usedIds)->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // 用户变更时，清空收货地址和帐单地址
                                $set('specification_value_id', null);
                            })
                            ->required()
                            ->reactive(),
                        Select::make('specification_value_id')
                            ->label(__('filament.product_variant.specification_value'))
                            ->options(function ($get) use ($specValueOptions) {
                                $specId = $get('specification_id');
                                return $specId && isset($specValueOptions[$specId])
                                    ? $specValueOptions[$specId]
                                    : [];
                            })
                            ->required()
                            ->searchable(),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Select::make('product_id')
                    ->label(__('filament.product_variant.product_id'))
                    ->relationship('product', 'id')
                    ->hiddenOn([ProductVariantsRelationManager::class])
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('sku')
                    ->label(__('filament.product_variant.sku'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('filament.product_variant.price'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('cost')
                    ->label(__('filament.product_variant.cost'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('stock')
                    ->label(__('filament.product_variant.stock'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('weight')
                    ->label(__('filament.product_variant.weight'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('length')
                    ->label(__('filament.product_variant.length'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('width')
                    ->label(__('filament.product_variant.width'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('height')
                    ->label(__('filament.product_variant.height'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn(Builder $query): Builder => $query
                    ->with([
                        'specifications.specificationTranslations',
                        'specificationValues.specificationValueTranslations',
                        'specificationValues.specification.specificationTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('filament.product_variant.image'))
                    ->collection('image')
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.product_variant.product_id'))
                    ->hiddenOn([ProductVariantsRelationManager::class])
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $product = $record->product;
                        if (!$product) return null;
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $product->productTranslations->first();
                        return $first ? $first->name : $product->id;
                    }),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('filament.product_variant.sku'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.product_variant.price'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label(__('filament.product_variant.cost'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('filament.product_variant.stock'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label(__('filament.product_variant.weight'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('length')
                    ->label(__('filament.product_variant.length'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('width')
                    ->label(__('filament.product_variant.width'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->label(__('filament.product_variant.height'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('specificationValues')
                    ->label(__('filament.product_variant.specification_values'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $items = [];
                        foreach ($record->specificationValues as $sv) {
                            $spec = $sv->specification;
                            $specName = '';
                            if ($spec) {
                                $specTrans = $spec->specificationTranslations->where('language_id', $lang?->id)->first();
                                $specName = $specTrans && $specTrans->name
                                    ? $specTrans->name
                                    : ($spec->specificationTranslations->first()->name ?? '');
                            }
                            $valTrans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $valName = $valTrans && $valTrans->name
                                ? $valTrans->name
                                : ($sv->specificationValueTranslations->first()->name ?? '');
                            $items[] = $specName . ':' . $valName;
                        }
                        return implode('，', array_filter($items));
                    })
                    ->limit(46),
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
