@php
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="max-w-7xl mx-auto px-4 py-10 min-h-[70vh] bg-white">
    <h1 class="text-3xl font-extrabold text-teal-700 mb-8 tracking-tight">{{ __('app.cart') }}</h1>
    <form wire:submit.prevent="checkout">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <table class="w-full text-left mb-6 border-separate border-spacing-y-2">
                <thead>
                    <tr class="border-b text-gray-700 text-sm">
                        <th class="py-2 px-2 w-10">
                            <input type="checkbox" wire:model="selectAll" wire:click="toggleSelectAll">
                        </th>
                        <th class="py-2 px-2">{{ __('app.product') }}</th>
                        <th class="py-2 px-2">{{ __('app.specification') }}</th>
                        <th class="py-2 px-2">{{ __('app.price') }}</th>
                        <th class="py-2 px-2">{{ __('app.qty') }}</th>
                        <th class="py-2 px-2">{{ __('app.total') }}</th>
                        <th class="py-2 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cartItems as $item)
                        @php
                            $product = $item->product;
                            $variant = $item->productVariant;
                            $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                            $name = $translation && $translation->name ? $translation->name : ($product->productTranslations->first()->name ?? $product->slug);
                            $image = $variant ? $variant->getFirstMediaUrl('image', 'thumb') : ($product->productVariants->first()?->getFirstMediaUrl('image', 'thumb') ?: asset('logo.svg'));
                            $specs = $variant ? $variant->specificationValues->map(function ($sv) use ($lang) {
                                $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                return $trans && $trans->name ? $trans->name : $sv->id;
                            })->implode(' / ') : '';
                            $price = $variant && $variant->price ? $currencyService->convertWithSymbol($variant->price, $currencyCode) : '';
                            $finalPrice = $item->final_price ?? ($variant && $variant->price ? $variant->price : 0);
                            $promotion = $item->promotion ?? null;
                        @endphp
                        <tr class="bg-gray-50 rounded-lg shadow-sm">
                            <td class="py-2 px-2 align-middle">
                                <input type="checkbox" wire:model="selected" value="{{ $item->id }}">
                            </td>
                            <td class="py-2 px-2 flex items-center gap-3 align-middle">
                                <img src="{{ $image }}" alt="{{ $name }}" class="w-14 h-14 object-cover rounded-lg border">
                                <span class="font-semibold text-gray-900">{{ $name }}</span>
                            </td>
                            <td class="py-2 px-2 text-xs text-gray-500 align-middle">{{ $specs }}</td>
                            <td class="py-2 px-2 font-bold text-teal-700 align-middle">
                                @if ($promotion)
                                    <span>{{ $currencyService->convertWithSymbol($finalPrice, $currencyCode) }}</span>
                                    <span class="text-gray-400 line-through ml-2">{{ $price }}</span>
                                    <span class="ml-2 text-xs text-red-500 font-semibold">
                                        @php
                                            $rule = $promotion['rule'] ?? [];
                                            // 处理 discount_type：可能是枚举对象、数组或字符串
                                            $discountType = null;
                                            if (is_array($rule)) {
                                                $discountType = $rule['discount_type'] ?? null;
                                                // 如果是枚举对象，获取其值
                                                if (is_object($discountType) && method_exists($discountType, 'value')) {
                                                    $discountType = $discountType->value;
                                                } elseif (is_object($discountType)) {
                                                    $discountType = (string) $discountType;
                                                }
                                            } elseif (is_object($rule)) {
                                                $discountType = $rule->discount_type ?? null;
                                                if (is_object($discountType) && method_exists($discountType, 'value')) {
                                                    $discountType = $discountType->value;
                                                } elseif (is_object($discountType)) {
                                                    $discountType = (string) $discountType;
                                                }
                                            }
                                            
                                            // 处理 discount_value
                                            $discountValue = is_array($rule) ? ($rule['discount_value'] ?? null) : (is_object($rule) ? ($rule->discount_value ?? null) : null);
                                            if (is_object($discountValue)) {
                                                $discountValue = (string) $discountValue;
                                            }
                                        @endphp
                                        @if ($discountType == 'percent' && $discountValue !== null)
                                            {{ __('app.discount_percent', ['percent' => (string) $discountValue]) }}
                                        @else
                                            {{ __('app.discount_amount', ['amount' => $currencyService->convertWithSymbol($promotion['discount'] ?? 0, $currencyCode)]) }}
                                        @endif
                                    </span>
                                    @if (!empty($promotion['description']))
                                        @php
                                            $description = $promotion['description'];
                                            if (is_array($description)) {
                                                $description = json_encode($description, JSON_UNESCAPED_UNICODE);
                                            } elseif (!is_string($description)) {
                                                $description = (string) $description;
                                            }
                                        @endphp
                                        <div class="text-xs text-red-600 mt-1">{{ $description }}</div>
                                    @endif
                                @else
                                    <span>{{ $price }}</span>
                                @endif
                            </td>
                            <td class="py-2 px-2 align-middle">
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ $item->qty - 1 }})" class="px-2 w-10 py-1 bg-gray-100 rounded hover:bg-gray-200 font-bold text-lg">-</button>
                                    <input type="number" wire:change="updateQty({{ $item->id }}, $event.target.value)" value="{{ $item->qty }}" min="1" class="w-14 py-1 text-center border rounded font-semibold">
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ $item->qty + 1 }})" class="px-2 w-10 py-1 bg-gray-100 rounded hover:bg-gray-200 font-bold text-lg">+</button>
                                </div>
                            </td>
                            <td class="py-2 px-2 font-bold text-teal-700 align-middle">
                                {{ $currencyService->convertWithSymbol($item->qty * $finalPrice, $currencyCode) }}
                            </td>
                            <td class="py-2 px-2 align-middle">
                                <button type="button" wire:click="removeItem({{ $item->id }})" class="text-red-500 hover:text-red-700 transition">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500">{{ __('app.empty_cart') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
                <div class="flex gap-2">
                    <button type="button" wire:click="toggleSelectAll" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 font-medium">
                        {{ __('app.select_all') }}
                    </button>
                    <button type="button" wire:click="toggleInverse" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 font-medium">
                        {{ __('app.inverse_select') }}
                    </button>
                </div>
                <div class="text-2xl font-extrabold text-teal-700">
                    {{ __('app.total') }}: {{ $currencyService->convertWithSymbol($total, $currencyCode) }}
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-teal-600 text-white rounded-lg font-bold hover:bg-teal-700 transition text-lg shadow">
                    {{ __('app.checkout') }}
                </button>
            </div>
        </div>
    </form>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ __('app.cart') }}" description="{{ __('app.cart') }}"
        keywords="{{ __('app.cart') }}" />
@endPushOnce
