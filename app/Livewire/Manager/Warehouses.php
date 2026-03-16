<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use App\Support\CacheKeys;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Warehouses extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;

    public string $filterActive = '';

    public function updatingFilterActive(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterActive = '';
        $this->resetPage();
    }

    public function deleteWarehouse(int $id): void
    {
        $this->deleteModel(Warehouse::class, $id, CacheKeys::WAREHOUSES_ALL);
        app(WarehouseService::class)->clearWarehousesCache();
    }

    protected function getCurrentPageItems()
    {
        return $this->warehouses->getCollection();
    }

    public function batchDeleteWarehouses(): void
    {
        $this->batchDelete(Warehouse::class, CacheKeys::WAREHOUSES_ALL);
        app(WarehouseService::class)->clearWarehousesCache();
    }

    #[Computed]
    public function warehouses()
    {
        $query = Warehouse::query()->with(['country', 'zone']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('city', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterActive !== '') {
            $query->where('active', $this->filterActive === '1');
        }

        return $query->orderBy('sort_order')->orderBy('id')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.warehouses', [
            'warehouses' => $this->warehouses,
        ])->layout('components.layouts.manager');
    }
}
