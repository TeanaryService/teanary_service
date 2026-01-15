<?php

namespace App\Filament\Manager\Resources\PromotionResource\RelationManagers;

use App\Models\UserGroup;
use App\Traits\HasTranslationHelpers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserGroupsRelationManager extends RelationManager
{
    use HasTranslationHelpers;

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
        $lang = static::getCurrentLanguage();

        $userGroupOptions = UserGroup::with('userGroupTranslations')->get()->mapWithKeys(function ($group) use ($lang) {
            $name = static::getTranslationName(
                $group->userGroupTranslations,
                $lang?->id,
                'name',
                $group->id
            );

            // 确保ID是字符串类型，以便在Select中正确匹配
            return [(string) $group->id => $name];
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
        $lang = static::getCurrentLanguage();

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
                        return static::getTranslationName(
                            $group->userGroupTranslations,
                            $lang?->id,
                            'name',
                            $group->id
                        );
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
