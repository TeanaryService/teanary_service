<?php

namespace App\Filament\Manager\Resources\ProductResource\RelationManagers;

use App\Filament\Manager\Resources\ProductVariantResource;
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
    public static function getLabel(): string
    {
        return __('filament_product.product_variants');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament_product.product_variants');
    }

    protected static string $relationship = 'productVariants';

    public function form(Form $form): Form
    {
        return ProductVariantResource::form($form);
    }

    public function table(Table $table): Table
    {
        return ProductVariantResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament_product.product_variants')),
            ]);
    }
}
