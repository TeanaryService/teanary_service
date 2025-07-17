<?php

namespace App\Filament\Manager\Resources\AttributeResource\RelationManagers;

use App\Filament\Manager\Resources\AttributeValueResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttributeValuesRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament_attribute.attribute_values');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament_attribute.attribute_values');
    }

    protected static string $relationship = 'attributeValues';

    public function form(Form $form): Form
    {
        return AttributeValueResource::form($form);
    }

    public function table(Table $table): Table
    {
        return AttributeValueResource::table($table)
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament_attribute.attribute_values')),
            ]);
    }
}
