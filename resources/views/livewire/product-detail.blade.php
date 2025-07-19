@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency_code', 'CNY');
    $translation = $product->productTranslations->where('language_id', $lang->id)->first();
    $name = $translation && $translation->name ? $translation->name : $product->slug;
    $desc = $translation && $translation->description ? $translation->description : '';
    $shortDesc = $translation && $translation->short_description ? $translation->short_description : '';
    $variant = $variants->where('id', $selectedVariantId)->first();
    $images = $product->getMedia('images');
    $price = $variant && $variant->price ? $currencyService->convertWithSymbol($variant->price, $currencyCode) : '';
    $attributes = $product->attributeValues ?? collect();

    $breadcrumbs = [
        [
            'label' => __('app.categories'),
            'url' => locaRoute('product'),
        ],
        [
            'label' => $name,
            'url' => '',
        ],
    ];
    $tab = request()->input('tab', 'desc');
@endphp

<div class="max-w-7xl mx-auto px-6 py-2 min-h-screen bg-white">
    <x-breadcrumbs :items="$breadcrumbs" />
    <div class="flex flex-col md:flex-row gap-8">
        {{-- 商品图片幻灯片 --}}
        <div class="md:w-1/2 flex justify-center items-center">
            @if ($images->count())
                <div x-data="{ active: 0 }" class="w-full">
                    <div class="relative w-full aspect-square overflow-hidden rounded-xl shadow-lg">
                        @foreach ($images as $i => $img)
                            <img src="{{ $img->getUrl() }}" alt="{{ $name }}"
                                class="absolute inset-0 w-full h-full object-cover transition-all duration-500"
                                x-show="active === {{ $i }}">
                        @endforeach
                    </div>
                    <div class="flex justify-center gap-2 mt-3">
                        @foreach ($images as $i => $img)
                            <button type="button"
                                class="w-4 h-4 rounded-full border-2 border-green-600 {{ $i === 0 ? 'bg-green-600' : 'bg-white' }}"
                                :class="{
                                    'bg-green-600': active === {{ $i }},
                                    'bg-white': active !==
                                        {{ $i }}
                                }"
                                x-on:click="active = {{ $i }}"></button>
                        @endforeach
                    </div>
                </div>
            @else
                <img src="{{ asset('logo.png') }}" alt="{{ $name }}"
                    class="rounded-xl shadow-lg w-full object-cover">
            @endif
        </div>
        {{-- 商品信息 --}}
        <div class="md:w-1/2">
            <h1 class="text-3xl font-bold text-green-700 mb-2">{{ $name }}</h1>
            <div class="mb-2 text-gray-500">
                @if ($categoryNames)
                    <span class="mr-2">{{ __('home.categories') }}:</span>
                    @foreach ($categoryNames as $catName)
                        <span
                            class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded mr-1">{{ $catName }}</span>
                    @endforeach
                @endif
            </div>
            {{-- 商品属性 --}}
            @if ($attributes->count())
                <div class="mb-2 text-gray-700">
                    <span class="mr-2">{{ __('home.attributes') }}:</span>
                    @foreach ($attributes as $attrValue)
                        @php
                            $attrTrans = $attrValue->attributeValueTranslations
                                ->where('language_id', $lang?->id)
                                ->first();
                            $attrName = $attrTrans && $attrTrans->name ? $attrTrans->name : $attrValue->id;
                        @endphp
                        <span
                            class="inline-block bg-gray-100 text-gray-800 px-2 py-1 rounded mr-1">{{ $attrName }}</span>
                    @endforeach
                </div>
            @endif
            @if ($shortDesc)
                <div class="mb-4 text-gray-700">{{ $shortDesc }}</div>
            @endif
            <div class="mb-4">
                <span class="text-2xl font-bold text-green-700">{{ $price }}</span>
            </div>
            {{-- 规格参数 --}}
            @if ($variant)
                <div class="mb-4 text-gray-700">
                    <div class="grid grid-cols-2 gap-x-6 gap-y-2">
                        @if ($variant->weight)
                            <div>
                                <span class="font-semibold">{{ __('home.weight') }}:</span>
                                <span>{{ rtrim(rtrim(number_format($variant->weight, 2), '0'), '.') }} g</span>
                            </div>
                        @endif
                        @if ($variant->length)
                            <div>
                                <span class="font-semibold">{{ __('home.length') }}:</span>
                                <span>{{ rtrim(rtrim(number_format($variant->length, 2), '0'), '.') }} cm</span>
                            </div>
                        @endif
                        @if ($variant->width)
                            <div>
                                <span class="font-semibold">{{ __('home.width') }}:</span>
                                <span>{{ rtrim(rtrim(number_format($variant->width, 2), '0'), '.') }} cm</span>
                            </div>
                        @endif
                        @if ($variant->height)
                            <div>
                                <span class="font-semibold">{{ __('home.height') }}:</span>
                                <span>{{ rtrim(rtrim(number_format($variant->height, 2), '0'), '.') }} cm</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            {{-- 规格选择 --}}
            @if ($variants->count() > 1)
                <div class="mb-4">
                    <label class="block mb-2 font-semibold text-gray-700">{{ __('home.select_variant') }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($variants as $v)
                            @php
                                $specs = $v->specificationValues
                                    ->map(function ($sv) use ($lang) {
                                        $trans = $sv->specificationValueTranslations
                                            ->where('language_id', $lang?->id)
                                            ->first();
                                        return $trans && $trans->name ? $trans->name : $sv->id;
                                    })
                                    ->implode(' / ');
                            @endphp
                            <button wire:click="selectVariant({{ $v->id }})"
                                class="px-4 py-2 rounded border cursor-pointer {{ $selectedVariantId == $v->id ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $specs ?: $v->sku }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- 购买按钮 --}}
            <div class="mt-6">
                <button
                    class="w-full px-6 py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
                    {{ __('home.buy_now') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Tab 切换 --}}
    <div class="py-6">
        <div class="flex border-b mb-4">
            <a href="?tab=desc"
                class="px-4 py-2 font-semibold {{ $tab == 'desc' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500' }}">{{ __('home.product_description') }}</a>
            <a href="?tab=reviews"
                class="px-4 py-2 font-semibold {{ $tab == 'reviews' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500' }}">{{ __('home.product_reviews') }}</a>
        </div>
        @if ($tab == 'desc')
            @if ($desc)
                <div class="prose max-w-none text-gray-800 px-6">
                    {!! nl2br(e($desc)) !!}
                </div>
            @endif
        @else
            <div class="px-6">
                @livewire('components.product-reviews', ['productId' => $product->id], key('product-reviews-' . $product->id))
            </div>
        @endif
    </div>

</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ $name }}" description="{{ $shortDesc }}"
        image="{{ $images->first()->getUrl() }}" />
@endPushOnce
