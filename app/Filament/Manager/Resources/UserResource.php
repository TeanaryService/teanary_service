<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\UserGroupResource\RelationManagers\UsersRelationManager;
use App\Filament\Manager\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\LocaleCurrencyService;
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
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return $form
            ->schema([
                Forms\Components\Section::make(__('filament.user.basic_info'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('avatar')
                            ->label(__('filament.user.avatar'))
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->columnSpanFull()
                            ->required()
                            ->collection('avatars')
                            ->avatar()
                            ->helperText(__('filament.user.avatar_helper')),
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.user.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('email')
                            ->label(__('filament.user.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        Forms\Components\Select::make('user_group_id')
                            ->label(__('filament.user.user_group'))
                            ->relationship('userGroup', 'id', function ($query) {
                                return $query->with('userGroupTranslations');
                            })
                            ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                                $translation = $record->userGroupTranslations->where('language_id', $lang?->id)->first();
                                if ($translation && $translation->name) {
                                    return $translation->name;
                                }
                                $first = $record->userGroupTranslations->first();
                                return $first ? $first->name : $record->id;
                            })
                            ->searchable()
                            ->preload()
                            ->default(null)
                            ->columnSpan(1)
                            ->hiddenOn([UsersRelationManager::class])
                            ->helperText(__('filament.user.user_group_helper')),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label(__('filament.user.email_verified_at'))
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('filament.user.password'))
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('filament.user.password'))
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => ! empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => ! empty($state))
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->same('password_confirmation')
                            ->autocomplete('new-password')
                            ->columnSpan(1)
                            ->helperText(__('filament.user.password_helper')),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(__('filament.user.password_confirmation'))
                            ->password()
                            ->maxLength(255)
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);

        return static::applyDefaultPagination($table
            ->modifyQueryUsing(
                fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                    ->with([
                        'userGroup.userGroupTranslations',
                    ])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label(__('filament.user.avatar'))
                    ->circular()
                    ->collection('avatars')
                    ->conversion('thumb')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.user.name'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.user.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('filament.user.email_verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => $record->email_verified_at !== null)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('filament.user.email_verified_at'))
                    ->dateTime(format: 'Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('userGroup.name')
                    ->label(__('filament.user.user_group'))
                    ->getStateUsing(function ($record) use ($lang) {
                        if (!$record->userGroup) {
                            return '-';
                        }
                        $translation = $record->userGroup->userGroupTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->userGroup->userGroupTranslations->first();
                        return $first ? $first->name : $record->userGroup->id;
                    })
                    ->searchable()
                    ->sortable()
                    ->hiddenOn([UsersRelationManager::class])
                    ->toggleable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label(__('filament.user.orders_count'))
                    ->counts('orders')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_group_id')
                    ->label(__('filament.user.user_group'))
                    ->relationship('userGroup', 'id', function ($query) {
                        return $query->with('userGroupTranslations');
                    })
                    ->getOptionLabelFromRecordUsing(function ($record) use ($lang) {
                        $translation = $record->userGroupTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->userGroupTranslations->first();
                        return $first ? $first->name : $record->id;
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('email_verified')
                    ->label(__('filament.user.email_verified'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('email_unverified')
                    ->label(__('filament.user.email_unverified'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('login')
                    ->label(__('filament.user.login'))
                    ->url(fn ($record) => locaRoute('login-as', ['id' => (int) $record->id]))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-key')
                    ->color('success'),
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
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
