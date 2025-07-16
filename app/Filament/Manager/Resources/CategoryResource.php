<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CategoryResource\Pages;
use App\Filament\Manager\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
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

class CategoryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Category::class;

    public static function getLabel(): string
    {
        return __('filament.CategoryResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CategoryResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CategoryResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CategoryResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CategoryResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.CategoryResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('parent_id')
                    ->label(__('filament_category.parent_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('slug')
                    ->label(__('filament_category.slug'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('parent_id')
                    ->label(__('filament_category.parent_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament_category.slug'))
                    ->searchable(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
