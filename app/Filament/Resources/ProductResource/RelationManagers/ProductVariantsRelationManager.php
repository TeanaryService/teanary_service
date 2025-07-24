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

    protected function configureEditAction(Tables\Actions\EditAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire, Model $record): bool => (! $livewire->isReadOnly()) && $livewire->canEdit($record))
            ->form(fn(Form $form): Form => $this->form($form->columns(2)))
            ->mutateRecordDataUsing(function (array $data, Model $record) {
                $data['specificationValues'] = $record->specificationValues()
                    ->with('specification') // 确保有 specification 可访问
                    ->get()
                    ->map(function ($value) {
                        return [
                            'specification_id' => $value->specification_id,
                            'specification_value_id' => $value->id,
                        ];
                    })
                    ->toArray();

                return $data;
            });
    }
}
