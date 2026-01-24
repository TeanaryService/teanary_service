<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\SnowflakeService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OrderForm extends Component
{
    use HasNavigationRedirect;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public ?int $id = null;
    public ?Order $order = null;

    // 订单基本信息
    public ?int $userId = null;
    public string $orderNo = '';
    public string $status = '';
    public ?int $shippingAddressId = null;
    public ?int $billingAddressId = null;

    // 订单商品编辑
    public array $editingItems = []; // [itemId => ['qty' => x, 'price' => y]]
    public bool $showAddItemForm = false;
    public ?int $newProductId = null;
    public ?int $newVariantId = null;
    public int $newQty = 1;
    public float $newPrice = 0;

    public function mount(?int $id = null): void
    {
        $this->id = $id;

        if ($this->id) {
            // 编辑模式
            $this->loadOrder();
        } else {
            // 创建模式
            $this->orderNo = strtolower(uniqid(prefix: 'ORD-'));
            $this->status = OrderStatusEnum::Pending->value;
        }
    }

    public function loadOrder(): void
    {
        $this->order = Order::with([
            'user',
            'currency',
            'shippingAddress.country.countryTranslations',
            'shippingAddress.zone.zoneTranslations',
            'billingAddress.country.countryTranslations',
            'billingAddress.zone.zoneTranslations',
            'orderItems.product.productTranslations',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
        ])->findOrFail($this->id);

        $this->userId = $this->order->user_id;
        $this->orderNo = $this->order->order_no;
        $this->status = $this->order->status->value;
        $this->shippingAddressId = $this->order->shipping_address_id;
        $this->billingAddressId = $this->order->billing_address_id;
    }

    public function save(): void
    {
        $this->validate([
            'userId' => 'required|exists:users,id',
            'orderNo' => 'required|string|max:255',
            'status' => 'required|string',
        ], [
            'userId.required' => '请选择用户',
            'userId.exists' => '用户不存在',
            'orderNo.required' => '请输入订单号',
            'status.required' => '请选择订单状态',
        ]);

        if ($this->id) {
            // 更新订单
            $this->order->update([
                'user_id' => $this->userId,
                'order_no' => $this->orderNo,
                'status' => OrderStatusEnum::from($this->status),
                'shipping_address_id' => $this->shippingAddressId,
                'billing_address_id' => $this->billingAddressId,
            ]);
            $this->flashMessage('updated_successfully');
        } else {
            // 创建订单
            $snowflakeService = app(SnowflakeService::class);
            $this->order = Order::create([
                'id' => $snowflakeService->nextId(),
                'order_no' => $this->orderNo,
                'user_id' => $this->userId,
                'status' => OrderStatusEnum::from($this->status),
                'shipping_address_id' => $this->shippingAddressId,
                'billing_address_id' => $this->billingAddressId,
                'total' => 0,
            ]);
            $this->id = $this->order->id;
            $this->dispatch('flash-message', type: 'success', message: '订单创建成功');
        }

        $this->loadOrder();
    }

    // 订单商品编辑功能
    public function startEditItem(int $itemId): void
    {
        $item = $this->order->orderItems->find($itemId);
        if ($item) {
            $this->editingItems[$itemId] = [
                'qty' => $item->qty,
                'price' => $item->price,
            ];
        }
    }

    public function cancelEditItem(int $itemId): void
    {
        unset($this->editingItems[$itemId]);
    }

    public function updateItem(int $itemId): void
    {
        if (! isset($this->editingItems[$itemId])) {
            return;
        }

        $data = $this->editingItems[$itemId];
        $item = OrderItem::findOrFail($itemId);

        $item->update([
            'qty' => $data['qty'],
            'price' => $data['price'],
        ]);

        unset($this->editingItems[$itemId]);
        $this->recalculateOrderTotal();
        $this->loadOrder();
        $this->flashMessage('updated_successfully');
    }

    public function deleteItem(int $itemId): void
    {
        OrderItem::findOrFail($itemId)->delete();
        $this->recalculateOrderTotal();
        $this->loadOrder();
        $this->flashMessage('deleted_successfully');
    }

    public function toggleAddItemForm(): void
    {
        $this->showAddItemForm = ! $this->showAddItemForm;
        if (! $this->showAddItemForm) {
            $this->resetAddItemForm();
        }
    }

    public function resetAddItemForm(): void
    {
        $this->newProductId = null;
        $this->newVariantId = null;
        $this->newQty = 1;
        $this->newPrice = 0;
        $this->resetErrorBag();
    }

    public function addItem(): void
    {
        if (! $this->order) {
            $this->dispatch('flash-message', type: 'error', message: '请先保存订单基本信息');

            return;
        }

        $this->validate([
            'newProductId' => 'required|exists:products,id',
            'newQty' => 'required|integer|min:1',
            'newPrice' => 'required|numeric|min:0',
        ], [
            'newProductId.required' => '请选择商品',
            'newQty.required' => '请输入数量',
            'newQty.min' => '数量至少为1',
            'newPrice.required' => '请输入单价',
            'newPrice.min' => '单价不能小于0',
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->newProductId,
            'product_variant_id' => $this->newVariantId,
            'qty' => $this->newQty,
            'price' => $this->newPrice,
        ]);

        $this->recalculateOrderTotal();
        $this->resetAddItemForm();
        $this->showAddItemForm = false;
        $this->loadOrder();
        $this->flashMessage('added_successfully');
    }

    protected function recalculateOrderTotal(): void
    {
        if ($this->order) {
            $total = $this->order->orderItems()->sum(DB::raw('price * qty'));
            $this->order->update(['total' => $total]);
        }
    }

    public function updatedUserId(): void
    {
        // 当用户改变时，重新加载地址列表
        $this->shippingAddressId = null;
        $this->billingAddressId = null;
    }

    #[Computed]
    public function calculatedTotal(): float
    {
        if (! $this->order) {
            return 0;
        }

        $total = 0;
        foreach ($this->order->orderItems as $item) {
            if (isset($this->editingItems[$item->id])) {
                $editData = $this->editingItems[$item->id];
                $total += ($editData['price'] ?? $item->price) * ($editData['qty'] ?? $item->qty);
            } else {
                $total += $item->price * $item->qty;
            }
        }

        return $total;
    }

    public function getProductName($product, $lang)
    {
        if (! $product) {
            return '-';
        }

        return $this->translatedField($product->productTranslations, $lang, 'name', (string) $product->id);
    }

    public function getVariantSpecs($variant, $lang)
    {
        if (! $variant) {
            return __('manager.order_item.no_variant');
        }
        $specNames = [];
        foreach ($variant->specificationValues as $specValue) {
            $specNames[] = $this->translatedField($specValue->specificationValueTranslations, $lang, 'name', '');
        }

        return implode(' / ', array_filter($specNames)) ?: ($variant->sku ?? $variant->id);
    }

    public function render()
    {
        $service = $this->getLocaleService();
        $lang = $this->getCurrentLanguage();
        $currentCurrencyCode = $this->getCurrentCurrencyCode();

        // 获取用户列表
        $users = User::orderBy('name')->get();

        // 获取用户地址（如果有用户）
        $userAddresses = [];
        if ($this->userId) {
            $userAddresses = Address::where('user_id', $this->userId)
                ->where('deleted', false)
                ->with(['country.countryTranslations', 'zone.zoneTranslations'])
                ->get();
        }

        // 获取商品列表（用于添加商品）
        $products = Product::with(['productTranslations', 'productVariants'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        return view('livewire.manager.order-form', [
            'order' => $this->order,
            'lang' => $lang,
            'currentCurrencyCode' => $currentCurrencyCode,
            'service' => $service,
            'statusOptions' => OrderStatusEnum::options(),
            'users' => $users,
            'userAddresses' => $userAddresses,
            'products' => $products,
        ])->layout('components.layouts.manager');
    }
}
