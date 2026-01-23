<?php

namespace App\Livewire\Manager;

use App\Models\Language;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Languages extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterDefault = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

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
        $language = Language::findOrFail($id);
        $language->delete();
        session()->flash('message', __('app.deleted_successfully'));
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
