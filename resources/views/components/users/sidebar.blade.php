@props(['active' => ''])

@php
    $navItems = [
        [
            'name' => __('app.profile'),
            'route' => 'auth.profile',
            'icon' => 'heroicon-o-user-circle',
            'key' => 'profile',
        ],
        [
            'name' => __('orders.my_orders'),
            'route' => 'auth.orders',
            'icon' => 'heroicon-o-shopping-bag',
            'key' => 'orders',
        ],
        [
            'name' => __('app.addresses.title'),
            'route' => 'auth.addresses',
            'icon' => 'heroicon-o-map-pin',
            'key' => 'addresses',
        ],
        [
            'name' => __('notifications.title'),
            'route' => 'auth.notifications',
            'icon' => 'heroicon-o-bell',
            'key' => 'notifications',
        ],
    ];
@endphp

<aside class="w-full md:w-64 flex-shrink-0">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 bg-gradient-to-r from-teal-50 to-teal-100 border-b border-teal-200">
            <h2 class="text-lg font-semibold text-teal-900">{{ __('app.user_center') }}</h2>
        </div>
        <nav class="p-2">
            <ul class="space-y-1">
                @foreach($navItems as $item)
                    <li>
                        <a href="{{ locaRoute($item['route']) }}" 
                           wire:navigate.hover
                           class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 data-[current]:bg-teal-50 data-[current]:text-teal-700 data-[current]:border-l-4 data-[current]:border-teal-600 text-gray-700 hover:bg-gray-50 hover:text-teal-600">
                            <x-dynamic-component :component="$item['icon']" class="w-5 h-5 flex-shrink-0" />
                            <span>{{ $item['name'] }}</span>
                            <svg class="w-4 h-4 ml-auto text-teal-600 data-[current]:block hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
