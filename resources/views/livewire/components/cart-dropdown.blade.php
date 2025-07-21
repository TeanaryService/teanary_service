<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    <!-- Cart Icon -->
    <button x-on:click="open = !open" class="relative flex items-center">
        <x-heroicon-o-shopping-cart class="w-7 h-7 text-green-700" />
        @if ($cartItems->count())
            <span
                class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">
                {{ $cartItems->count() }}
            </span>
        @endif
    </button>

    <!-- Dropdown Cart -->
    <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed md:absolute left-0 mt-3 w-screen md:w-76 lg:w-96 bg-white rounded-xl shadow-xl z-50 border border-gray-200 overflow-hidden">

        <!-- Header -->
        <div class="p-4 bg-gray-50 border-b border-green-100 flex justify-between items-center">
            <h3 class="font-bold text-green-700">{{ __('app.cart') }}</h3>
            <button x-on:click="open = false" class="text-gray-400 hover:text-green-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>

        <!-- Items -->
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
            @php
                $currencyService = app(\App\Services\LocaleCurrencyService::class);
                $currencyCode = session('currency');
            @endphp
            @forelse($cartItems as $item)
                @php
                    $product = $item->product;
                    $variant = $item->productVariant;
                    $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                    $name = $translation && $translation->name ? $translation->name : ($product->productTranslations->first()->name ?? $product->slug);
                    $image = $variant ? $variant->getFirstMediaUrl('image', 'thumb') : ($product->productVariants->first()?->getFirstMediaUrl('image', 'thumb') ?: asset('logo.png'));
                    $specs = $variant ? $variant->specificationValues->map(function ($sv) use ($lang) {
                        $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                        return $trans && $trans->name ? $trans->name : $sv->id;
                    })->implode(' / ') : '';
                    $price = $variant && $variant->price ? $currencyService->convertWithSymbol($variant->price, $currencyCode) : '';
                    $finalPrice = $item->final_price ?? ($variant && $variant->price ? $variant->price : 0);
                    $promotion = $item->promotion ?? null;
                @endphp

                <div class="flex items-start gap-4 p-4">
                    <img src="{{ $image }}" alt="{{ $name }}"
                        class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                    <div class="flex-1 space-y-1">
                        <div class="font-semibold text-green-900 line-clamp-2">{{ $name }}</div>
                        @if ($specs)
                            <div class="text-xs text-gray-500">{{ $specs }}</div>
                        @endif
                        <div>
                            @if ($promotion)
                                <span class="text-green-700 font-bold">{{ $currencyService->convertWithSymbol($finalPrice, $currencyCode) }}</span>
                                <span class="text-gray-400 line-through ml-2">{{ $price }}</span>
                                <span class="ml-2 text-xs text-red-500 font-semibold">
                                    {{ $promotion['rule']['discount_type'] == 'percent' ? __('app.discount_percent', ['percent' => $promotion['rule']['discount_value']]) : __('app.discount_amount', ['amount' => $currencyService->convertWithSymbol($promotion['discount'], $currencyCode)]) }}
                                </span>
                            @else
                                <span class="text-green-700 font-bold">{{ $price }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1 mt-2">
                            <button wire:click="updateQty({{ $item->id }}, {{ $item->qty - 1 }})"
                                class="w-7 h-7 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200">
                                -
                            </button>
                            <input type="number" wire:change="updateQty({{ $item->id }}, $event.target.value)"
                                value="{{ $item->qty }}" min="1"
                                class="w-14 text-center border rounded text-md">
                            <button wire:click="updateQty({{ $item->id }}, {{ $item->qty + 1 }})"
                                class="w-7 h-7 flex items-center justify-center bg-gray-100 rounded hover:bg-gray-200">
                                +
                            </button>

                            <button wire:click="removeItem({{ $item->id }})"
                                class="ml-2 text-red-500 hover:text-red-700">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">{{ __('app.empty_cart') }}</div>
            @endforelse
        </div>

        <!-- Total & Action -->
        <div class="p-4 border-t bg-gray-50 border-green-100">
            <div class="flex justify-between items-center text-green-800 font-bold mb-3 text-base">
                <span>{{ __('app.total') }}</span>
                <span>{{ $currencyService->convertWithSymbol($cartTotal, $currencyCode) }}</span>
            </div>
            <a href="{{ locaRoute('cart') }}"
                class="w-full block text-center bg-green-600 text-white py-2.5 rounded-lg font-semibold hover:bg-green-700 transition">
                {{ __('app.checkout') }}
            </a>
        </div>
    </div>
</div>