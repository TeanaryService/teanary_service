<?php

namespace App\Filament\Manager\Resources\UserResource\Pages;

use App\Filament\Manager\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
