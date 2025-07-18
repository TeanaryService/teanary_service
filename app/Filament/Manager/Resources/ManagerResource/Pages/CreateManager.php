<?php

namespace App\Filament\Manager\Resources\ManagerResource\Pages;

use App\Filament\Manager\Resources\ManagerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateManager extends CreateRecord
{
    protected static string $resource = ManagerResource::class;
}
