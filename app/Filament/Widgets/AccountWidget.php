<?php

namespace App\Filament\Widgets;
use Filament\Widgets\AccountWidget as BaseAccountWidget;

class AccountWidget extends BaseAccountWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 100;
}
