<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\UserGroupResource\RelationManagers\UsersRelationManager;
use App\Filament\Manager\Resources\UserResource\Pages;
use App\Filament\Manager\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Services\LocaleCurrencyService;
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
    protected static ?int $navigationSort = 300;

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

    public static function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);

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
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => !empty($state))
                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->same('password_confirmation')
                    ->autocomplete('new-password'),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(__('filament_user.password_confirmation'))
                    ->password()
                    ->maxLength(255)
                    ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->dehydrated(false),
                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label(__('filament_user.email_verified_at')),
                Forms\Components\Select::make('user_group_id')
                    ->label(__('filament_user.user_group_id'))
                    ->relationship('userGroup', 'id')
                    ->hiddenOn([UsersRelationManager::class])
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->userGroupTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->userGroupTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload()
                    ->default(null),
            ]);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // 编辑时如果密码为空则不更新
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $data;
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
                Tables\Columns\TextColumn::make('userGroup.name')
                    ->label(__('filament_user.user_group_id'))
                    ->hiddenOn([UsersRelationManager::class])
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        $translation = $record->userGroup?->userGroupTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->userGroup?->userGroupTranslations->first();
                        return $first ? $first->name : $record->userGroup?->id;
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
