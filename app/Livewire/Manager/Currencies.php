<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Models\Currency;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Currencies extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;

    public string $filterDefault = '';
    public ?float $exchangeRateFrom = null;
    public ?float $exchangeRateUntil = null;

    public function updatingFilterDefault(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterDefault = '';
        $this->exchangeRateFrom = null;
        $this->exchangeRateUntil = null;
        $this->resetPage();
    }

    public function deleteCurrency(int $id): void
    {
        $this->deleteModel(Currency::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->currencies->getCollection();
    }

    public function batchDeleteCurrencies(): void
    {
        $this->batchDelete(Currency::class);
    }

    #[Computed]
    public function currencies()
    {
        $query = Currency::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('symbol', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterDefault !== '') {
            $query->where('default', $this->filterDefault === '1');
        }

        if ($this->exchangeRateFrom !== null) {
            $query->where('exchange_rate', '>=', $this->exchangeRateFrom);
        }

        if ($this->exchangeRateUntil !== null) {
            $query->where('exchange_rate', '<=', $this->exchangeRateUntil);
        }

        return $query->orderBy('code')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.currencies', [
            'currencies' => $this->currencies,
        ])->layout('components.layouts.manager');
    }
}
