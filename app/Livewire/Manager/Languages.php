<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasBatchActions;
use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Models\Language;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Languages extends Component
{
    use HasBatchActions;
    use HasDeleteAction;
    use HasSearchAndFilters;

    public string $filterDefault = '';

    public function updatingFilterDefault(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterDefault = '';
        $this->resetPage();
    }

    public function deleteLanguage(int $id): void
    {
        $this->deleteModel(Language::class, $id);
    }

    protected function getCurrentPageItems()
    {
        return $this->languages->getCollection();
    }

    public function batchDeleteLanguages(): void
    {
        $this->batchDelete(Language::class);
    }

    #[Computed]
    public function languages()
    {
        $query = Language::query();

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        if ($this->filterDefault !== '') {
            $query->where('default', $this->filterDefault === '1');
        }

        return $query->orderBy('code')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.languages', [
            'languages' => $this->languages,
        ])->layout('components.layouts.manager');
    }
}
