<?php

namespace App\Livewire\Manager;

use App\Models\Contact;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasDeleteAction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Contacts extends Component
{
    use HasSearchAndFilters;
    use HasDeleteAction;

    public ?string $createdFrom = null;
    public ?string $createdUntil = null;

    public function updatingCreatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatingCreatedUntil(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->createdFrom = null;
        $this->createdUntil = null;
        $this->resetPage();
    }

    public function deleteContact(int $id): void
    {
        $this->deleteModel(Contact::class, $id);
    }

    #[Computed]
    public function contacts()
    {
        $query = Contact::query();

        // 搜索：通过名称、邮箱、消息搜索
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            });
        }

        // 筛选：创建日期
        if ($this->createdFrom) {
            $query->whereDate('created_at', '>=', $this->createdFrom);
        }
        if ($this->createdUntil) {
            $query->whereDate('created_at', '<=', $this->createdUntil);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.contacts', [
            'contacts' => $this->contacts,
        ])->layout('components.layouts.manager');
    }
}
