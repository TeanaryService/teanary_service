@php
    if (!isset($currencies) || !isset($selectedCurrency)) {
        $service = app(\App\Services\LocaleCurrencyService::class);
        $currencies = $service->getCurrencies();
        $currencyCode = session('currency');
        $selectedCurrency = $service->getCurrencyByCode($currencyCode);
    }
@endphp

<div>
    {{-- 切换货币 --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = ! open"
            class="flex items-center gap-2 px-2 md:px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 h-10">
            <div class="font-bold w-6 h-6 md:w-auto md:h-auto">{{ $selectedCurrency->symbol ?? '' }}</div>
            <div class="hidden md:block">{{ $selectedCurrency->code ?? '' }}</div>
            <x-heroicon-o-chevron-down class="w-4 h-4 hidden md:block" />
        </button>
        <div x-show="open" @click.away="open = false"
            class="absolute right-0 mt-2 w-48 bg-white border border-teal-200 rounded-lg shadow-lg z-50">
            @foreach ($currencies as $currency)
                <div @click.prevent="
                        document.getElementById('currency-input').value = '{{ $currency->code }}';
                        document.getElementById('lang-currency-form').submit();
                    "
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-100 cursor-pointer">
                    <span class="font-bold">{{ $currency->symbol }}</span>
                    {{ $currency->code }}
                </div>
            @endforeach
        </div>
    </div>

    <form id="lang-currency-form" method="POST" action="{{ locaRoute('currency-switcher.update') }}" class="hidden">
        @csrf
        <input type="hidden" name="lang" id="lang-input">
        <input type="hidden" name="currency" id="currency-input">
    </form>
</div>
