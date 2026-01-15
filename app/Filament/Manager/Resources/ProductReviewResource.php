<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ProductReviewResource\Pages;
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
                Forms\Components\Section::make(__('filament.product_review.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label(__('filament.product_review.product'))
                            ->relationship('product', 'id', function ($query) {
                                return $query->with('productTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                                $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                                $name = $translation && $translation->name
                                    ? $translation->name
                                    : ($record->productTranslations->first()->name ?? $record->slug);
                                return $name;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1)
                            ->reactive()
                            ->afterStateUpdated(function ($set) {
                                $set('product_variant_id', null);
                            })
                            ->helperText(__('filament.product_review.product_helper')),
                        Forms\Components\Select::make('product_variant_id')
                            ->label(__('filament.product_review.variant'))
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
                                    $specs = $variant->specificationValues->map(function ($sv) use ($lang) {
                                        $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                        return $trans && $trans->name ? $trans->name : $sv->id;
                                    })->implode(' / ');
                                    $options[(string) $variant->id] = $specs ?: ($variant->sku ?? $variant->id);
                                }
                                return $options;
                            })
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->columnSpan(1)
                            ->reactive()
                            ->helperText(__('filament.product_review.variant_helper')),
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.product_review.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('rating')
                            ->label(__('filament.product_review.rating'))
                            ->required()
                            ->maxValue(5)
                            ->minValue(1)
                            ->numeric()
                            ->default(5)
                            ->columnSpan(1)
                            ->helperText(__('filament.product_review.rating_helper')),
                        Forms\Components\Toggle::make('is_approved')
                            ->label(__('filament.product_review.is_approved'))
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('filament.product_review.content'))
                    ->schema([
                        Forms\Components\Textarea::make('content')
                            ->label(__('filament.product_review.content'))
                            ->rows(5)
                            ->columnSpanFull()
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('filament.product_review.images'))
                            ->multiple()
                            ->columnSpanFull()
                            ->collection('images')
                            ->reorderable()
                            ->helperText(__('filament.product_review.images_helper')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = app(\App\Services\LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->with([
                        'product.productTranslations',
                        'productVariant.specificationValues.specificationValueTranslations',
                        'user',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('filament.product_review.images'))
                    ->stacked()
                    ->collection('images')
                    ->conversion('thumb')
                    ->limit(3)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('filament.product_review.product'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $product = $record->product;
                        if (! $product) {
                            return null;
                        }
                        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                        return $translation && $translation->name
                            ? $translation->name
                            : ($product->productTranslations->first()->name ?? $product->slug);
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('product.productTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('productVariant.specifications')
                    ->label(__('filament.product_review.variant'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $variant = $record->productVariant;
                        if (! $variant) {
                            return __('filament.product_review.no_variant');
                        }
                        $specs = $variant->specificationValues->map(function ($sv) use ($lang) {
                            $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                            return $trans && $trans->name ? $trans->name : $sv->id;
                        })->implode(' / ');
                        return $specs ?: ($variant->sku ?? $variant->id);
                    })
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.product_review.user'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rating')
                    ->label(__('filament.product_review.rating'))
                    ->numeric()
                    ->icon('heroicon-o-star')
                    ->iconColor(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('filament.product_review.content'))
                    ->limit(100)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('filament.product_review.is_approved'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('filament.product_review.product'))
                    ->relationship('product', 'id', function ($query) {
                        return $query->with('productTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('rating')
                    ->label(__('filament.product_review.rating'))
                    ->options([
                        5 => '5 ⭐',
                        4 => '4 ⭐',
                        3 => '3 ⭐',
                        2 => '2 ⭐',
                        1 => '1 ⭐',
                    ])
                    ->multiple(),
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label(__('filament.product_review.is_approved'))
                    ->options([
                        1 => __('filament.product_review.approved'),
                        0 => __('filament.product_review.pending'),
                    ]),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
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
            'index' => Pages\ListProductReviews::route('/'),
            'create' => Pages\CreateProductReview::route('/create'),
            'edit' => Pages\EditProductReview::route('/{record}/edit'),
        ];
    }
}
