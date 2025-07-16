<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\AddressResource\Pages;
use App\Filament\Manager\Resources\AddressResource\RelationManagers;
use App\Models\Address;
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

class AddressResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '收货地址';
    protected static ?string $label = '收货地址';
    protected static ?int $navigationSort = 302;
    protected static ?string $navigationGroup = '用户管理';
    protected static ?string $navigationLabel = '收货地址';
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('用户')
                    ->relationship('user', 'name')
                    ->default(null),
                Forms\Components\TextInput::make('firstname')
                    ->label('名')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('lastname')
                    ->label('姓')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('telephone')
                    ->label('电话')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('company')
                    ->label('公司')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address_1')
                    ->label('地址1')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('address_2')
                    ->label('地址2')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('city')
                    ->label('城市')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('postcode')
                    ->label('邮编')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('country_id')
                    ->label('国家')
                    ->relationship('country', 'iso_code_2')
                    ->default(null),
                Forms\Components\Select::make('zone_id')
                    ->label('地区')
                    ->relationship('zone', 'code')
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('用户')
                    ->sortable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->label('名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->label('姓')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->label('电话')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->label('公司')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_1')
                    ->label('地址1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address_2')
                    ->label('地址2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('城市')
                    ->searchable(),
                Tables\Columns\TextColumn::make('postcode')
                    ->label('邮编')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country.iso_code_2')
                    ->label('国家')
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone.code')
                    ->label('地区')
                    ->sortable(),
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
            'index' => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit' => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
