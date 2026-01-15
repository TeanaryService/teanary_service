<?php

namespace App\Filament\Manager\Resources;

use App\Enums\TranslationStatusEnum;
use App\Filament\Manager\Resources\UserGroupResource\Pages;
use App\Filament\Manager\Resources\UserGroupResource\RelationManagers\UsersRelationManager;
use App\Models\UserGroup;
use App\Services\LocaleCurrencyService;
use App\Traits\HasActions;
use App\Traits\HasDefaultPagination;
use App\Traits\HasTimestampsColumn;
use App\Traits\HasTranslationStatus;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserGroupResource extends Resource
{
    use HasActions;
    use HasDefaultPagination;
    use HasTimestampsColumn;
    use HasTranslationStatus;

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
                Forms\Components\Section::make(__('filament.user_group.basic_info'))
                    ->schema([
                        Forms\Components\Select::make('translation_status')
                            ->label(__('filament.user_group.translation_status'))
                            ->options(TranslationStatusEnum::options())
                            ->default(TranslationStatusEnum::NotTranslated->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(1),
                Forms\Components\Section::make(__('filament.user_group.translations'))
                    ->schema([
                        Forms\Components\Group::make(
                            $languages->map(function ($language) use ($model) {
                                $default = '';
                                if ($model && $model->exists) {
                                    $translation = $model->userGroupTranslations
                                        ->where('language_id', $language->id)
                                        ->first();
                                    $default = $translation ? $translation->name : '';
                                }

                                return TextInput::make("translations.{$language->id}.name")
                                    ->label(__('filament.user_group.name')." ({$language->name})")
                                    ->required($language->is_default ?? false)
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default($default)
                                    ->helperText($language->is_default ? __('filament.user_group.name_helper') : null);
                            })->toArray()
                        )->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
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
                        'userGroupTranslations',
                        'users',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.user_group.name'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $translation = $record->userGroupTranslations->where('language_id', $lang?->id)->first();
                        if ($translation && $translation->name) {
                            return $translation->name;
                        }
                        $first = $record->userGroupTranslations->first();
                        return $first ? $first->name : __('filament.user_group.unnamed');
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        return $query->whereHas('userGroupTranslations', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $direction) use ($lang): \Illuminate\Database\Eloquent\Builder {
                        $langId = $lang?->id ?? 1;
                        return $query->leftJoin('user_group_translations', function ($join) use ($langId) {
                            $join->on('user_groups.id', '=', 'user_group_translations.user_group_id')
                                ->where('user_group_translations.language_id', '=', $langId);
                        })
                        ->orderBy('user_group_translations.name', $direction)
                        ->select('user_groups.*')
                        ->groupBy('user_groups.id');
                    })
                    ->wrap(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label(__('filament.user_group.users_count'))
                    ->counts('users')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('translation_status')
                    ->formatStateUsing(fn ($state): string => $state->label())
                    ->label(__('filament.user_group.translation_status'))
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        TranslationStatusEnum::NotTranslated => 'gray',
                        TranslationStatusEnum::Pending => 'warning',
                        TranslationStatusEnum::Translated => 'success',
                    })
                    ->sortable()
                    ->toggleable(),
                ...static::getTimestampsColumns(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('translation_status')
                    ->label(__('filament.user_group.translation_status'))
                    ->options(TranslationStatusEnum::options())
                    ->multiple(),
            ])
            ->actions([
                ...static::getActions(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ...static::getBulkActions(),
                    ...static::getTranslationStatusBulkActions(),
                ]),
            ])
            ->defaultSort('created_at', 'desc'));
    }

    public static function getRelations(): array
    {
        return [
            //
            UsersRelationManager::class,
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
