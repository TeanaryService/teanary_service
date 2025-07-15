<div class="flex gap-2 items-center">
    {{-- 切换语言 --}}
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button icon="heroicon-o-globe-alt" icon-position="after" size="sm">
                {{ $selectedLanguage->name }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($languages as $lang)
                <x-filament::dropdown.list.item
                    x-on:click.prevent="
                        document.getElementById('change-language-{{ $lang->id }}').submit();
                    ">
                    {{ $lang->name }}
                </x-filament::dropdown.list.item>

                <form id="change-language-{{ $lang->id }}" method="POST"
                    action="{{ route('locale-currency-switcher.change-language') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang->code }}">
                </form>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>

    {{-- 切换货币 --}}
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button size="sm">
                {{ $selectedCurrency->symbol }} {{ $selectedCurrency->code }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($currencies as $currency)
                <x-filament::dropdown.list.item
                    x-on:click.prevent="
                        document.getElementById('change-currency-{{ $currency->id }}').submit();
                    ">
                    {{ $selectedCurrency->symbol }} {{ $currency->code }}
                </x-filament::dropdown.list.item>

                <form id="change-currency-{{ $currency->id }}" method="POST"
                    action="{{ route('locale-currency-switcher.change-currency') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="currency" value="{{ $currency->code }}">
                </form>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
