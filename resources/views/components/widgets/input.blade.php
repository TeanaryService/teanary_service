@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'wire' => null, // wire:model, wire:model.live, wire:model.live.debounce.300ms, etc.
    'error' => null, // error key for @error directive
    'class' => '',
])

@php
    $inputId = $id ?? $name;
    $baseClasses = 'w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white text-gray-900 placeholder-gray-400 shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 hover:border-gray-300';
    $classes = trim($baseClasses . ' ' . $class);
    
    // Parse wire attribute - support formats like:
    // - "search" -> wire:model="search"
    // - "live.debounce.300ms=search" -> wire:model.live.debounce.300ms="search"
    // - "lazy=qty" -> wire:model.lazy="qty"
    $wireDirective = null;
    if ($wire) {
        if (str_contains($wire, '=')) {
            [$modifiers, $model] = explode('=', $wire, 2);
            // Handle modifiers like "live.debounce.300ms" or "lazy"
            $modifierParts = explode('.', $modifiers);
            $wireDirective = 'wire:model.' . implode('.', $modifierParts) . '="' . $model . '"';
        } else {
            $wireDirective = 'wire:model="' . $wire . '"';
        }
    }
@endphp

<input 
    type="{{ $type }}"
    @if($inputId) id="{{ $inputId }}" @endif
    @if($name) name="{{ $name }}" @endif
    @if($value !== null) value="{{ $value }}" @endif
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($wireDirective) {!! $wireDirective !!} @endif
    class="{{ $classes }}"
    {{ $attributes->except(['type', 'name', 'id', 'value', 'placeholder', 'required', 'disabled', 'readonly', 'wire', 'error', 'class']) }}
/>

@if($error)
    @error($error)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
@endif
