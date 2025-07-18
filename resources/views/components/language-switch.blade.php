{{-- 切换语言 --}}
<div x-data="{ open: false }" class="relative">
    <button @click="open = ! open"
        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        {{ $selectedLanguage->name }}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 ml-1 text-white" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" @click.away="open = false"
        class="absolute right-0 mt-2 w-40 bg-white border border-green-200 rounded shadow-lg z-50">
        @foreach ($languages as $lang)
            <a href="{{ switch_locale_url($lang->code) }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-100">
                {{ $lang->name }}
            </a>
        @endforeach
    </div>
</div>
