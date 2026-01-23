<?php

namespace App\Livewire\Manager;

use App\Models\Manager;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasDeleteAction;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Managers extends Component
{
    use HasSearchAndFilters;
    use HasDeleteAction;

    public function deleteManager(int $id): void
    {
        $this->deleteModel(Manager::class, $id);
    }

    public function generateToken(int $id): void
    {
        $manager = Manager::findOrFail($id);
        $token = bin2hex(random_bytes(32));
        $manager->update(['token' => $token]);
        session()->flash('message', 'Token已生成: '.$token);
    }

    #[Computed]
    public function managers()
    {
        $query = Manager::query();

        // 搜索：通过名称、邮箱搜索
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.managers', [
            'managers' => $this->managers,
        ])->layout('components.layouts.manager');
    }
}
