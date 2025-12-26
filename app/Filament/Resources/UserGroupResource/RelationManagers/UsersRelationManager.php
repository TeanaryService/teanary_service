<?php

namespace App\Filament\Resources\UserGroupResource\RelationManagers;

use App\Filament\Resources\UserResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament.user_group.users');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament.user_group.users');
    }

    protected static string $relationship = 'users';

    public function form(Form $form): Form
    {
        return UserResource::form($form);
    }

    public function table(Table $table): Table
    {
        return UserResource::table($table);
    }
}
