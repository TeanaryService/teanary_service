<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Models\UserGroup;
use App\Services\LocaleCurrencyService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserGroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'userGroups';

    public static function getLabel(): string
    {
        return __('filament.promotion.user_groups');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.promotion.user_groups');
    }

    public function form(Form $form): Form
    {
        $service = app(LocaleCurrencyService::class);
        $lang = $service->getLanguageByCode(app()->getLocale());

        $userGroupOptions = UserGroup::with('userGroupTranslations')->get()->mapWithKeys(function ($group) use ($lang) {
            $translation = $group->userGroupTranslations->where('language_id', $lang?->id)->first();
            $name = $translation && $translation->name ? $translation->name : ($group->userGroupTranslations->first()->name ?? $group->id);

            return [$group->id => $name];
        })->toArray();

        return $form
            ->schema([
                Forms\Components\Select::make('user_group_id')
                    ->label(__('filament.promotion.user_group'))
                    ->options($userGroupOptions)
                    ->required()
                    ->columnSpanFull()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        $service = app(LocaleCurrencyService::class);
        $lang = $service->getLanguageByCode(app()->getLocale());

        return $table
            ->recordTitleAttribute('user_group_id')
            ->columns([
                Tables\Columns\TextColumn::make('userGroup')
                    ->label(__('filament.promotion.user_group'))
                    ->getStateUsing(function ($record) use ($lang) {
                        $group = $record->userGroup ?? $record;
                        if (! $group) {
                            return null;
                        }
                        $translation = $group->userGroupTranslations->where('language_id', $lang?->id)->first();

                        return $translation && $translation->name
                            ? $translation->name
                            : ($group->userGroupTranslations->first()->name ?? $group->id);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('filament.promotion.attach_user_group'))
                    ->action(function (array $data, $livewire) {
                        $livewire->getOwnerRecord()->userGroups()->attach($data['user_group_id']);
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('filament.promotion.detach_user_group'))
                    ->action(function ($record, $livewire) {
                        $livewire->getOwnerRecord()->userGroups()->detach($record->user_group_id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('filament.promotion.detach_user_group'))
                        ->action(function ($records, $livewire) {
                            $ids = $records->pluck('user_group_id')->all();
                            $livewire->getOwnerRecord()->userGroups()->detach($ids);
                        }),
                ]),
            ]);
    }
}
