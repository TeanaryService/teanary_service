@php
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="max-w-7xl mx-auto px-4 py-10 min-h-screen bg-white">
    <h1 class="text-2xl font-bold text-green-700 mb-8">{{ __('app.cart') }}</h1>
    <form wire:submit.prevent="checkout">
        <div class="bg-gray-50 rounded-lg shadow p-6">
            <table class="w-full text-left mb-6">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 px-2">
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
                            $image = $variant ? $variant->getFirstMediaUrl('image', 'thumb') : ($product->productVariants->first()?->getFirstMediaUrl('image', 'thumb') ?: asset('logo.png'));
                            $specs = $variant ? $variant->specificationValues->map(function ($sv) use ($lang) {
                                $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                return $trans && $trans->name ? $trans->name : $sv->id;
                            })->implode(' / ') : '';
                            $price = $variant && $variant->price ? $currencyService->convertWithSymbol($variant->price, $currencyCode) : '';
                        @endphp
                        <tr class="border-b">
                            <td class="py-2 px-2">
                                <input type="checkbox" wire:model="selected" value="{{ $item->id }}">
                            </td>
                            <td class="py-2 px-2 flex items-center gap-2">
                                <img src="{{ $image }}" alt="{{ $name }}" class="w-12 h-12 object-cover rounded">
                                <span>{{ $name }}</span>
                            </td>
                            <td class="py-2 px-2 text-xs text-gray-500">{{ $specs }}</td>
                            <td class="py-2 px-2 font-bold text-green-700">{{ $price }}</td>
                            <td class="py-2 px-2">
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ $item->qty - 1 }})" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">-</button>
                                    <input type="number" wire:change="updateQty({{ $item->id }}, $event.target.value)" value="{{ $item->qty }}" min="1" class="w-12 text-center border rounded">
                                    <button type="button" wire:click="updateQty({{ $item->id }}, {{ $item->qty + 1 }})" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">+</button>
                                </div>
                            </td>
                            <td class="py-2 px-2 font-bold text-green-700">
                                {{ $currencyService->convertWithSymbol($item->qty * ($variant->price ?? 0), $currencyCode) }}
                            </td>
                            <td class="py-2 px-2">
                                <button type="button" wire:click="removeItem({{ $item->id }})" class="text-red-500 hover:text-red-700">
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
            <div class="flex justify-between items-center mt-6">
                <div>
                    <button type="button" wire:click="toggleSelectAll" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200 mr-2">
                        {{ __('app.select_all') }}
                    </button>
                    <button type="button" wire:click="toggleInverse" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">
                        {{ __('app.inverse_select') }}
                    </button>
                </div>
                <div class="text-xl font-bold text-green-700">
                    {{ __('app.total') }}: {{ $currencyService->convertWithSymbol($total, $currencyCode) }}
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
                    {{ __('app.checkout') }}
                </button>
            </div>
        </div>
    </form>
</div>
