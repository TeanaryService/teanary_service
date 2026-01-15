<?php

namespace App\Filament\Manager\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getTitle(): string
    {
        return __('filament.dashboard.title');
    }

    public function getHeading(): string
    {
        return __('filament.dashboard.heading');
    }
}
