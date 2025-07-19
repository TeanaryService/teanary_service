<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductReviewResource\Pages;
use App\Filament\Manager\Resources\ProductReviewResource\RelationManagers;
use App\Models\ProductReview;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductReviewResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = ProductReview::class;
    protected static ?int $navigationSort = 204;

    public static function getLabel(): string
    {
        return __('filament.ProductReviewResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ProductReviewResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ProductReviewResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ProductReviewResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ProductReviewResource.icon');
    }

    public static function form(Form $form): Form
    {
        $locale = app()->getLocale();
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);

        return $form
            ->schema([
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('filament_product_review.images'))
                    ->multiple()
                    ->columnSpanFull()
                    ->collection('images')
                    ->reorderable(),
                Forms\Components\Select::make('product_id')
                    ->label(__('filament_product_review.product'))
                    ->options(
                        \App\Models\Product::with('productTranslations')->get()->mapWithKeys(function ($product) use ($lang) {
                            $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                            $name = $translation && $translation->name
                                ? $translation->name
                                : ($product->productTranslations->first()->name ?? $product->slug);
                            return [$product->id => $name];
                        })->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($set) {
                        $set('product_variant_id', null);
                    }),
                Forms\Components\Select::make('product_variant_id')
                    ->label(__('filament_product_review.product_variant'))
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
                            $specs = $variant->specificationValues->map(function ($sv) use ($lang) {
                                $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                return $trans && $trans->name ? $trans->name : $sv->id;
                            })->implode(' / ');
                            $options[$variant->id] = $specs ?: $variant->sku;
                        }
                        return $options;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null)
                    ->reactive(),
                Forms\Components\Select::make('user_id')
                    ->label(__('filament_product_review.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('rating')
                    ->label(__('filament_product_review.rating'))
                    ->required()
                    ->maxValue(5)
                    ->minValue(1)
                    ->numeric()
                    ->default(5),
                Forms\Components\Textarea::make('content')
                    ->label(__('filament_product_review.content'))
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved')
                    ->label(__('filament_product_review.is_approved'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->query(
                fn() => static::getEloquentQuery()
                    ->with([
                        'product.productTranslations'
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('filament_product_review.images'))
                    ->stacked()
                    ->collection('images')
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament_product_review.product'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $product = $record->product;
                        if (!$product) return null;
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($product->productTranslations->first()->name ?? $product->slug);
                    }),
                Tables\Columns\TextColumn::make('productVariant.sku')
                    ->label(__('filament_product_review.product_variant'))
                    ->getStateUsing(function ($record) {
                        $variant = $record->productVariant;
                        if (!$variant) return null;
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $specs = $variant->specificationValues->map(function ($sv) use ($lang) {
                            $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            return $trans && $trans->name ? $trans->name : $sv->id;
                        })->implode(' / ');
                        return $specs ?: $variant->sku;
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament_product_review.user')),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('filament_product_review.rating'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('filament_product_review.is_approved'))
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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
