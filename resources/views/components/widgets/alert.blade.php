@props([
    'type' => 'info', // success, error, warning, info
    'message' => null,
    'dismissible' => false,
    'duration' => null, // Auto-dismiss duration in milliseconds (null = no auto-dismiss)
    'class' => '',
])

@php
    $typeConfig = match($type) {
        'success' => [
            'bg' => 'bg-teal-50',
            'text' => 'text-teal-800',
            'border' => 'border-teal-200',
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'text-teal-500',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'text' => 'text-red-800',
            'border' => 'border-red-200',
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'text-red-500',
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'text' => 'text-yellow-800',
            'border' => 'border-yellow-200',
            'icon' => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'text-yellow-500',
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-800',
            'border' => 'border-blue-200',
            'icon' => 'heroicon-o-information-circle',
            'iconColor' => 'text-blue-500',
        ],
        default => [
            'bg' => 'bg-gray-50',
            'text' => 'text-gray-800',
            'border' => 'border-gray-200',
            'icon' => 'heroicon-o-information-circle',
            'iconColor' => 'text-gray-500',
        ],
    };
    
    $classes = "rounded-xl border-2 {$typeConfig['bg']} {$typeConfig['border']} p-4 shadow-sm {$class}";
    $hasAlpine = $duration !== null || $dismissible;
@endphp

<div 
    @if($hasAlpine) 
        x-data="{ show: true }" 
        @if($duration) x-init="setTimeout(() => show = false, {{ $duration }})" @endif
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition" 
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100" 
        x-transition:leave="transform ease-in duration-200 transition"
        x-transition:leave-start="opacity-100 translate-y-0" 
        x-transition:leave-end="opacity-0 translate-y-2"
    @endif
    class="{{ $classes }}" 
    role="alert"
>
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <x-dynamic-component :component="$typeConfig['icon']" class="w-5 h-5 {{ $typeConfig['iconColor'] }}" />
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold {{ $typeConfig['text'] }}">
                {{ $message ?? $slot }}
            </p>
        </div>
        @if($dismissible)
            <button 
                type="button" 
                class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                @if($hasAlpine) @click="show = false" @else onclick="this.parentElement.parentElement.remove()" @endif
            >
                <x-heroicon-o-x-mark class="w-5 h-5" />
            </button>
        @endif
    </div>
</div>
