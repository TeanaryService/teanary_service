<div class="flex gap-4 items-center">
    {{-- 切换语言 --}}
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button icon="heroicon-o-globe-alt" size="sm">
                {{ $selectedLanguage->name }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($languages as $lang)
                <x-filament::dropdown.list.item
                    x-on:click.prevent="
                        document.getElementById('lang-input').value = '{{ $lang->code }}';
                        document.getElementById('lang-currency-form').submit();
                    ">
                    {{ $lang->name }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>

    {{-- 切换货币 --}}
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button size="sm">
                <span class="font-bold">{{ $selectedCurrency->symbol }}</span>
                {{ $selectedCurrency->code }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($currencies as $currency)
                <x-filament::dropdown.list.item
                    x-on:click.prevent="
                        document.getElementById('currency-input').value = '{{ $currency->code }}';
                        document.getElementById('lang-currency-form').submit();
                    ">
                    <span class="text-xl font-bold">{{ $currency->symbol }}</span>
                    {{ $currency->code }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>

    {{-- 单一隐藏表单 --}}
    <form id="lang-currency-form" method="POST" action="{{ locaRoute('currency-switcher.update') }}" class="hidden">
        @csrf
        <input type="hidden" name="lang" id="lang-input">
        <input type="hidden" name="currency" id="currency-input">
    </form>
</div>
