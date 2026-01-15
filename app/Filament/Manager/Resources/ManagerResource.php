<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ManagerResource\Pages;
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
                    ->label(__('filament.manager.avatar'))
                    ->image()
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->columnSpanFull()
                    ->required()
                    ->collection('avatars')
                    ->avatar(),
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.manager.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label(__('filament.manager.email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label(__('filament.manager.password'))
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn ($context) => $context === 'create')
                    ->confirmed(),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('filament.manager.password_confirmation'))
                    ->password()
                    ->maxLength(255)
                    ->required(fn ($context) => $context === 'create'),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('filament.manager.email_verified_at')),
                Forms\Components\TextInput::make('token')
                    ->label('API Token')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record !== null)
                    ->placeholder('点击"生成Token"按钮生成')
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generateToken')
                            ->label('生成Token')
                            ->icon('heroicon-o-key')
                            ->action(function (Manager $record, Forms\Components\TextInput $component) {
                                $token = bin2hex(random_bytes(32));
                                $record->update(['token' => $token]);
                                $component->state($token);
                                \Filament\Notifications\Notification::make()
                                    ->title('Token已生成')
                                    ->body('新Token: '.$token)
                                    ->success()
                                    ->send();
                            })
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label(__('filament.manager.avatar'))
                    ->circular()
                    ->collection('avatars')
                    ->conversion('thumb'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.manager.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.manager.email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('filament.manager.email_verified_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('token')
                    ->label('API Token')
                    ->limit(20)
                    ->copyable()
                    ->copyMessage('Token已复制')
                    ->placeholder('未生成'),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('generateToken')
                    ->label('生成Token')
                    ->icon('heroicon-o-key')
                    ->color('success')
                    ->action(function (Manager $record) {
                        $token = bin2hex(random_bytes(32));
                        $record->update(['token' => $token]);
                        \Filament\Notifications\Notification::make()
                            ->title('Token已生成')
                            ->body('新Token: '.$token)
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('生成API Token')
                    ->modalDescription('这将生成一个新的API访问令牌。旧的令牌将失效。')
                    ->modalSubmitActionLabel('确认生成'),
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ...static::getBulkActions(),
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

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // 编辑时如果密码为空则不更新
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
