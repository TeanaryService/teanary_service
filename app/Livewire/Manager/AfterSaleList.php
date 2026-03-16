<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasSearchAndFilters;
use App\Models\AfterSale;
use App\Services\AfterSaleService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AfterSaleList extends Component
{
    use HasSearchAndFilters;

    public ?string $filterStatus = null;
    public ?string $filterType = null;
    public ?int $currentId = null;
    public ?string $currentAction = null;
    public ?string $dialogRemarks = null;
    public bool $showDialog = false;

    protected function getAfterSaleService(): AfterSaleService
    {
        return app(AfterSaleService::class);
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatus = null;
        $this->filterType = null;
        $this->resetPage();
    }

    public function openDialog(string $action, int $id): void
    {
        $this->currentAction = $action;
        $this->currentId = $id;
        $this->dialogRemarks = null;
        $this->showDialog = true;
    }

    public function performAction(): void
    {
        if (! $this->currentId || ! $this->currentAction) {
            return;
        }

        $afterSale = AfterSale::query()->findOrFail($this->currentId);

        match ($this->currentAction) {
            'approve' => $this->getAfterSaleService()->review($afterSale, true, $this->dialogRemarks),
            'reject' => $this->getAfterSaleService()->review($afterSale, false, $this->dialogRemarks),
            'complete' => $this->getAfterSaleService()->complete($afterSale, $this->dialogRemarks),
            'cancel' => $this->getAfterSaleService()->cancel($afterSale, $this->dialogRemarks),
            default => null,
        };

        $flashMessage = match ($this->currentAction) {
            'approve' => '售后已通过审核',
            'reject' => '售后已拒绝',
            'complete' => '售后已标记完成',
            'cancel' => '售后已取消',
            default => '操作已完成',
        };

        $this->currentAction = null;
        $this->currentId = null;
        $this->dialogRemarks = null;
        $this->showDialog = false;

        $this->resetPage();
        $this->dispatch('flash-message', type: 'success', message: $flashMessage);
    }

    #[Computed]
    public function afterSales()
    {
        $query = AfterSale::query()
            ->with([
                'order.user',
                'product.productTranslations',
                'warehouse',
            ]);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('reason', 'like', '%'.$search.'%')
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_no', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('order.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    public function render()
    {
        return view('livewire.manager.after-sales', [
            'afterSales' => $this->afterSales,
        ])->layout('components.layouts.manager');
    }
}
