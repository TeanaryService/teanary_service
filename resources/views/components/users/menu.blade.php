<div x-data="{ open: false }" class="relative inline-block text-left">
    {{-- <button @click="open = !open" type="button"
        class="inline-flex items-center px-2 md:px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-teal-700 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 gap gap-x-2 h-10"> --}}
    <button @click="open = !open" type="button"
        class="inline-flex items-center py-2 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 gap gap-x-2 h-10">
        {{-- <x-heroicon-o-user-circle class="w-6 h-6" />
        <div class="hidden lg:block">{{ auth()->user()->name ?? 'Guest' }}</div> --}}
        <image src="{{ auth()->user()->getFilamentAvatarUrl() }}" class="w-8 h-8 rounded-full">
            <x-heroicon-o-chevron-down class="w-4 h-4" />
    </button>

    <div x-show="open" @click.away="open = false" x-transition
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-teal-300">
        <div class="py-1">
            <a href="{{ locaRoute('auth.orders') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700">
                {{ __('orders.my_orders') }}
            </a>
            <a href="{{ locaRoute('auth.notifications') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700 relative">
                {{ __('notifications.my_notifications') }}
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                @endif
            </a>
            <a href="{{ locaRoute('auth.addresses') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700">
                {{ __('app.addresses.my_addresses') }}
            </a>
            <a href="{{ locaRoute('auth.profile') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700">
                {{ __('app.profile') }}
            </a>
            <form method="POST" action="{{ locaRoute('auth.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-700">
                    {{ __('app.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
