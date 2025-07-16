<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\UserResource\Pages;
use App\Filament\Manager\Resources\UserResource\RelationManagers;
use App\Models\User;
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

class UserResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $pluralLabel = '用户管理';
    protected static ?string $label = '用户管理';
    protected static ?int $navigationSort = 301;
    protected static ?string $navigationGroup = '用户管理';
    protected static ?string $navigationLabel = '用户管理';
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('用户名')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('邮箱验证时间'),
                Forms\Components\Select::make('user_group_id')
                    ->label('用户分组')
                    ->relationship('userGroup', 'id')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('default_language_id')
                    ->label('默认语言ID')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('default_currency_id')
                    ->label('默认币种ID')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('用户名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('邮箱验证时间')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userGroup.id')
                    ->label('用户分组ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_language_id')
                    ->label('默认语言ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_currency_id')
                    ->label('默认币种ID')
                    ->numeric()
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
