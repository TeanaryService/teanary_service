@props(['promotions' => []])

@php
    $currencyService = app(\App\Services\LocaleCurrencyService::class);
    $currencyCode = session('currency');
@endphp

<div class="w-full max-w-7xl mx-auto">
    @if (count($promotions))
        <div class="bg-green-50 border-l-4 border-green-400 p-6 rounded-2xl shadow-md mb-10">
            <h2 class="text-2xl font-bold text-green-800 mb-4">{{ __('home.promotions') }}</h2>

            <ul class="space-y-6">
                @foreach ($promotions as $promotion)
                    <li
                        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex-1">
                            <div class="text-green-900 font-semibold text-lg">{{ $promotion['name'] }}</div>

                            @if (!empty($promotion['description']))
                                <p class="text-gray-600 text-sm mt-1">{{ $promotion['description'] }}</p>
                            @endif

                            @if (!empty($promotion['rules']))
                                <ul class="mt-3 text-sm text-green-700 list-disc pl-5 space-y-1">
                                    @foreach ($promotion['rules'] as $rule)
                                        <li>
                                            {{ __('home.promotion_text', [
                                                'condition' => __('home.promotion_rule_' . ($rule['condition_type'] ?? '')),
                                                'condition_value' => $rule['condition_value'] ? $currencyService->convertWithSymbol($rule['condition_value'], $currencyCode) : '',
                                                'discount' => __('home.promotion_discount_' . ($rule['discount_type'] ?? '')),
                                                'discount_value' => $rule['discount_value'] ? $currencyService->convertWithSymbol($rule['discount_value'], $currencyCode) : '',
                                            ]) }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <div class="text-xs text-gray-500 md:text-right">
                            @if ($promotion['starts_at'])
                                <div>{{ __('home.promotion_starts_at') }}:
                                    {{ \Carbon\Carbon::parse($promotion['starts_at'])->format('Y-m-d') }}
                                </div>
                            @endif
                            @if ($promotion['ends_at'])
                                <div class="mt-1">{{ __('home.promotion_ends_at') }}:
                                    {{ \Carbon\Carbon::parse($promotion['ends_at'])->format('Y-m-d') }}
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
