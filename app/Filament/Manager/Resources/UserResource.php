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

    protected static ?string $model = User::class;

    public static function getLabel(): string
    {
        return __('filament.UserResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.UserResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.UserResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.UserResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.UserResource.icon');
    }
    public static function getNavigationSort(): int
    {
        return (int) __('filament.UserResource.sort');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('filament_user.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament_user.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label(__('filament_user.password'))
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('filament_user.email_verified_at')),
                Forms\Components\Select::make('user_group_id')
                    ->label(__('filament_user.user_group_id'))
                    ->relationship('userGroup', 'id')
                    ->searchable()
                    ->preload()
                    ->default(null),
                Forms\Components\TextInput::make('default_language_id')
                    ->label(__('filament_user.default_language_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('default_currency_id')
                    ->label(__('filament_user.default_currency_id'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament_user.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament_user.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('filament_user.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('userGroup.id')
                    ->label(__('filament_user.userGroup.id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_language_id')
                    ->label(__('filament_user.default_language_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_currency_id')
                    ->label(__('filament_user.default_currency_id'))
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
