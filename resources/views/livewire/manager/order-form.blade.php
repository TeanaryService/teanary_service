@php
    $isEdit = $id !== null;
    $breadcrumbs = buildManagerCenterBreadcrumbs('orders', $isEdit ? __('app.edit') : __('app.create'), __('manager.orders.label'), locaRoute('manager.orders'));
@endphp

<div class="min-h-[70vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="orders" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $isEdit ? __('app.edit') : __('app.create') }} 订单</h1>
                        @if($isEdit && $order)
                            <p class="text-sm text-gray-600 mt-1">订单号: {{ $order->order_no }}</p>
                        @endif
                    </div>
                    <x-widgets.button 
                        href="{{ locaRoute('manager.orders') }}" wire:navigate 
                        variant="secondary"
                        class="inline-flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('app.back') }}
                    </x-widgets.button>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-100 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                <form wire:submit="save">
                    {{-- 订单基本信息 --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">订单基本信息</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-widgets.form-field label="用户" labelFor="userId" required error="userId">
                                <x-widgets.select 
                                    id="userId"
                                    wire="userId"
                                    :options="[['value' => '', 'label' => __('app.select')], ...collect($users)->map(fn($user) => ['value' => $user->id, 'label' => $user->name . ' (' . $user->email . ')'])->toArray()]"
                                    error="userId"
                                />
                            </x-widgets.form-field>

                            <x-widgets.form-field label="订单号" labelFor="orderNo" required error="orderNo">
                                <x-widgets.input 
                                    type="text" 
                                    id="orderNo"
                                    wire="orderNo"
                                    error="orderNo"
                                />
                            </x-widgets.form-field>

                            <x-widgets.form-field label="订单状态" labelFor="status" required error="status">
                                <x-widgets.select 
                                    id="status"
                                    wire="status"
                                    :options="collect($statusOptions)->map(fn($label, $value) => ['value' => $value, 'label' => $label])->toArray()"
                                    error="status"
                                />
                            </x-widgets.form-field>

                            @if($userId)
                                <x-widgets.form-field label="收货地址" labelFor="shippingAddressId" error="shippingAddressId">
                                    <x-widgets.select 
                                        id="shippingAddressId"
                                        wire="shippingAddressId"
                                        :selected="$shippingAddressId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($userAddresses)->map(fn($addr) => ['value' => $addr->id, 'label' => $addr->firstname . ' ' . $addr->lastname . ' - ' . $addr->address_1])->toArray()]"
                                        error="shippingAddressId"
                                    />
                                </x-widgets.form-field>

                                <x-widgets.form-field label="账单地址" labelFor="billingAddressId" error="billingAddressId">
                                    <x-widgets.select 
                                        id="billingAddressId"
                                        wire="billingAddressId"
                                        :selected="$billingAddressId"
                                        :options="[['value' => '', 'label' => __('app.select')], ...collect($userAddresses)->map(fn($addr) => ['value' => $addr->id, 'label' => $addr->firstname . ' ' . $addr->lastname . ' - ' . $addr->address_1])->toArray()]"
                                        error="billingAddressId"
                                    />
                                </x-widgets.form-field>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <x-widgets.button 
                                href="{{ locaRoute('manager.orders') }}" wire:navigate
                                variant="secondary"
                                class="px-6 py-2"
                            >
                                {{ __('app.cancel') }}
                            </x-widgets.button>
                            <x-widgets.button type="submit" class="px-6 py-2">
                                {{ __('app.save') }}
                            </x-widgets.button>
                        </div>
                    </div>
                </form>

                {{-- 订单商品列表（仅编辑模式显示） --}}
                @if($isEdit && $order)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('manager.order.items_section') }}</h2>
                            <x-widgets.button 
                                wire:click="toggleAddItemForm"
                                class="inline-flex items-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.add') }} 商品
                            </x-widgets.button>
                        </div>

                        {{-- 添加商品表单 --}}
                        @if($showAddItemForm)
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900 mb-4">添加商品</h3>
                                <form wire:submit="addItem" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <x-widgets.form-field label="商品" labelFor="newProductId" required error="newProductId">
                                            <x-widgets.select 
                                                id="newProductId"
                                                wire="newProductId"
                                                :options="[['value' => '', 'label' => __('app.select')], ...collect($products)->map(fn($p) => ['value' => $p->id, 'label' => $p->productTranslations->where('language_id', $lang->id)->first()?->name ?? $p->slug])->toArray()]"
                                                error="newProductId"
                                            />
                                        </x-widgets.form-field>
                                        <x-widgets.form-field label="数量" labelFor="newQty" required error="newQty">
                                            <x-widgets.input 
                                                type="number" 
                                                id="newQty"
                                                wire="newQty"
                                                min="1"
                                                error="newQty"
                                            />
                                        </x-widgets.form-field>
                                        <x-widgets.form-field label="单价" labelFor="newPrice" required error="newPrice">
                                            <x-widgets.input 
                                                type="number" 
                                                id="newPrice"
                                                wire="newPrice"
                                                step="0.01"
                                                min="0"
                                                error="newPrice"
                                            />
                                        </x-widgets.form-field>
                                        <div class="flex items-end gap-2">
                                            <x-widgets.button 
                                                type="button"
                                                wire:click="toggleAddItemForm"
                                                variant="secondary"
                                            >
                                                {{ __('app.cancel') }}
                                            </x-widgets.button>
                                            <x-widgets.button type="submit">
                                                {{ __('app.save') }}
                                            </x-widgets.button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">规格</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">单价</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">小计</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->orderItems as $item)
                                        @php
                                            $orderCurrencyCode = $order->currency?->code ?? $service->getDefaultCurrencyCode();
                                            $isEditing = isset($editingItems[$item->id]);
                                            $editData = $isEditing ? $editingItems[$item->id] : ['qty' => $item->qty, 'price' => $item->price];
                                            $subtotal = $editData['price'] * $editData['qty'];
                                            $subtotalFormatted = $service->formatWithSymbol($subtotal, $orderCurrencyCode);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $this->getProductName($item->product, $lang) }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $this->getVariantSpecs($item->productVariant, $lang) }}
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($isEditing)
                                                    <x-widgets.input 
                                                        type="number" 
                                                        wire:model.live="editingItems.{{ $item->id }}.price"
                                                        step="0.01"
                                                        min="0"
                                                        class="w-24 text-right"
                                                    />
                                                @else
                                                    <span class="text-sm text-gray-900">
                                                        {{ $service->formatWithSymbol($item->price, $orderCurrencyCode) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if($isEditing)
                                                    <x-widgets.input 
                                                        type="number" 
                                                        wire:model.live="editingItems.{{ $item->id }}.qty"
                                                        min="1"
                                                        class="w-20 text-center"
                                                    />
                                                @else
                                                    <span class="text-sm text-gray-900">{{ $item->qty }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-sm font-medium text-gray-900">{{ $subtotalFormatted }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if($isEditing)
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button 
                                                            wire:click="updateItem({{ $item->id }})"
                                                            class="text-teal-600 hover:text-teal-700 text-sm"
                                                        >
                                                            {{ __('app.save') }}
                                                        </button>
                                                        <button 
                                                            wire:click="cancelEditItem({{ $item->id }})"
                                                            class="text-gray-600 hover:text-gray-700 text-sm"
                                                        >
                                                            {{ __('app.cancel') }}
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-end gap-2">
                                                        <button 
                                                            wire:click="startEditItem({{ $item->id }})"
                                                            class="text-teal-600 hover:text-teal-700 text-sm"
                                                        >
                                                            {{ __('app.edit') }}
                                                        </button>
                                                        <button 
                                                            wire:click="deleteItem({{ $item->id }})"
                                                            wire:confirm="确定要删除这个商品吗？"
                                                            class="text-red-600 hover:text-red-700 text-sm"
                                                        >
                                                            {{ __('app.delete') }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-900">订单总额</td>
                                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                                            @php
                                                $orderCurrencyCode = $order->currency?->code ?? $service->getDefaultCurrencyCode();
                                                $calculatedTotal = $this->calculatedTotal ?? $order->total;
                                                $displayTotal = $calculatedTotal > 0 ? $calculatedTotal : $order->total;
                                                $totalFormatted = $service->formatWithSymbol($displayTotal, $orderCurrencyCode);
                                            @endphp
                                            {{ $totalFormatted }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ $isEdit ? __('app.edit') : __('app.create') }} 订单" />
