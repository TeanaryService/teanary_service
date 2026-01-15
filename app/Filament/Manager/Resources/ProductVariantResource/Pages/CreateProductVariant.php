<?php

namespace App\Filament\Manager\Resources\ProductVariantResource\Pages;

use App\Filament\Manager\Resources\ProductVariantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVariant extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['specificationValues'])) {
            $data['specificationValues'] = collect($data['specificationValues'])
                ->filter(fn ($item) => ! empty($item['specification_id']) && ! empty($item['specification_value_id']))
                ->map(fn ($item) => [
                    'specification_id' => (int) $item['specification_id'],
                    'specification_value_id' => (int) $item['specification_value_id'],
                ])
                ->unique('specification_id')
                ->values()
                ->toArray();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;
        if (isset($data['specificationValues'])) {
            $syncData = [];
            foreach ($data['specificationValues'] as $item) {
                $syncData[$item['specification_value_id']] = ['specification_id' => $item['specification_id']];
            }
            $record->syncSpecificationValues($syncData);
        }
    }
}
