<?php

namespace App\Filament\Resources\ProductReviewResource\Pages;

use App\Filament\Resources\ProductReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductReviews extends ListRecords
{
    protected static string $resource = ProductReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
