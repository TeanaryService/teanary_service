<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductVariantResource\Pages;
use App\Filament\Manager\Resources\ProductVariantResource\RelationManagers;
use App\Models\ProductVariant;
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

class ProductVariantResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ProductVariant::class;

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
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ProductVariantResource.sort');
    }

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        // 获取所有规格值及多语言名
        $specValues = \App\Models\SpecificationValue::with('specificationValueTranslations')->get();
        $specValueOptions = [];
        foreach ($specValues as $sv) {
            $translation = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
            $specValueOptions[$sv->id] = $translation && $translation->name
                ? $translation->name
                : ($sv->specificationValueTranslations->first()->name ?? $sv->id);
        }

        return $form
            ->schema([
                Repeater::make('specificationValues')
                    ->label(__('filament_product_variant.specification_values'))
                    // 不要加 ->relationship()，Repeater 只做表单收集，不自动保存关联
                    ->relationship()
                    ->schema([
                        Select::make('id')
                            ->label(__('filament_product_variant.specification_value'))
                            ->options(function ($get) use ($specValueOptions) {
                                $all = $get('../../specificationValues') ?? [];
                                $currentId = $get('id');
                                $usedIds = [];
                                foreach ($all as $item) {
                                    if (isset($item['id']) && $item['id'] !== $currentId) {
                                        $usedIds[] = $item['id'];
                                    }
                                }
                                return collect($specValueOptions)->except($usedIds)->toArray();
                            })
                            ->required(),
                        // 可以加更多字段，如排序、备注等
                        // Forms\Components\TextInput::make('remark')->label('备注'),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Select::make('product_id')
                    ->label(__('filament_product_variant.product_id'))
                    ->relationship('product', 'id')
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
                    ->label(__('filament_product_variant.sku'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->label(__('filament_product_variant.price'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('cost')
                    ->label(__('filament_product_variant.cost'))
                    ->numeric()
                    ->default(null)
                    ->prefix('￥'),
                Forms\Components\TextInput::make('stock')
                    ->label(__('filament_product_variant.stock'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('weight')
                    ->label(__('filament_product_variant.weight'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('length')
                    ->label(__('filament_product_variant.length'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('width')
                    ->label(__('filament_product_variant.width'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('height')
                    ->label(__('filament_product_variant.height'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    
    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament_product_variant.product_id'))
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
                    ->label(__('filament_product_variant.sku'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament_product_variant.price'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label(__('filament_product_variant.cost'))
                    ->money('CNY')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('filament_product_variant.stock'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label(__('filament_product_variant.weight'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('length')
                    ->label(__('filament_product_variant.length'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('width')
                    ->label(__('filament_product_variant.width'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->label(__('filament_product_variant.height'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specificationValues')
                    ->label(__('filament_product_variant.specification_values'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $names = [];
                        foreach ($record->specificationValues as $sv) {
                            $translation = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $names[] = $translation && $translation->name
                                ? $translation->name
                                : ($sv->specificationValueTranslations->first()->name ?? '');
                        }
                        return implode(', ', array_filter($names));
                    }),
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

    // public static function mutateFormDataBeforeSave(array $data): array
    // {
    //     // 只保留主表字段，specificationValues 交由 afterSave 处理
    //     if (isset($data['specificationValues'])) {
    //         // 只取id，且为 int
    //         $data['specificationValues'] = collect($data['specificationValues'])
    //             ->pluck('id')
    //             ->filter(fn($id) => !empty($id))
    //             ->unique()
    //             ->map(fn($id) => (int)$id)
    //             ->values()
    //             ->toArray();
    //     }
    //     return $data;
    // }

    // public static function afterSave($record, array $data): void
    // {
    //     if (isset($data['specificationValues'])) {
    //         // 只同步已存在的规格值ID，不创建新规格值
    //         $record->specificationValues()->sync($data['specificationValues']);
    //     }
    // }
}
