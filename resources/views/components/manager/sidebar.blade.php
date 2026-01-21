@props(['active' => ''])

@php
    $navItems = [
        [
            'name' => __('filament.dashboard.heading'),
            'route' => 'manager.home',
            'icon' => 'heroicon-o-home',
            'key' => 'home',
        ],
    ];
@endphp

<aside class="w-full md:w-64 flex-shrink-0">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 bg-gradient-to-r from-teal-50 to-teal-100 border-b border-teal-200">
            <h2 class="text-lg font-semibold text-teal-900">{{ __('app.manager_center') }}</h2>
        </div>
        <nav class="p-2">
            <ul class="space-y-1">
                @foreach($navItems as $item)
                    @php
                        $isActive = $active === $item['key'] || request()->routeIs($item['route']);
                    @endphp
                    <li>
                        <a href="{{ locaRoute($item['route']) }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 {{ $isActive ? 'bg-teal-50 text-teal-700 border-l-4 border-teal-600' : 'text-gray-700 hover:bg-gray-50 hover:text-teal-600' }}">
                            <x-dynamic-component :component="$item['icon']" class="w-5 h-5 flex-shrink-0" />
                            <span>{{ $item['name'] }}</span>
                            @if($isActive)
                                <svg class="w-4 h-4 ml-auto text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
