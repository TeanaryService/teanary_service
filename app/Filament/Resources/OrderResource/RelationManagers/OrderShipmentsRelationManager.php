<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\OrderShipmentResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderShipmentsRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.order.order_shipments');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.order.order_shipments');
    }

    protected static string $relationship = 'orderShipments';

    public function form(Form $form): Form
    {
        return OrderShipmentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return OrderShipmentResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.order.order_shipments')),
            ]);
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(
                static fn(RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)
            )
            ->form(fn(Form $form): Form => $this->form($form->columns(1)));
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->form(fn(Form $form): Form => $this->form($form->columns(1)));
    }
}
