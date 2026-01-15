<?php

namespace App\Filament\Manager\Resources\OrderResource\RelationManagers;

use App\Filament\Manager\Resources\OrderItemResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(
                static fn (RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)
            )
            ->form(fn (Form $form): Form => $this->form($form->columns(1)));
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->form(fn (Form $form): Form => $this->form($form->columns(1)));
    }
}
