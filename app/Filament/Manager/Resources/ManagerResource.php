<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ManagerResource\Pages;
use App\Filament\Manager\Resources\ManagerResource\RelationManagers;
use App\Models\Manager;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
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
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->label(__('filament_manager.avatar'))
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->columnSpanFull()
                    ->required()
                    ->collection('avatars')
                    ->avatar(),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament_manager.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament_manager.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label(__('filament_manager.password'))
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => $state ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn($context) => $context === 'create')
                    ->confirmed(),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('filament_manager.password_confirmation'))
                    ->password()
                    ->maxLength(255)
                    ->required(fn($context) => $context === 'create'),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('filament_manager.email_verified_at')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label(__('filament_manager.avatar'))
                    ->circular()
                    ->collection('avatars')
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament_manager.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament_manager.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('filament_manager.email_verified_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
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
            'index' => getFilamentUrl(Pages\ListManagers::class, '/'),
            'create' => getFilamentUrl(Pages\CreateManager::class, '/create'),
            'edit' => getFilamentUrl(Pages\EditManager::class, '/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // 编辑时如果密码为空则不更新
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
    }
}
