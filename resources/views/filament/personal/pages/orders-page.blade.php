<x-filament-panels::page>
    @php
        $currencyService = app(\App\Services\LocaleCurrencyService::class);
        $currencyCode = session('currency');
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
    @endphp

    <div class="py-10">
        <form method="GET" class="py-6 flex gap-2 items-center">
            <label class="font-semibold text-gray-700">{{ __('personal.order_status') }}</label>

            <x-filament::input.wrapper>
                <x-filament::input.select id="status" name="status" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach ($statuses as $key => $label)
                        <option value="{{ $key }}" @if (request('status', '') == $key) selected @endif>
                            {{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </form>
        <div class="bg-white rounded-xl shadow-lg p-8">
            @forelse($this->orders as $order)
                <div class="border-b p-6">
                    <div class="flex justify-between items-center pb-6">
                        <div class="font-bold text-primary-600 text-lg">{{ __('personal.order_no') }}:
                            {{ $order->order_no }}
                        </div>
                        <x-filament::badge color="danger" size="xl">
                            {{ __('personal.order_status_' . $order->status->value) }}
                        </x-filament::badge>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($order->orderItems as $item)
                            @php
                                $product = $item->product;
                                $variant = $item->productVariant;
                                $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                                $name =
                                    $translation && $translation->name
                                        ? $translation->name
                                        : $product->productTranslations->first()->name ?? $product->slug;
                                $image = $variant
                                    ? $variant->getFirstMediaUrl('image', 'thumb')
                                    : ($product->productVariants->first()?->getFirstMediaUrl('image', 'thumb') ?:
                                    asset('logo.png'));
                                $specs = $variant
                                    ? $variant->specificationValues
                                        ->map(function ($sv) use ($lang) {
                                            $trans = $sv->specificationValueTranslations
                                                ->where('language_id', $lang?->id)
                                                ->first();
                                            return $trans && $trans->name ? $trans->name : $sv->id;
                                        })
                                        ->implode(' / ')
                                    : '';
                                $price = $currencyService->convertWithSymbol($item->price, $currencyCode);
                            @endphp
                            <a href="{{ locaRoute('product.show', ['id' => $product->id]) }}" target="_blank"
                                class="flex items-center gap-4">
                                <img src="{{ $image }}" alt="{{ $name }}"
                                    class="w-16 h-16 object-cover rounded-lg border shadow-sm">
                                <div>
                                    <div class="font-semibold text-gray-900 text-base">{{ $name }}</div>
                                    <div class="text-xs text-gray-500 mb-1">{{ $specs }}</div>
                                    <div class="text-primary-600 font-bold">{{ $price }} × {{ $item->qty }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center pt-6">
                        <x-filament::button wire:click="viewDetail({{ $order->id }})">
                            {{ __('personal.view_detail') }}
                        </x-filament::button>

                        <div class="text-right text-xl font-extrabold text-primary-600">
                            {{ __('personal.order_total') }}:
                            {{ $currencyService->convertWithSymbol($order->total, $currencyCode) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-gray-500 text-lg">{{ __('personal.no_orders') }}</div>
            @endforelse
            <div class="mt-8">
                {{ $this->orders->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
