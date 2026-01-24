@php
    $currentRoute = request()->route()->getName();
    $manager = auth('manager')->user();
@endphp

<aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-center border-b border-gray-200">
        <a href="{{ locaRoute('manager.dashboard') }}" class="flex items-center space-x-2">
            <x-layouts.logo imgClass="max-w-12 max-h-12" />
            <!-- <span class="text-lg font-bold text-gray-900">{{ __('app.manager_panel') }}</span> -->
        </a>
    </div>

    {{-- 导航菜单 --}}
    <nav class="flex-1 overflow-y-auto py-4">
        <div class="px-3 space-y-1">
            <a href="{{ locaRoute('manager.dashboard') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.dashboard') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                {{ __('filament.dashboard.title') }}
            </a>

            <a href="{{ locaRoute('manager.orders') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.orders') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                {{ __('filament.OrderResource.pluralLabel') }}
            </a>

            <a href="{{ locaRoute('manager.users') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.users') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                {{ __('filament.UserResource.pluralLabel') }}
            </a>

            <a href="{{ locaRoute('manager.products') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.products') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                {{ __('filament.ProductResource.pluralLabel') }}
            </a>

            <a href="{{ locaRoute('manager.categories') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.categories') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ __('filament.CategoryResource.pluralLabel') }}
            </a>

            <a href="{{ locaRoute('manager.traffic-statistics') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.traffic-statistics') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ __('filament.TrafficStatistics.navigation_label') }}
            </a>

            <a href="{{ locaRoute('manager.notifications') }}" 
               class="flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ str_starts_with($currentRoute, 'manager.notifications') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {{ __('notifications.my_notifications') }}
                @if($manager && $manager->unreadNotifications->count() > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $manager->unreadNotifications->count() > 99 ? '99+' : $manager->unreadNotifications->count() }}
                    </span>
                @endif
            </a>
        </div>
    </nav>

    {{-- 底部用户信息 --}}
    <div class="border-t border-gray-200 p-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                <span class="text-teal-600 font-semibold">{{ substr($manager->name ?? 'M', 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $manager->name ?? 'Manager' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $manager->email ?? '' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ locaRoute('manager.logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                {{ __('app.logout') }}
            </button>
        </form>
    </div>
</aside>
