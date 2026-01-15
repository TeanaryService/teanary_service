<?php

namespace App\Filament\User\Resources\UserOrderResource\Pages;

use App\Filament\User\Resources\UserOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserOrders extends ListRecords
{
    protected static string $resource = UserOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 可以添加创建订单等操作
        ];
    }
}
