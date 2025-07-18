<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ManagerResource\Pages;
use App\Filament\Manager\Resources\ManagerResource\RelationManagers;
use App\Models\Manager;
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

class ManagerResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = Manager::class;
    protected static ?int $navigationSort = 400;

    public static function getLabel(): string
    {
        return __('filament.ManagerResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.ManagerResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.ManagerResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.ManagerResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.ManagerResource.icon');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                ...static::getTimestampsColumns()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('login')
                    ->label(__('filament_user.login'))
                    ->url(fn($record) => route('login-as', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-key'),
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
            'index' => Pages\ListManagers::route('/'),
            'create' => Pages\CreateManager::route('/create'),
            'edit' => Pages\EditManager::route('/{record}/edit'),
        ];
    }
}
