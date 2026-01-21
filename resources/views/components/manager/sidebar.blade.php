@props(['active' => ''])

@php
    $navItems = [
        [
            'name' => __('manager.dashboard.heading'),
            'route' => 'manager.home',
            'icon' => 'heroicon-o-home',
            'key' => 'home',
        ],
        [
            'name' => __('notifications.my_notifications'),
            'route' => 'manager.notifications',
            'icon' => 'heroicon-o-bell',
            'key' => 'notifications',
        ],
        [
            'name' => __('manager.traffic_statistics.navigation_label'),
            'route' => 'manager.traffic-statistics',
            'icon' => 'heroicon-o-chart-bar',
            'key' => 'traffic-statistics',
        ],
    ];

    $systemItems = [
        [
            'name' => __('manager.managers.label'),
            'route' => 'manager.managers',
            'icon' => 'heroicon-o-lifebuoy',
            'key' => 'managers',
        ],
        [
            'name' => __('manager.languages.label'),
            'route' => 'manager.languages',
            'icon' => 'heroicon-o-language',
            'key' => 'languages',
        ],
        [
            'name' => __('manager.currencies.label'),
            'route' => 'manager.currencies',
            'icon' => 'heroicon-o-currency-dollar',
            'key' => 'currencies',
        ],
        [
            'name' => __('manager.countries.label'),
            'route' => 'manager.countries',
            'icon' => 'heroicon-o-globe-alt',
            'key' => 'countries',
        ],
        [
            'name' => __('manager.zones.label'),
            'route' => 'manager.zones',
            'icon' => 'heroicon-o-globe-americas',
            'key' => 'zones',
        ],
    ];

    $businessItems = [
        [
            'name' => __('manager.orders.label'),
            'route' => 'manager.orders',
            'icon' => 'heroicon-o-receipt-percent',
            'key' => 'orders',
        ],
        [
            'name' => __('manager.promotions.label'),
            'route' => 'manager.promotions',
            'icon' => 'heroicon-o-gift-top',
            'key' => 'promotions',
        ],
        [
            'name' => __('manager.articles.label'),
            'route' => 'manager.articles',
            'icon' => 'heroicon-o-pencil-square',
            'key' => 'articles',
        ],
        [
            'name' => __('manager.carts.label'),
            'route' => 'manager.carts',
            'icon' => 'heroicon-o-shopping-cart',
            'key' => 'carts',
        ],
        [
            'name' => __('manager.contacts.label'),
            'route' => 'manager.contacts',
            'icon' => 'heroicon-o-envelope-open',
            'key' => 'contacts',
        ],
    ];

    $productItems = [
        [
            'name' => __('manager.products.label'),
            'route' => 'manager.products',
            'icon' => 'heroicon-o-cube',
            'key' => 'products',
        ],
        [
            'name' => __('manager.categories.label'),
            'route' => 'manager.categories',
            'icon' => 'heroicon-o-squares-2x2',
            'key' => 'categories',
        ],
        [
            'name' => __('manager.attributes.label'),
            'route' => 'manager.attributes',
            'icon' => 'heroicon-o-adjustments-horizontal',
            'key' => 'attributes',
        ],
        [
            'name' => __('manager.attribute_values.label'),
            'route' => 'manager.attribute-values',
            'icon' => 'heroicon-o-tag',
            'key' => 'attribute-values',
        ],
        [
            'name' => __('manager.specifications.label'),
            'route' => 'manager.specifications',
            'icon' => 'heroicon-o-table-cells',
            'key' => 'specifications',
        ],
        [
            'name' => __('manager.specification_values.label'),
            'route' => 'manager.specification-values',
            'icon' => 'heroicon-o-rectangle-group',
            'key' => 'specification-values',
        ],
    ];

    $userItems = [
        [
            'name' => __('manager.users.label'),
            'route' => 'manager.users',
            'icon' => 'heroicon-o-user',
            'key' => 'users',
        ],
        [
            'name' => __('manager.addresses.label'),
            'route' => 'manager.addresses',
            'icon' => 'heroicon-o-map-pin',
            'key' => 'addresses',
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
            
            @if(count($systemItems) > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('manager.languages.group') }}
                    </h3>
                    <ul class="space-y-1">
                        @foreach($systemItems as $item)
                            @php
                                $isActive = $active === $item['key'] || request()->routeIs($item['route'] . '*');
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
                </div>
            @endif

            @if(count($businessItems) > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('manager.articles.group') }}
                    </h3>
                    <ul class="space-y-1">
                        @foreach($businessItems as $item)
                            @php
                                $isActive = $active === $item['key'] || request()->routeIs($item['route'] . '*');
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
                </div>
            @endif

            @if(count($productItems) > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('manager.attributes.group') }}
                    </h3>
                    <ul class="space-y-1">
                        @foreach($productItems as $item)
                            @php
                                $isActive = $active === $item['key'] || request()->routeIs($item['route'] . '*');
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
                </div>
            @endif

            @if(count($userItems) > 0)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('manager.addresses.group') }}
                    </h3>
                    <ul class="space-y-1">
                        @foreach($userItems as $item)
                            @php
                                $isActive = $active === $item['key'] || request()->routeIs($item['route'] . '*');
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
                </div>
            @endif
        </nav>
    </div>
</aside>
