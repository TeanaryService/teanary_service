<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\ProductVariantResource;
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
        return __('filament.product.product_variants');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.product.product_variants');
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
                    ->label(__('filament.product.product_variants')),
            ]);
    }

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(
                static fn(RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record)
            )
            ->form(fn(Form $form): Form => $this->form($form->columns(1)))
            ->mutateRecordDataUsing(function (array $data, Model $record): array {
                $specificationValues = [];

                if ($record->specificationValues) {
                    foreach ($record->specificationValues as $specificationValue) {
                        $specificationValues[] = [
                            'specification_id' => $specificationValue->specification_id,
                            'specification_value_id' => $specificationValue->pivot->specification_value_id,
                        ];
                    }
                }

                $data['specificationValues'] = $specificationValues;

                return $data;
            })
            ->after(function (Model $record, array $data): void {
                if (isset($data['specificationValues'])) {
                    $pivotData = [];
                    foreach ($data['specificationValues'] as $specificationValue) {
                        $pivotData[] = $specificationValue;
                    }
                    $record->specificationValues()->sync($pivotData);
                } else {
                    $record->specificationValues()->sync([]);
                }
            });
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->form(fn(Form $form): Form => $this->form($form->columns(1)))
            ->after(function (Model $record, array $data): void {
                $pivotData = [];
                foreach ($data['specificationValues'] ?? [] as $specificationValue) {
                    $pivotData[] = $specificationValue;
                }
                $record->specificationValues()->sync($pivotData);
            });
    }
}
