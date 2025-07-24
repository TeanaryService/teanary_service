<?php

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductVariant extends EditRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['specificationValues'])) {
            $data['specificationValues'] = collect($data['specificationValues'])
                ->filter(fn($item) => !empty($item['specification_id']) && !empty($item['specification_value_id']))
                ->map(fn($item) => [
                    'specification_id' => (int)$item['specification_id'],
                    'specification_value_id' => (int)$item['specification_value_id'],
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
            $record->specificationValues()->sync($syncData);
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 回填规格信息
        $specValues = [];
        if ($this->record && $this->record->specificationValues) {
            foreach ($this->record->specificationValues as $sv) {
                $pivot = $sv->pivot ?? null;
                if ($pivot) {
                    $specValues[] = [
                        'specification_id' => $pivot->specification_id,
                        'specification_value_id' => $sv->id,
                    ];
                }
            }
        }
        $data['specificationValues'] = $specValues;
        return $data;
    }
}
