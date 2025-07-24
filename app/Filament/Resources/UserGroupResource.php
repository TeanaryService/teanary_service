<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserGroupResource\Pages;
use App\Filament\Resources\UserGroupResource\RelationManagers;
use App\Filament\Resources\UserGroupResource\RelationManagers\UsersRelationManager;
use App\Models\UserGroup;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserGroupResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;

    protected static ?string $model = UserGroup::class;
    protected static ?int $navigationSort = 301;

    public static function getLabel(): string
    {
        return __('filament.UserGroupResource.label');
    }
    public static function getPluralLabel(): string
    {
        return __('filament.UserGroupResource.pluralLabel');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament.UserGroupResource.group');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament.UserGroupResource.label');
    }
    public static function getNavigationIcon(): string
    {
        return __('filament.UserGroupResource.icon');
    }

    public static function form(Form $form): Form
    {
        $languages = app(LocaleCurrencyService::class)->getLanguages();
        $model = $form->getModelInstance();
        return $form
            ->schema([
                // 多语言 name 字段
                Forms\Components\Group::make(
                    $languages->map(function ($lang) use ($model) {
                        $default = '';
                        if ($model && $model->exists) {
                            $translation = $model->userGroupTranslations
                                ->where('language_id', $lang->id)
                                ->first();
                            $default = $translation ? $translation->name : '';
                        }
                        
                        return TextInput::make("translations.{$lang->id}.name")
                            ->label(__('filament_user_group.name') . " ({$lang->name})")
                            ->required($lang->is_default ?? false)
                            ->columnSpanFull()
                            ->default($default);
                    })->toArray()
                )->columnSpanFull()
                ->label(__('filament_user_group.name')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return static::applyDefaultPagination($table
            ->columns([
                // 显示当前语言的 name
                TextColumn::make('userGroupTranslations.name')
                    ->label(__('filament_user_group.name'))
                    ->getStateUsing(function ($record) {
                        $locale = app()->getLocale();
                        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
                        return optional(
                            $record->userGroupTranslations->where('language_id', $lang?->id)->first()
                        )->name;
                    }),
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
            UsersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserGroups::route('/'),
            'create' => Pages\CreateUserGroup::route('/create'),
            'edit' => Pages\EditUserGroup::route('/{record}/edit'),
        ];
    }
}
