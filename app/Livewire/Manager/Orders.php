<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;

class Orders extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filterStatus = [];
    public ?int $filterUserId = null;
    public ?int $filterCurrencyId = null;
    public ?string $createdFrom = null;
    public ?string $createdUntil = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCurrencyId(): void
    {
        $this->resetPage();
    }

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
        $this->filterStatus = [];
        $this->filterUserId = null;
        $this->filterCurrencyId = null;
        $this->createdFrom = null;
        $this->createdUntil = null;
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        $query = Order::query()
            ->with(['user', 'currency', 'shippingAddress', 'billingAddress'])
            ->withCount('orderItems');

        // 搜索：通过订单号、用户名称、用户邮箱搜索
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // 筛选：订单状态
        if (!empty($this->filterStatus)) {
            $query->whereIn('status', $this->filterStatus);
        }

        // 筛选：用户
        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
        }

        // 筛选：货币
        if ($this->filterCurrencyId) {
            $query->where('currency_id', $this->filterCurrencyId);
        }

        // 筛选：创建日期范围
        if ($this->createdFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->createdFrom));
        }
        if ($this->createdUntil) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->createdUntil));
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getStatusBadgeColor($status): string
    {
        return match ($status) {
            OrderStatusEnum::Pending => 'bg-gray-100 text-gray-800',
            OrderStatusEnum::Paid => 'bg-blue-100 text-blue-800',
            OrderStatusEnum::Shipped => 'bg-yellow-100 text-yellow-800',
            OrderStatusEnum::Completed => 'bg-green-100 text-green-800',
            OrderStatusEnum::Cancelled => 'bg-red-100 text-red-800',
            OrderStatusEnum::AfterSale => 'bg-purple-100 text-purple-800',
            OrderStatusEnum::AfterSaleCompleted => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $users = \App\Models\User::orderBy('name')->get();
        $currencies = \App\Models\Currency::orderBy('name')->get();

        return view('livewire.manager.orders', [
            'orders' => $this->orders,
            'users' => $users,
            'currencies' => $currencies,
            'statusOptions' => OrderStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
