<div x-data="{ open: false }" class="relative inline-block text-left">
    <button
        @click="open = !open"
        type="button"
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
    >
        <svg class="h-5 w-5 text-green-600 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4.5 19.5a8.25 8.25 0 0115 0v.75a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V19.5z"/>
        </svg>
        {{ auth()->user()->name ?? 'Guest' }}
        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div
        x-show="open"
        @click.away="open = false"
        x-transition
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
    >
        <div class="py-1">
            <a href="{{ locaRoute('filament.personal.pages.dashboard') }}"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                {{ __('app.profile') }}
            </a>
            <form method="POST" action="{{ locaRoute('filament.personal.auth.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                    {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
