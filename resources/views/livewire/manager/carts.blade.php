@php
    $breadcrumbs = buildManagerCenterBreadcrumbs('carts', __('manager.carts.label'));
@endphp

<div class="min-h-[60vh] mb-10 bg-tea-50 tea-bg-texture">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <x-widgets.breadcrumbs :items="$breadcrumbs" />
        
        <div class="flex flex-col md:flex-row gap-6">
            <x-manager.sidebar active="carts" />
            
            <div class="flex-1">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('manager.carts.label') }}</h1>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 rounded-md bg-teal-50 p-4">
                        <p class="text-sm font-medium text-teal-800">{{ session('message') }}</p>
                    </div>
                @endif

                {{-- 筛选器 --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-widgets.label>{{ __('app.search') }}</x-widgets.label>
                            <x-widgets.input 
                                type="text" 
                                wire="live.debounce.300ms=search"
                                placeholder="{{ __('app.search_placeholder') }}"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.cart.user') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterUserId" 
                                :options="[['value' => '', 'label' => __('app.all')], ...collect($users)->map(fn($user) => ['value' => $user->id, 'label' => $user->name])->toArray()]"
                            />
                        </div>
                        <div>
                            <x-widgets.label>{{ __('manager.cart.has_items') }}</x-widgets.label>
                            <x-widgets.select 
                                wire="live=filterHasItems" 
                                :options="[
                                    ['value' => '', 'label' => __('app.all')],
                                    ['value' => '1', 'label' => __('manager.cart.has_items')],
                                    ['value' => '0', 'label' => __('manager.cart.empty')]
                                ]"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-widgets.button 
                            wire:click="resetFilters"
                            variant="secondary"
                        >
                            {{ __('app.reset') }}
                        </x-widgets.button>
                    </div>
                </div>

                {{-- 购物车列表 --}}
                <div class="space-y-6">
                    @forelse($carts as $cart)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            {{-- 购物车头部 --}}
                            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">{{ __('manager.cart.id') }}:</span>
                                            <span class="text-sm font-semibold text-gray-900 ml-2">#{{ $cart->id }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">{{ __('manager.cart.user') }}:</span>
                                            <span class="text-sm text-gray-900 ml-2">{{ $cart->user?->name ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">{{ __('manager.cart.items_count') }}:</span>
                                            <span class="text-sm font-semibold text-gray-900 ml-2">{{ $cart->cart_items_count }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">{{ __('app.created_at') }}: {{ $cart->created_at->format('Y-m-d H:i') }}</span>
                                        <button 
                                            wire:click="deleteCart({{ $cart->id }})"
                                            wire:confirm="{{ __('app.confirm_delete') }}"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- 购物车项列表 --}}
                            @if($cart->cartItems->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('manager.cart_item.product') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('manager.cart_item.variant') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('manager.cart_item.price') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('manager.cart_item.qty') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ __('manager.cart_item.subtotal') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($cart->cartItems as $item)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 text-sm text-gray-900">
                                                        <div class="font-medium">{{ $this->getProductName($item->product, $lang) }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600">
                                                        <div class="max-w-xs">{{ $this->getVariantSpecifications($item->productVariant, $lang) }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                                        {{ $this->getItemPrice($item, $service, $currentCurrencyCode) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                                        {{ $item->qty }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                                        {{ $this->getItemSubtotal($item, $service, $currentCurrencyCode) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                                    {{ __('app.total') }}:
                                                </td>
                                                <td class="px-6 py-4 text-right text-sm font-bold text-teal-600">
                                                    @php
                                                        $total = 0;
                                                        foreach ($cart->cartItems as $item) {
                                                            $price = null;
                                                            if ($item->productVariant && $item->productVariant->price) {
                                                                $price = $item->productVariant->price;
                                                            } elseif ($item->product && $item->product->relationLoaded('productVariants')) {
                                                                $variant = $item->product->productVariants->first();
                                                                $price = $variant ? $variant->price : null;
                                                            }
                                                            $total += ($price ?? 0) * ($item->qty ?? 0);
                                                        }
                                                    @endphp
                                                    {{ $service->convertWithSymbol($total, $currentCurrencyCode) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="px-6 py-12 text-center text-sm text-gray-500">
                                    <svg class="w-10 h-10 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>{{ __('manager.cart.empty') }}</span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="text-sm text-gray-500">{{ __('app.no_data') }}</span>
                        </div>
                    @endforelse
                </div>

                {{-- 分页 --}}
                <div class="mt-6">
                    {{ $carts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<x-seo-meta title="{{ __('manager.carts.label') }}" />
