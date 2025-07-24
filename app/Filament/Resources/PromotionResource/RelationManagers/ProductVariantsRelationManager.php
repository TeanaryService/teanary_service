<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Models\ProductVariant;
use App\Services\LocaleCurrencyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductVariantsRelationManager extends RelationManager
{
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
        $service = app(LocaleCurrencyService::class);
        $lang = $service->getLanguageByCode(app()->getLocale());

        // 商品选项
        $productOptions = \App\Models\Product::with('productTranslations')->get()->mapWithKeys(function ($product) use ($lang) {
            $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name ? $translation->name : ($product->productTranslations->first()->name ?? $product->id);
            return [$product->id => $name];
        })->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label(__('filament_promotion.product'))
                    ->options($productOptions)
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        $set('product_variant_id', null);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->label(__('filament_promotion.product_variant'))
                    ->options(function ($get) use ($lang) {
                        $productId = $get('product_id');
                        if (!$productId) {
                            return [];
                        }
                        $variants = \App\Models\ProductVariant::where('product_id', $productId)
                            ->with('specificationValues.specificationValueTranslations')
                            ->get();
                        $options = [];
                        foreach ($variants as $variant) {
                            $specNames = [];
                            foreach ($variant->specificationValues as $specValue) {
                                $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                $specNames[] = $translation && $translation->name
                                    ? $translation->name
                                    : ($specValue->specificationValueTranslations->first()->name ?? '');
                            }
                            $options[$variant->id] = $specNames ? implode(' / ', array_filter($specNames)) : $variant->sku;
                        }
                        return $options;
                    })
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $lang = $service->getLanguageByCode(app()->getLocale());

        return $table
            ->recordTitleAttribute('product_variant_id')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament_promotion.product'))
                    ->getStateUsing(function ($record) use ($lang) {
                        // 兼容 $record->product 可能为 null
                        $product = $record->product ?? $record->productVariant?->product;
                        if (!$product) return null;
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($product->productTranslations->first()->name ?? $product->id);
                    }),
                Tables\Columns\TextColumn::make('productVariant')
                    ->label(__('filament_promotion.product_variant'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $specNames = [];
                        foreach ($record->specificationValues as $specValue) {
                            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            $specNames[] = $translation && $translation->name
                                ? $translation->name
                                : ($specValue->specificationValueTranslations->first()->name ?? '');
                        }
                        return $specNames ? implode(' / ', array_filter($specNames)) : $record->sku;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament_promotion.attach_product_variant'))
                    ->action(function (array $data, $livewire) {
                        // 附加
                        $livewire->getOwnerRecord()->productVariants()->attach(
                            $data['product_variant_id'],
                            ['product_id' => $data['product_id']]
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('filament_promotion.detach_product_variant'))
                    ->action(function ($record, $livewire) {
                        // 分离
                        $livewire->getOwnerRecord()->productVariants()->detach($record->product_variant_id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('filament_promotion.detach_product_variant'))
                        ->action(function ($records, $livewire) {
                            $ids = $records->pluck('product_variant_id')->all();
                            $livewire->getOwnerRecord()->productVariants()->detach($ids);
                        }),
                ]),
            ]);
    }
}
