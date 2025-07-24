<?php

namespace App\Filament\Resources\SpecificationResource\RelationManagers;

use App\Filament\Resources\SpecificationValueResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecificationValuesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament_specification.specification_values');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament_specification.specification_values');
    }

    protected static string $relationship = 'specificationValues';

    public function form(Form $form): Form
    {
        return SpecificationValueResource::form($form);
    }

    public function table(Table $table): Table
    {
        return SpecificationValueResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament_specification.specification_values')),
            ]);;
    }
}
