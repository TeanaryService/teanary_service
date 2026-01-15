<?php

namespace App\Filament\Manager\Resources\PromotionResource\RelationManagers;

use App\Traits\HasTranslationHelpers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductVariantsRelationManager extends RelationManager
{
    use HasTranslationHelpers;

    protected static string $relationship = 'productVariants';

    public static function getLabel(): string
    {
        return __('filament.product.product_variants');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.product.product_variants');
    }

    public function form(Form $form): Form
    {
        $lang = static::getCurrentLanguage();

        // 商品选项
        $productOptions = \App\Models\Product::with('productTranslations')->get()->mapWithKeys(function ($product) use ($lang) {
            $name = static::getTranslationName(
                $product->productTranslations,
                $lang?->id,
                'name',
                $product->id
            );

            // 确保ID是字符串类型，以便在Select中正确匹配
            return [(string) $product->id => $name];
        })->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('filament.promotion.product'))
                    ->options($productOptions)
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        $set('product_variant_id', null);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->label(__('filament.promotion.product_variant'))
                    ->options(function ($get) use ($lang) {
                        $productId = $get('product_id');
                        if (! $productId) {
                            return [];
                        }
                        $variants = \App\Models\ProductVariant::where('product_id', $productId)
                            ->with('specificationValues.specificationValueTranslations')
                            ->get();
                        $options = [];
                        foreach ($variants as $variant) {
                            $specNames = [];
                            foreach ($variant->specificationValues as $specValue) {
                                $specNames[] = static::getTranslationName(
                                    $specValue->specificationValueTranslations,
                                    $lang?->id,
                                    'name',
                                    ''
                                );
                            }
                            // 确保ID是字符串类型，以便在Select中正确匹配
                            $options[(string) $variant->id] = $specNames ? implode(' / ', array_filter($specNames)) : $variant->sku;
                        }

                        return $options;
                    })
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        $lang = static::getCurrentLanguage();

        return $table
            ->recordTitleAttribute('product_variant_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.promotion.product'))
                    ->getStateUsing(function ($record) use ($lang) {
                        // 兼容 $record->product 可能为 null
                        $product = $record->product ?? $record->productVariant?->product;
                        if (! $product) {
                            return null;
                        }
                        return static::getTranslationName(
                            $product->productTranslations,
                            $lang?->id,
                            'name',
                            $product->id
                        );
                    }),
                Tables\Columns\TextColumn::make('productVariant')
                    ->label(__('filament.promotion.product_variant'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $specNames = [];
                        foreach ($record->specificationValues as $specValue) {
                            $specNames[] = static::getTranslationName(
                                $specValue->specificationValueTranslations,
                                $lang?->id,
                                'name',
                                ''
                            );
                        }

                        return $specNames ? implode(' / ', array_filter($specNames)) : $record->sku;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.promotion.attach_product_variant'))
                    ->action(function (array $data, $livewire) {
                        // 附加（使用自定义方法以触发同步）
                        $livewire->getOwnerRecord()->attachProductVariant(
                            $data['product_variant_id'],
                            ['product_id' => $data['product_id']]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('filament.promotion.detach_product_variant'))
                    ->action(function ($record, $livewire) {
                        // 分离（使用自定义方法以触发同步）
                        $livewire->getOwnerRecord()->detachProductVariant($record->product_variant_id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('filament.promotion.detach_product_variant'))
                        ->action(function ($records, $livewire) {
                            $ids = $records->pluck('product_variant_id')->all();
                            $livewire->getOwnerRecord()->detachProductVariant($ids);
                        }),
                ]),
            ]);
    }
}
