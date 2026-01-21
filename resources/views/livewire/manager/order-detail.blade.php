@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('orders', __('app.view'), __('manager.orders.label'), locaRoute('manager.orders'));
@endphp

<div class="min-h-[40vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="orders" />
            
            <div class="flex-1">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.orders.label') }}</h1>
                        <p class="text-sm text-gray-600 mt-1">订单号: {{ $order->order_no }}</p>
                    </div>
                    <a href="{{ locaRoute('manager.orders') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('app.back') }}
                    </a>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 订单基本信息 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">订单信息</h2>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $this->getStatusBadgeColor($order->status) }}">
                            {{ $order->status->label() }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.user_info') }}</h3>
                            @if($order->user)
                                <p class="text-sm text-gray-900">{{ $order->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $order->user->email }}</p>
                            @else
                                <p class="text-sm text-gray-400">-</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.amount') }}</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $order->currency ? $order->currency->symbol : '' }}{{ number_format($order->total, 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $order->currency ? $order->currency->name : '' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.payment_method') }}</h3>
                            <p class="text-sm text-gray-900">{{ $order->payment_method ? $order->payment_method->label() : '-' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.shipping_method') }}</h3>
                            <p class="text-sm text-gray-900">{{ $order->shipping_method ? $order->shipping_method->label() : '-' }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.shipping_address') }}</h3>
                            @if($order->shippingAddress)
                                <p class="text-sm text-gray-900">
                                    {{ $order->shippingAddress->firstname }} {{ $order->shippingAddress->lastname }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $order->shippingAddress->address_1 }}<br>
                                    {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->zone }}, {{ $order->shippingAddress->country }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $order->shippingAddress->postcode }}</p>
                                <p class="text-sm text-gray-600">{{ $order->shippingAddress->telephone }}</p>
                            @else
                                <p class="text-sm text-gray-400">-</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.billing_address') }}</h3>
                            @if($order->billingAddress)
                                <p class="text-sm text-gray-900">
                                    {{ $order->billingAddress->firstname }} {{ $order->billingAddress->lastname }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ $order->billingAddress->address_1 }}<br>
                                    {{ $order->billingAddress->city }}, {{ $order->billingAddress->zone }}, {{ $order->billingAddress->country }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $order->billingAddress->postcode }}</p>
                            @else
                                <p class="text-sm text-gray-400">-</p>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.created_at') }}</h3>
                            <p class="text-sm text-gray-900">{{ $order->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('manager.order.updated_at') }}</h3>
                            <p class="text-sm text-gray-900">{{ $order->updated_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>

                    {{-- 订单状态操作 --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('manager.order.status_section') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($statusOptions as $value => $label)
                                <button 
                                    wire:click="updateStatus('{{ $value }}')"
                                    class="px-3 py-1 text-sm font-medium rounded-lg border transition-colors {{ $order->status->value === $value ? 'bg-teal-50 border-teal-300 text-teal-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 订单商品列表 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('manager.order.items_section') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">规格</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">单价</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">数量</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">小计</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $this->getProductName($item->product, $lang) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            {{ $this->getVariantSpecs($item->productVariant, $lang) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-900">
                                            @php
                                                $orderCurrencyCode = $order->currency?->code ?? $service->getDefaultCurrencyCode();
                                                $itemPrice = $service->convertWithSymbol($item->price, $currentCurrencyCode, $orderCurrencyCode);
                                            @endphp
                                            {{ $itemPrice }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm text-gray-900">
                                            {{ $item->qty }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                            @php
                                                $subtotal = ($item->price ?? 0) * ($item->qty ?? 0);
                                                $subtotalFormatted = $service->convertWithSymbol($subtotal, $currentCurrencyCode, $orderCurrencyCode);
                                            @endphp
                                            {{ $subtotalFormatted }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-sm font-medium text-gray-900">订单总额</td>
                                    <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                                        @php
                                            $orderCurrencyCode = $order->currency?->code ?? $service->getDefaultCurrencyCode();
                                            $totalFormatted = $service->convertWithSymbol($order->total, $currentCurrencyCode, $orderCurrencyCode);
                                        @endphp
                                        {{ $totalFormatted }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- 发货记录 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">发货记录</h2>
                        @if($order->status === \App\Enums\OrderStatusEnum::Paid || $order->status === \App\Enums\OrderStatusEnum::Shipped)
                            <button 
                                wire:click="toggleShipmentForm"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.create') }} 发货记录
                            </button>
                        @endif
                    </div>

                    {{-- 发货表单 --}}
                    @if($showShipmentForm)
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900 mb-4">新增发货记录</h3>
                            <form wire:submit="createShipment" class="space-y-4">
                                <div>
                                    <label for="shippingMethod" class="block text-sm font-medium text-gray-700 mb-2">
                                        配送方式 <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        id="shippingMethod"
                                        wire:model="shippingMethod"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('shippingMethod') border-red-300 @enderror"
                                    >
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($shippingMethodOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('shippingMethod')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="trackingNumber" class="block text-sm font-medium text-gray-700 mb-2">
                                        运单号
                                    </label>
                                    <input 
                                        type="text" 
                                        id="trackingNumber"
                                        wire:model="trackingNumber"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('trackingNumber') border-red-300 @enderror"
                                        placeholder="请输入运单号"
                                    />
                                    @error('trackingNumber')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        备注
                                    </label>
                                    <textarea 
                                        id="notes"
                                        wire:model="notes"
                                        rows="3"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 @error('notes') border-red-300 @enderror"
                                        placeholder="发货备注信息"
                                    ></textarea>
                                    @error('notes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button"
                                        wire:click="toggleShipmentForm"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        {{ __('app.cancel') }}
                                    </button>
                                    <button 
                                        type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors"
                                    >
                                        {{ __('app.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    {{-- 发货记录列表 --}}
                    @if($order->orderShipments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">配送方式</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">运单号</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">备注</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">创建时间</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($order->orderShipments as $shipment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $shipment->shipping_method->label() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $shipment->tracking_number ?: '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $shipment->notes ?: '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $shipment->created_at->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button 
                                                    wire:click="deleteShipment({{ $shipment->id }})"
                                                    wire:confirm="确定要删除这条发货记录吗？"
                                                    class="text-red-600 hover:text-red-700"
                                                >
                                                    {{ __('app.delete') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-sm text-gray-500">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p>暂无发货记录</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
