@props(['promotions' => null, 'parentClass' => '', 'class' => ''])

@php
    // 如果没有传递 promotions，则从服务中获取
    if ($promotions === null) {
        $promotions = app(\App\Services\PromotionService::class)->getAvailablePromotions(auth()->user());
    }
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="promotions">
    @if (count($promotions))
        <div class="{{ $parentClass }}">
            <div class="w-full max-w-screen 2xl:max-w-[75vw] mx-auto {{ $class }}">
                <div class="bg-teal-100 dark:bg-teal-900/20 border-l-4 border-teal-400 dark:border-teal-500 rounded-2xl shadow-md p-6 transition-colors duration-300">
                    <h2 class="text-2xl font-bold text-teal-800 dark:text-teal-300 mb-4 transition-colors duration-300">{{ __('home.promotions') }}</h2>

                    <ul class="space-y-6">
                        @foreach ($promotions as $promotion)
                            <li
                                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                                <div class="flex-1">
                                    <div class="text-teal-900 dark:text-teal-200 font-semibold text-lg transition-colors duration-300">{{ $promotion['name'] }}</div>

                                    <div class="text-xs text-gray-500 dark:text-gray-400 flex gap gap-6 items-center transition-colors duration-300">
                                        {{-- @if ($promotion['starts_at'])
                                    <p>{{ __('home.promotion_starts_at') }}:
                                        {{ \Carbon\Carbon::parse($promotion['starts_at'])->format('Y-m-d H:i') }}
                                    </p>
                                @endif --}}
                                        @if ($promotion['ends_at'])
                                            <p class="mt-1">{{ __('home.promotion_ends_at') }}:
                                                {{ \Carbon\Carbon::parse($promotion['ends_at'])->format('Y-m-d H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                    @if (!empty($promotion['description']))
                                        <p class="text-gray-600 dark:text-gray-300 text-sm mt-1 transition-colors duration-300">{{ $promotion['description'] }}</p>
                                    @endif

                                    @if (!empty($promotion['rules']))
                                        <ul class="mt-3 text-sm text-teal-700 dark:text-teal-300 list-disc pl-5 space-y-1 transition-colors duration-300">
                                            @foreach ($promotion['rules'] as $rule)
                                                <li>
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
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
