@props(['type' => 'info', 'message' => '', 'duration' => 3000])

@php
    $baseClasses =
        'fixed top-20 right-6 z-50 max-w-xs w-full px-5 py-4 rounded-xl shadow-xl text-sm flex items-start space-x-3 border-l-4';
    $types = [
        'success' => 'bg-teal-50 text-teal-800 border-teal-500',
        'error' => 'bg-red-50 text-red-800 border-red-500',
        'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-500',
        'info' => 'bg-blue-50 text-blue-800 border-blue-500',
    ];

    $icons = [
        'success' =>
            '<svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        'error' =>
            '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        'warning' =>
            '<svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.79-2.89L13.42 4.58a2 2 0 00-2.83 0L3.14 16.11A2 2 0 004.93 19z"/></svg>',
        'info' =>
            '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>',
    ];
@endphp

<div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ $duration }})" x-show="show"
    x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200 transition"
    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
    class="{{ $baseClasses }} {{ $types[$type] ?? $types['info'] }}" role="alert">
    {!! $icons[$type] ?? $icons['info'] !!}
    <div class="flex-1 text-sm">
        {{ $message }}
    </div>
    <button @click="show = false" class="ml-2 text-gray-400 hover:text-gray-700 text-lg leading-none">
        &times;
    </button>
</div>
