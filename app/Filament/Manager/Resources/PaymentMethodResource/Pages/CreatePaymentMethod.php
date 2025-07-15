<?php

namespace App\Filament\Manager\Resources\PaymentMethodResource\Pages;

use App\Filament\Manager\Resources\PaymentMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
