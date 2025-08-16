@php
    $locale = session('lang');
    $lang = app(\App\Services\LocaleCurrencyService::class)->getLanguageByCode($locale);
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
    $translation = $product->productTranslations->where('language_id', $lang->id)->first();
    $name = $translation && $translation->name ? $translation->name : $product->slug;
    $desc = $translation && $translation->description ? $translation->description : '';
    $shortDesc = $translation && $translation->short_description ? $translation->short_description : '';
    $variant = $variants->where('id', $selectedVariantId)->first();
    $images = $product->getMedia('images');
    $price = isset($finalPrice)
        ? $currencyService->convertWithSymbol($finalPrice, $currencyCode)
        : ($variant && $variant->price
            ? $currencyService->convertWithSymbol($variant->price, $currencyCode)
            : '');
    $attributes = $product->attributeValues ?? collect();

    $productId = $product->id;
    $variantId = $selectedVariantId;
    $qty = 1;
    $maxQty = $variant && $variant->stock ? $variant->stock : 1;

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

    // 准备结构化数据
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $name,
        'description' => $shortDesc,
        'image' => $images->first()?->getUrl(),
        'sku' => $variant?->sku,
    ];

    if ($price) {
        $structuredData['offers'] = [
            '@type' => 'Offer',
            'url' => url()->current(),
            'priceCurrency' => $currencyCode,
            'price' => str_replace(['$', '€', '£', '¥'], '', $price),
            'availability' =>
                $variant && $variant->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ];
    }

    if ($attributes->count()) {
        $structuredData['additionalProperty'] = $attributes
            ->map(function ($attrValue) use ($lang) {
                $attrTrans = $attrValue->attribute->attributeTranslations->where('language_id', $lang?->id)->first();

                $attrValueTrans = $attrValue->attributeValueTranslations->where('language_id', $lang?->id)->first();

                // 属性名和属性值翻译
                $attrName = $attrTrans && $attrTrans->name ? $attrTrans->name : $attrValue->attribute->id;
                $attrValueName = $attrValueTrans && $attrValueTrans->name ? $attrValueTrans->name : $attrValue->id;

                return [
                    '@type' => 'PropertyValue',
                    'name' => "{$attrName}: {$attrValueName}",
                ];
            })
            ->values()
            ->all();
    }

@endphp

<div class="max-w-7xl mx-auto px-6 min-h-[70vh]">
    <x-breadcrumbs :items="$breadcrumbs" />
    <div class="flex flex-col lg:flex-row gap-8 items-start">
        {{-- 商品图片幻灯片 --}}
        <div class="w-full lg:w-1/2 flex justify-center items-center">
            @if ($images->count())
                <!-- Alpine.js 数据和方法 -->
                <div x-data="{
                    active: 0,
                    total: {{ $images->count() }},
                    autoplay: null,
                    isHovered: false,
                    isZoomed: false,
                    mouseX: 0,
                    mouseY: 0,
                    init() {
                        this.startAutoplay();
                    },
                    startAutoplay() {
                        this.autoplay = setInterval(() => {
                            if (!this.isHovered) {
                                this.next();
                            }
                        }, 3000);
                    },
                    stopAutoplay() {
                        if (this.autoplay) {
                            clearInterval(this.autoplay);
                        }
                    },
                    next() {
                        this.active = (this.active + 1) % this.total;
                    },
                    prev() {
                        this.active = (this.active - 1 + this.total) % this.total;
                    },
                    goTo(index) {
                        this.active = index;
                    },
                    handleMouseEnter() {
                        this.isHovered = true;
                        this.isZoomed = true;
                    },
                    handleMouseLeave() {
                        this.isHovered = false;
                        this.isZoomed = false;
                    },
                    handleMouseMove(e) {
                        if (this.isZoomed) {
                            const rect = e.currentTarget.getBoundingClientRect();
                            this.mouseX = ((e.clientX - rect.left) / rect.width) * 100;
                            this.mouseY = ((e.clientY - rect.top) / rect.height) * 100;
                        }
                    }
                }" x-init="init()" @destroy="stopAutoplay()" class="w-full">

                    <!-- 主图显示区域 -->
                    <div class="relative w-full aspect-square overflow-hidden rounded-xl shadow-lg cursor-crosshair"
                        @mouseenter="handleMouseEnter()" @mouseleave="handleMouseLeave()"
                        @mousemove="handleMouseMove($event)">

                        @foreach ($images as $i => $img)
                            <div class="absolute inset-0 w-full h-full transition-all duration-500"
                                x-show="active === {{ $i }}"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-300"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                                <img src="{{ $img->getUrl() }}" alt="{{ $name }}"
                                    class="w-full h-full object-cover transition-transform duration-300"
                                    :class="{
                                        'scale-100': !isZoomed
                                    }"
                                    :style="isZoomed ?
                                        `transform-origin: ${mouseX}% ${mouseY}%; max-width: 750px; max-height: 750px; transform: scale(2);` :
                                        ''">
                            </div>
                        @endforeach

                        <!-- 左右切换按钮 -->
                        @if ($images->count() > 1)
                            <button type="button"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-all duration-200 opacity-0 group-hover:opacity-100"
                                :class="{ 'opacity-100': isHovered }" @click="prev()">
                                <x-heroicon-o-arrow-small-left class="w-4 h-4" />
                            </button>

                            <button type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-all duration-200 opacity-0 group-hover:opacity-100"
                                :class="{ 'opacity-100': isHovered }" @click="next()">
                                <x-heroicon-o-arrow-small-right class="w-4 h-4" />
                            </button>
                        @endif

                        <!-- 进度条 -->
                        @if ($images->count() > 1)
                            <div class="absolute bottom-2 left-2 right-2">
                                <div class="w-full bg-black/20 rounded-full h-1">
                                    <div class="bg-white/80 h-1 rounded-full transition-all duration-100"
                                        :style="`width: ${((active + 1) / total) * 100}%`"></div>
                                </div>
                            </div>
                        @endif

                        <!-- 放大提示 -->
                        <div class="absolute top-2 right-2 opacity-0 transition-opacity duration-200"
                            :class="{ 'opacity-100': !isZoomed }">
                            <div class="bg-black/50 text-white text-xs px-2 py-1 rounded flex items-center gap-1">
                                <x-heroicon-o-magnifying-glass class="w-3 h-3" />
                                {{ __('app.zoom_on_hover') }}
                            </div>
                        </div>
                    </div>

                    <!-- 缩略图导航 -->
                    @if ($images->count() > 1)
                        <div class="flex justify-center gap-4 mt-3">
                            @foreach ($images as $i => $img)
                                <button type="button"
                                    class="relative w-24 h-24 rounded-lg overflow-hidden border-2 transition-all duration-200 hover:scale-105"
                                    :class="{
                                        'border-teal-600 ring-2 ring-teal-200': active === {{ $i }},
                                        'border-gray-300 hover:border-teal-400': active !== {{ $i }}
                                    }"
                                    @click="goTo({{ $i }})">
                                    <img src="{{ $img->getUrl() }}" alt="{{ $name }}"
                                        class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/20 transition-opacity duration-200"
                                        :class="{
                                            'opacity-0': active === {{ $i }},
                                            'opacity-100': active !== {{ $i }}
                                        }">
                                    </div>
                                </button>
                            @endforeach
                        </div>

                        <!-- 圆点导航（备选） -->
                        {{-- <div class="flex justify-center gap-2 mt-2">
                            @foreach ($images as $i => $img)
                                <button type="button" class="w-2 h-2 rounded-full transition-all duration-200"
                                    :class="{
                                        'bg-teal-600 w-6': active === {{ $i }},
                                        'bg-gray-300 hover:bg-teal-400': active !== {{ $i }}
                                    }"
                                    @click="goTo({{ $i }})"></button>
                            @endforeach
                        </div> --}}
                    @endif
                </div>
            @else
                <img src="{{ asset('logo.svg') }}" alt="{{ $name }}"
                    class="rounded-xl shadow-lg w-full object-cover">
            @endif
        </div>

        {{-- 商品信息 --}}
        <div class="w-full lg:w-1/2 bg-gray-50 rounded-xl p-5">
            <h1 class="text-3xl font-bold text-teal-700 mb-2">{{ $name }}</h1>
            <div class="mb-2 text-gray-500">
                @if ($categoryNames)
                    <span class="mr-2">{{ __('home.categories') }}:</span>
                    @foreach ($categoryNames as $catName)
                        <span
                            class="inline-block bg-teal-100 text-teal-800 px-2 py-1 rounded mr-1">{{ $catName }}</span>
                    @endforeach
                @endif
            </div>

            {{-- 商品属性 --}}
            @if ($attributes->count())
                <div class="mb-2 text-gray-700 flex flex-col gap gap-2">
                    {{-- <span class="mr-2">{{ __('home.attributes') }}:</span> --}}
                    @foreach ($attributes as $attrValue)
                        @php
                            $attrTrans = $attrValue->attribute->attributeTranslations
                                ->where('language_id', $lang?->id)
                                ->first();
                            $attrValueTrans = $attrValue->attributeValueTranslations
                                ->where('language_id', $lang?->id)
                                ->first();

                            $attrName = $attrTrans && $attrTrans->name ? $attrTrans->name : $attrValue->attribute->id;
                            $attrValueName =
                                $attrValueTrans && $attrValueTrans->name ? $attrValueTrans->name : $attrValue->id;
                        @endphp
                        <div class="text-gray-800 flex gap gap-2 py-0.5">
                            <p> {{ $attrName }}: </p>
                            <p>{{ $attrValueName }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($shortDesc)
                <div class="mb-4 text-gray-700">{{ $shortDesc }}</div>
            @endif
            <div class="mb-4">
                <span class="text-2xl font-bold text-teal-700">{{ $price }}</span>
                @if ($variant && $variant->weight)
                    <span class="ml-2 text-sm text-gray-500">
                        ({{ $currencyService->convertWithSymbol($variant->price / $variant->weight, $currencyCode) }}/g)
                    </span>
                @endif
            </div>

            {{-- 显示所有可用促销信息 --}}
            @if (!empty($availablePromotions) && count($availablePromotions) > 0)
                <div class="mb-4 p-3 bg-red-50 rounded-lg">
                    <div class="space-y-2">
                        @foreach ($availablePromotions as $promo)
                            <div class="text-sm">
                                <div class="flex items-start gap-2">
                                    <div class="shrink-0 w-4 h-4 mt-0.5 text-red-500">
                                        <x-heroicon-s-tag />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-red-700">{{ $promo['name'] }}</div>
                                        @if ($promo['description'])
                                            <div class="text-red-600 mt-0.5">{{ $promo['description'] }}</div>
                                        @endif

                                        {{-- 显示促销规则 --}}
                                        @if (!empty($promo['rules']))
                                            <div class="mt-1 space-y-1">
                                                @foreach ($promo['rules'] as $rule)
                                                    <div class="flex items-center gap-1 text-gray-600">
                                                        <span class="text-xs">
                                                            {{ __('home.promotion_text', [
                                                                'condition' => __('home.promotion_rule_' . ($rule['condition_type'] ?? '')),
                                                                'condition_value' => isset($rule['condition_value'])
                                                                    ? ($rule['condition_type'] === 'order_qty_min'
                                                                        ? $rule['condition_value']
                                                                        : $currencyService->convertWithSymbol($rule['condition_value'], $currencyCode))
                                                                    : '',
                                                                'discount' => __('home.promotion_discount_' . ($rule['discount_type'] ?? '')),
                                                                'discount_value' => isset($rule['discount_value'])
                                                                    ? ($rule['discount_type'] === 'percentage'
                                                                        ? $rule['discount_value'] . '%'
                                                                        : $currencyService->convertWithSymbol($rule['discount_value'], $currencyCode))
                                                                    : '',
                                                            ]) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($promo['ends_at'])
                                            <div class="text-red-500 text-xs mt-1">
                                                {{ __('home.promotion_ends_at') }}:
                                                {{ \Carbon\Carbon::parse($promo['ends_at'])->format('Y-m-d H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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
                                class="px-4 py-2 rounded border cursor-pointer {{ $selectedVariantId == $v->id ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                                {{ $specs ?: $v->sku }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- 购买数量 --}}
            <div class="mb-4 flex items-center gap-2">
                <span class="font-semibold text-gray-700">{{ __('home.qty') }}:</span>
                <button type="button" class="w-10 py-1 bg-gray-200 rounded" wire:click="decrementQty">-</button>
                <input type="number" min="1" max="{{ $maxQty }}" wire:model.lazy="qty"
                    wire:change="updateQty($event.target.value)" class="w-16 text-center border rounded px-2 py-0.5" />
                <button type="button" class="w-10 py-1 bg-gray-200 rounded" wire:click="incrementQty">+</button>
                <span class="text-gray-400 ml-2 text-sm">{{ __('home.storage', ['storage' => $maxQty]) }}</span>
            </div>
            {{-- 购买按钮 --}}
            <div class="mt-6 flex items-center gap gap-4">
                <button
                    wire:click="$dispatch('cart:add', { productId: {{ $productId }}, variantId: {{ $variantId }}, qty: {{ $qty }} })"
                    class="w-full px-6 py-3 bg-teal-600 text-white rounded-lg font-bold hover:bg-teal-700 transition">
                    {{ __('home.addCart') }}
                </button>

                <button wire:click="buyNow"
                    class="w-full px-6 py-3 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition">
                    {{ __('home.buy_now') }}
                </button>
            </div>
            <div class="py-6">
                <x-share-buttons title="{{ $name }}" description="{{ $shortDesc }}"
                    image="{{ $images->first()->getUrl() }}" />
            </div>
            <x-promotion-list class="py-6" />
        </div>
    </div>

    {{-- Tab 切换 --}}
    <div class="p-6 my-9 bg-gray-50 rounded-xl">
        <div class="flex border-b mb-4 border-teal-600">
            <a href="?tab=desc"
                class="px-4 py-2 font-semibold {{ $tab == 'desc' ? 'border-b-2 border-teal-600 text-teal-700' : 'text-gray-500' }}">{{ __('home.product_description') }}</a>
            <a href="?tab=reviews"
                class="px-4 py-2 font-semibold {{ $tab == 'reviews' ? 'border-b-2 border-teal-600 text-teal-700' : 'text-gray-500' }}">{{ __('home.product_reviews') }}</a>
        </div>
        @if ($tab == 'desc')
            @if ($desc)
                <div class="prose max-w-none text-gray-800 px-6">
                    {!! $desc !!}
                </div>
            @endif
        @else
            @livewire('components.product-reviews', ['productId' => $product->id], key('product-reviews-' . $product->id))
        @endif
    </div>

    <div class="py-10">
        @livewire('components.recommend-products', ['currentProductId' => $product->id, 'categoryIds' => $product->productCategories?->pluck('id')])
    </div>
</div>

@pushOnce('seo')
    <x-layouts.seo title="{{ $name }}" description="{{ $shortDesc }}"
        image="{{ $images->first()->getUrl() }}" />
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <style>
        /* 自定义样式 */
        .group:hover .group-hover\:opacity-100 {
            opacity: 1;
        }

        /* 优化鼠标悬停时的过渡效果 */
        .cursor-crosshair:hover {
            cursor: crosshair;
        }

        /* 禁用图片拖拽 */
        img {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
            pointer-events: none;
        }

        /* 确保按钮可以点击 */
        button {
            pointer-events: auto;
        }
    </style>
@endPushOnce
