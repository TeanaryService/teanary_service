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

    protected static ?string $pluralLabel = '国家数据';
    protected static ?string $label = '国家数据';
    protected static ?int $navigationSort = 404;
    protected static ?string $navigationGroup = '系统设置';
    protected static ?string $navigationLabel = '国家数据';
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('iso_code_2')
                    ->label('二字码')
                    ->maxLength(2)
                    ->default(null),
                Forms\Components\TextInput::make('iso_code_3')
                    ->label('三字码')
                    ->maxLength(3)
                    ->default(null),
                Forms\Components\Toggle::make('postcode_required')
                    ->label('需要邮编')
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->label('启用')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('iso_code_2')
                    ->label('二字码')
                    ->searchable(),
                Tables\Columns\TextColumn::make('iso_code_3')
                    ->label('三字码')
                    ->searchable(),
                Tables\Columns\IconColumn::make('postcode_required')
                    ->label('需要邮编')
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label('启用')
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
