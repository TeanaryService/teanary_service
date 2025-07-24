<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\OrderItemResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.order.order_items');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.order.order_items');
    }

    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return OrderItemResource::form($form);
    }

    public function table(Table $table): Table
    {
        return OrderItemResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.order.order_items')),
            ]);
    }
}
