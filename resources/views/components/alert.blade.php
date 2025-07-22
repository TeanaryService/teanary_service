@props(['type' => 'info', 'message' => '', 'duration' => 3000])

@php
    $baseClasses = 'fixed top-20 right-6 z-50 px-4 py-3 rounded-xl shadow-lg text-sm flex items-center space-x-2';
    $types = [
        'success' => 'bg-green-100 text-green-800 border border-green-300',
        'error' => 'bg-red-100 text-red-800 border border-red-300',
        'warning' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
        'info' => 'bg-blue-100 text-blue-800 border border-blue-300',
    ];
@endphp

<div>
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, {{ $duration }})" x-show="show" x-transition
        class="{{ $baseClasses }} {{ $types[$type] ?? $types['info'] }}" role="alert">
        <span>{{ $message }}</span>
        <button @click="show = false" class="ml-2 text-xl leading-none">&times;</button>
    </div>
</div>
