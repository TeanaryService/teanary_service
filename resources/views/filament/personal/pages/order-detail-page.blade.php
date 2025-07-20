<x-filament-panels::page>
    @php
        $currencyService = app(\App\Services\LocaleCurrencyService::class);
        $currencyCode = session('currency', $currencyService->getDefaultCurrencyCode());
        $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
        $order = $order ?? null;
    @endphp

    @if ($order)
        <div class="bg-white rounded-xl shadow p-6">
            <div class="mb-2">
                <span class="font-bold text-primary-600">{{ __('personal.order_no') }}: {{ $order->order_no }}</span>
                <span class="ml-4 text-gray-500">{{ __('personal.order_status') }}:
                    {{ __('personal.order_status_' . $order->status->value) }}</span>
            </div>
            <div class="mb-2 text-gray-700">
                <span>{{ __('personal.order_created_at') }}: {{ $order->created_at->format('Y-m-d H:i') }}</span>
            </div>
            <div class="mb-2 text-gray-700">
                <span>{{ __('personal.shipping_method') }}:
                    {{ $order->shippingMethod->shippingMethodTranslations->where('language_id', $lang?->id)->first()?->name ?? ($order->shippingMethod->code ?? '-') }}
                </span>
            </div>
            <div class="mb-2 text-gray-700">
                <span>{{ __('personal.payment_method') }}:
                    {{ $order->paymentMethod->paymentMethodTranslations->where('language_id', $lang?->id)->first()?->name ?? ($order->paymentMethod->code ?? '-') }}
                </span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-bold mb-4">{{ __('personal.order_items') }}</h2>
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
                                $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                                return $trans && $trans->name ? $trans->name : $sv->id;
                            })
                            ->implode(' / ')
                        : '';
                    $price = $currencyService->convertWithSymbol($item->price, $currencyCode);
                @endphp
                <div class="flex items-center gap-3 py-2 border-b">
                    <img src="{{ $image }}" alt="{{ $name }}"
                        class="w-14 h-14 object-cover rounded-lg border">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $name }}</div>
                        <div class="text-xs text-gray-500">{{ $specs }}</div>
                        <div class="text-primary-600 font-bold">{{ $price }} × {{ $item->qty }}</div>
                    </div>
                </div>
            @endforeach
            <div class="mt-4 text-right text-xl font-bold text-primary-600">
                {{ __('personal.order_total') }}:
                {{ $currencyService->convertWithSymbol($order->total, $currencyCode) }}
            </div>
        </div>
    @else
        <div class="py-12 text-center text-gray-500">{{ __('personal.no_order_detail') }}</div>
    @endif
</x-filament-panels::page>
