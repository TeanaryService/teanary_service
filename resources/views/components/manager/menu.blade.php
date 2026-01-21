<div x-data="{ open: false }" class="relative inline-block text-left">
    <button @click="open = !open" type="button"
        class="inline-flex items-center py-2 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 gap gap-x-2 h-10">
        <image src="{{ auth('manager')->user()->getFilamentAvatarUrl() }}" class="w-8 h-8 rounded-full">
            <x-heroicon-o-chevron-down class="w-4 h-4" />
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-teal-300">
        <div class="py-1">
            <form method="POST" action="{{ locaRoute('manager.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700">
                    {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
