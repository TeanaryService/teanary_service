@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'wire' => null, // wire:model, wire:model.live, etc.
    'label' => null,
    'class' => '',
])

@php
    $radioId = $id ?? $name ?? uniqid('radio_');
    $baseClasses = 'w-4 h-4 text-teal-500 border-gray-300 focus:ring-teal-500';
    $classes = trim($baseClasses . ' ' . $class);
    
    // 处理 wire 属性，支持 wire:model, wire:model.live 等
    $wireAttribute = null;
    if ($wire) {
        if (str_starts_with($wire, 'live=')) {
            $wireAttribute = 'wire:model.live="' . substr($wire, 5) . '"';
        } else {
            $wireAttribute = 'wire:model="' . $wire . '"';
        }
    }
@endphp

<div class="flex items-center gap-3">
    <input 
        type="radio"
        id="{{ $radioId }}"
        @if($name) name="{{ $name }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @if($checked) checked @endif
        @if($wireAttribute) {!! $wireAttribute !!} @endif
        class="{{ $classes }}"
        {{ $attributes->except(['name', 'id', 'value', 'checked', 'wire', 'label', 'class'])->merge(['wire:click' => $attributes->get('wire:click')]) }}
    />
    @if($label)
        <label for="{{ $radioId }}" class="text-sm font-medium text-gray-700 cursor-pointer">
            {{ $label }}
        </label>
    @endif
    @if($slot->isNotEmpty())
        <label for="{{ $radioId }}" class="cursor-pointer">
            {{ $slot }}
        </label>
    @endif
</div>
