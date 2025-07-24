<?php

namespace App\Filament\Resources\UserGroupResource\RelationManagers;

use App\Filament\Resources\UserResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsersRelationManager extends RelationManager
{
    public static function getLabel(): string
    {
        return __('filament_user_group.users');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament_user_group.users');
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
