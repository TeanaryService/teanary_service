<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\CountryResource\Pages;
use App\Filament\Manager\Resources\CountryResource\RelationManagers;
use App\Models\Country;
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

class CountryResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Country::class;

    public static function getLabel(): string
    {
        return __('filament.CountryResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.CountryResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.CountryResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.CountryResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.CountryResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.CountryResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('iso_code_2')
                    ->label(__('filament_country.iso_code_2'))
                    ->maxLength(2)
                    ->default(null),
                Forms\Components\TextInput::make('iso_code_3')
                    ->label(__('filament_country.iso_code_3'))
                    ->maxLength(3)
                    ->default(null),
                Forms\Components\Toggle::make('postcode_required')
                    ->label(__('filament_country.postcode_required'))
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label(__('filament_country.active'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->label(__('filament_country.iso_code_2'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso_code_3')
                    ->label(__('filament_country.iso_code_3'))
                    ->searchable(),
                Tables\Columns\IconColumn::make('postcode_required')
                    ->label(__('filament_country.postcode_required'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('filament_country.active'))
                    ->boolean(),
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
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
