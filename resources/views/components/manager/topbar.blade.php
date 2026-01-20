<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
    <div class="flex items-center space-x-4">
        <a href="{{ locaRoute('home') }}" target="_blank" class="text-sm font-medium text-gray-700 hover:text-teal-600 transition-colors">
            {{ __('app.home') }}
        </a>
    </div>

    <div class="flex items-center space-x-4">
        {{-- 语言和货币切换器 --}}
        @livewire(\App\Filament\Manager\Widgets\LanguageCurrencySwitcher::class)
        
        {{-- 通知铃铛 --}}
        @livewire('filament.manager.components.notification-bell')
    </div>
</header>
