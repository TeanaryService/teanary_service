<div x-data="{ open: false }" class="relative inline-block text-left">
    {{-- <button @click="open = !open" type="button"
        class="inline-flex items-center px-2 md:px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 gap gap-x-2 h-10"> --}}
    <button @click="open = !open" type="button"
        class="inline-flex items-center py-2 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 gap gap-x-2 h-10">
        {{-- <x-heroicon-o-user-circle class="w-6 h-6" />
        <div class="hidden lg:block">{{ auth()->user()->name ?? 'Guest' }}</div> --}}
        <image src="{{ auth()->user()->getFilamentAvatarUrl() }}" class="w-8 h-8 rounded-full">
            <x-heroicon-o-chevron-down class="w-4 h-4" />
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-green-300">
        <div class="py-1">
            <a target="_blank" href="{{ route('filament.personal.pages.dashboard') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                {{ __('app.profile') }}
            </a>
            <form method="POST" action="{{ route('filament.personal.auth.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                    {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
