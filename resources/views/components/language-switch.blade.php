{{-- 切换语言 --}}
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open"
        class="flex items-center gap-2 px-2 md:px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 h-10">
        <x-heroicon-o-globe-alt class="w-6 h-6" />
        <div class="hidden lg:block">{{ $selectedLanguage->name }}</div>
        <x-heroicon-o-chevron-down class="w-4 h-4 hidden md:block" />
    </button>
    <div x-show="open" @click.away="open = false"
        class="absolute right-0 mt-2 w-40 bg-white border border-green-200 rounded-lg shadow-lg z-50">
        @foreach ($languages as $lang)
            <a href="{{ switch_locale_url($lang->code) }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-100">
                {{ $lang->name }}
            </a>
        @endforeach
    </div>
</div>
