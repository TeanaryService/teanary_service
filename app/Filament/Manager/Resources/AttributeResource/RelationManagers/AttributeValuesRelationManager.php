<?php

namespace App\Filament\Manager\Resources\AttributeResource\RelationManagers;

use App\Filament\Manager\Resources\AttributeValueResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttributeValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'attributeValues';

    public function form(Form $form): Form
    {
        return AttributeValueResource::form($form);
    }

    public function table(Table $table): Table
    {
        return AttributeValueResource::table($table);
    }
}
