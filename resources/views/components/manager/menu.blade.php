<div x-data="{ open: false }" class="relative inline-block text-left">
    <x-widgets.button 
        @click="open = !open" 
        type="button"
        variant="secondary"
        size="md"
        class="!p-2 !bg-transparent !border-0 !shadow-none"
    >
        @php
            $manager = auth('manager')->user();
            $avatarUrl = $manager?->getAvatarUrl();
        @endphp
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
        @else
            <div class="w-8 h-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 text-sm font-semibold">
                {{ Str::of($manager?->name ?? 'M')->substr(0, 1)->upper() }}
            </div>
        @endif
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </x-widgets.button>

    <div x-show="open" @click.away="open = false" x-transition
        class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 border border-teal-300">
        <div class="py-1">
            <form method="POST" action="{{ locaRoute('manager.logout') }}">
                @csrf
                <x-widgets.button 
                    type="submit"
                    variant="secondary"
                    size="sm"
                    class="w-full !justify-start !text-left"
                >
                    {{ __('app.logout') }}
                </x-widgets.button>
            </form>
        </div>
    </div>
</div>
