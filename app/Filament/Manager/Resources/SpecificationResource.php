<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\SpecificationResource\Pages;
use App\Filament\Manager\Resources\SpecificationResource\RelationManagers;
use App\Models\Specification;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecificationResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Specification::class;

    public static function getLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.SpecificationResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.SpecificationResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.SpecificationResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.SpecificationResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.SpecificationResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                ...static::getActions()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions()
                ]),
            ]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecifications::route('/'),
            'create' => Pages\CreateSpecification::route('/create'),
            'edit' => Pages\EditSpecification::route('/{record}/edit'),
        ];
    }
}
