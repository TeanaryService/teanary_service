<?php

namespace App\Filament\Manager\Resources;

use App\Enums\ProductStatusEnum;
use App\Filament\Manager\Resources\ProductResource\Pages;
use App\Filament\Manager\Resources\ProductResource\RelationManagers;
use App\Models\Product;
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
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;

class ProductResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Product::class;

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
    public static function getNavigationSort(): int
    {
        return (int) __('filament.ProductResource.sort');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();

        return $form
            ->schema([

                Forms\Components\TextInput::make('slug')
                    ->label(__('filament_product.slug'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('filament_product.status'))
                    ->options(ProductStatusEnum::options())
                    ->required(),

                Tabs::make('translations_tabs')
                    ->tabs(
                        $languages->map(function ($lang) use ($model) {
                            $translation = null;
                            if ($model && $model->exists) {
                                $translation = $model->productTranslations
                                    ->where('language_id', $lang->id)
                                    ->first();
                            }
                            return Tabs\Tab::make($lang->name)
                                ->schema([
                                    Forms\Components\TextInput::make("translations.{$lang->id}.name")
                                        ->label(__('filament_product.name'))
                                        ->required($lang->is_default ?? false)
                                        ->default($translation ? $translation->name : ''),
                                    Textarea::make("translations.{$lang->id}.description")
                                        ->label(__('filament_product.description'))
                                        ->default($translation ? $translation->description : ''),
                                    Textarea::make("translations.{$lang->id}.short_description")
                                        ->label(__('filament_product.short_description'))
                                        ->default($translation ? $translation->short_description : ''),
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
                Tables\Columns\TextColumn::make('productTranslations.name')
                    ->label(__('filament_product.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->productTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->productTranslations->first();
                        return $first ? $first->name : '';
                    }),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament_product.slug'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state): string => $state->label())
                    ->label(__('filament_product.status')),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
