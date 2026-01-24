<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use App\Models\Currency;
use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = [];
    public $userIdFilter = null;
    public $currencyIdFilter = null;
    public $createdFrom = null;
    public $createdUntil = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => []],
        'userIdFilter' => ['except' => null],
        'currencyIdFilter' => ['except' => null],
        'createdFrom' => ['except' => null],
        'createdUntil' => ['except' => null],
    ];

    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->userIdFilter = null;
        $this->currencyIdFilter = null;
        $this->createdFrom = null;
        $this->createdUntil = null;
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        $query = Order::query()
            ->with(['user', 'currency', 'shippingAddress', 'billingAddress'])
            ->withCount('orderItems')
            ->when($this->search, function (Builder $query) {
                $query->where('order_no', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function (Builder $q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->whereIn('status', $this->statusFilter);
            })
            ->when($this->userIdFilter, function (Builder $query) {
                $query->where('user_id', $this->userIdFilter);
            })
            ->when($this->currencyIdFilter, function (Builder $query) {
                $query->where('currency_id', $this->currencyIdFilter);
            })
            ->when($this->createdFrom, function (Builder $query) {
                $query->whereDate('created_at', '>=', $this->createdFrom);
            })
            ->when($this->createdUntil, function (Builder $query) {
                $query->whereDate('created_at', '<=', $this->createdUntil);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate(15);
    }

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function getCurrenciesProperty()
    {
        return Currency::orderBy('name')->get();
    }

    public function getStatusOptionsProperty(): array
    {
        return OrderStatusEnum::options();
    }

    public function render()
    {
        return view('livewire.manager.orders', [
            'orders' => $this->orders,
            'users' => $this->users,
            'currencies' => $this->currencies,
            'statusOptions' => $this->statusOptions,
        ])->layout('components.layouts.manager');
    }
}
