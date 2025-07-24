<?php

namespace App\Filament\Resources\CartResource\RelationManagers;

use App\Filament\Resources\CartItemResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CartItemsRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.cart.cart_items');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.cart.cart_items');
    }


    protected static string $relationship = 'cartItems';

    public function form(Form $form): Form
    {
        return CartItemResource::form($form);
    }

    public function table(Table $table): Table
    {
        return CartItemResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.cart.cart_items')),
            ]);
    }
}
