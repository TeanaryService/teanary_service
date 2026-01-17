<div class="flex items-center gap-4">
    @if(($panelId ?? 'manager') === 'manager')
        @livewire('filament.manager.components.notification-bell')
        @livewire(\App\Filament\Manager\Widgets\LanguageCurrencySwitcher::class)
    @elseif(($panelId ?? 'user') === 'user')
        @livewire('filament.user.components.notification-bell')
        @livewire(\App\Filament\Manager\Widgets\LanguageCurrencySwitcher::class)
    @endif
</div>
