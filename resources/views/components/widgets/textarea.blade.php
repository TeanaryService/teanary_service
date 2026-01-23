@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'wire' => null, // wire:model, wire:model.live, etc.
    'error' => null,
    'class' => '',
])

@php
    $textareaId = $id ?? $name;
    // 统一文本域样式：与input保持一致的基础样式
    $baseClasses = 'w-full px-4 py-2.5 min-h-[80px] rounded-xl border-2 border-teal-100 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 hover:border-teal-200 hover:shadow-md resize-y disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:border-gray-200';
    $classes = trim($baseClasses . ' ' . $class);
    if ($error) {
        $classes .= ' @error(\'' . $error . '\') border-red-300 @enderror';
    }
@endphp

<textarea 
    @if($textareaId) id="{{ $textareaId }}" @endif
    @if($name) name="{{ $name }}" @endif
    rows="{{ $rows }}"
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($wire) wire:model="{{ $wire }}" @endif
    class="{{ $classes }}"
    {{ $attributes->except(['name', 'id', 'value', 'placeholder', 'rows', 'required', 'disabled', 'readonly', 'wire', 'error', 'class']) }}
>{{ $value }}</textarea>

@if($error)
    @error($error)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
@endif
