<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    <button x-on:click="open = !open" class="relative flex items-center">
        <x-heroicon-o-shopping-cart class="w-7 h-7 text-green-700" />
        @if($cartItems->count())
            <span class="absolute -top-2 -right-2 bg-green-600 text-white text-xs rounded-full px-2 py-0.5">{{ $cartItems->count() }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg z-50 border border-gray-200">
        <div class="p-4 border-b font-bold text-green-700 flex justify-between items-center">
            <span>{{ __('app.cart') }}</span>
            <button x-on:click="open = false" class="text-gray-400 hover:text-green-600">
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
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
                    $price = $variant && $variant->price ? app(\App\Services\LocaleCurrencyService::class)->convertWithSymbol($variant->price, session('currency_code', 'CNY')) : '';
                @endphp
                <div class="flex items-center gap-4 p-4 border-b">
                    <img src="{{ $image }}" alt="{{ $name }}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <div class="font-semibold text-green-900">{{ $name }}</div>
                        @if($specs)
                            <div class="text-xs text-gray-500">{{ $specs }}</div>
                        @endif
                        <div class="text-green-700 font-bold mt-1">{{ $price }}</div>
                        <div class="flex items-center gap-2 mt-2">
                            <button wire:click="updateQty({{ $item->id }}, {{ $item->qty - 1 }})" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">-</button>
                            <input type="number" wire:change="updateQty({{ $item->id }}, $event.target.value)" value="{{ $item->qty }}" min="1" class="w-12 text-center border rounded">
                            <button wire:click="updateQty({{ $item->id }}, {{ $item->qty + 1 }})" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">+</button>
                            <button wire:click="removeItem({{ $item->id }})" class="ml-2 text-red-500 hover:text-red-700">
                                <x-heroicon-o-trash class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">{{ __('app.empty_cart') }}</div>
            @endforelse
        </div>
        <div class="p-4 border-t flex justify-between items-center">
            <span class="font-bold">{{ __('app.total') }}</span>
            <span class="text-green-700 font-bold">{{ $cartTotal }}</span>
        </div>
        <div class="p-4">
            <a href="{{ locaRoute('cart') }}" class="w-full block text-center bg-green-600 text-white py-2 rounded font-bold hover:bg-green-700 transition">
                {{ __('app.view_cart') }}
            </a>
        </div>
    </div>
</div>