<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ZoneResource\Pages;
use App\Filament\Manager\Resources\ZoneResource\RelationManagers;
use App\Models\Zone;
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

class ZoneResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '地区数据';
    protected static ?string $label = '地区数据';
    protected static ?int $navigationSort = 405;
    protected static ?string $navigationGroup = '系统设置';
    protected static ?string $navigationLabel = '地区数据';
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-americas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('country_id')
                    ->label('国家')
                    ->relationship('country', 'iso_code_2')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('地区代码')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('active')
                    ->label('启用')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('country.iso_code_2')
                    ->label('国家')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('地区代码')
                    ->searchable(),
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
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
