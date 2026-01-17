<?php

namespace App\Filament\Manager\Resources\TrafficStatisticResource\Pages;

use App\Filament\Manager\Resources\TrafficStatisticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrafficStatistics extends ListRecords
{
    protected static string $resource = TrafficStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 只读列表，不需要创建操作
        ];
    }
}
