@props(['type' => 'info', 'message' => '', 'duration' => 3000])

@php
    $alertClasses = getAlertClasses($type);
    $baseClasses = $alertClasses['baseClasses'];
    $typeClasses = $alertClasses['typeClasses'];
@endphp

<div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ $duration }})" x-show="show"
    x-transition:enter="transform ease-out duration-300 transition" x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transform ease-in duration-200 transition"
    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
    class="{{ $baseClasses }} {{ $typeClasses }}" role="alert">

    @switch($type)
        @case('success')
            <x-heroicon-o-check-circle class="w-5 h-5 text-teal-500" />
            @break

        @case('error')
            <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
            @break

        @case('warning')
            <x-heroicon-o-exclamation-circle class="w-5 h-5 text-yellow-500" />
            @break

        @default
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500" />
    @endswitch

    <div class="flex-1 text-sm">
        {{ $message }}
    </div>

    <button @click="show = false" class="ml-2 text-gray-400 hover:text-gray-700 text-lg leading-none">
        &times;
    </button>
</div>
