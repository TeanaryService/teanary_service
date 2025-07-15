<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ShippingMethodResource\Pages;
use App\Filament\Manager\Resources\ShippingMethodResource\RelationManagers;
use App\Models\ShippingMethod;
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

class ShippingMethodResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '物流方式';
    protected static ?string $label = '物流方式';
    protected static ?int $navigationSort = 403;
    protected static ?string $navigationGroup = '系统设置';
    protected static ?string $navigationLabel = '物流方式';
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('物流方式代码')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('active')
                    ->label('是否启用')
                    ->required(),
                Forms\Components\TextInput::make('api_url')
                    ->label('接口地址')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('物流方式代码')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('是否启用')
                    ->boolean(),
                Tables\Columns\TextColumn::make('api_url')
                    ->label('接口地址')
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
            'index' => Pages\ListShippingMethods::route('/'),
            'create' => Pages\CreateShippingMethod::route('/create'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }
}
