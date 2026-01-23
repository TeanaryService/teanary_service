@props([
    'name' => null,
    'id' => null,
    'wire' => null, // wire:model, wire:model.live, etc. Format: "live=filterStatus" or just "filterStatus"
    'options' => [], // ['value' => 'label'] or [['value' => 'x', 'label' => 'y']]
    'selected' => null,
    'placeholder' => null,
    'multiple' => false,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'class' => '',
])

@php
    $selectId = $id ?? $name;
    // 统一选择框样式：与input高度一致
    $baseClasses = 'w-full px-4 py-2.5 h-10 rounded-xl border-2 border-teal-100 bg-white text-sm text-gray-900 shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500 hover:border-teal-200 hover:shadow-md cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:border-gray-200';
    $classes = trim($baseClasses . ' ' . $class);
    if ($error) {
        $classes .= ' @error(\'' . $error . '\') border-red-300 @enderror';
    }
    
    // Parse wire attribute
    $wireDirective = null;
    if ($wire) {
        if (str_contains($wire, '=')) {
            [$modifiers, $model] = explode('=', $wire, 2);
            $wireDirective = 'wire:model.' . str_replace('.', '.', $modifiers) . '="' . $model . '"';
        } else {
            $wireDirective = 'wire:model="' . $wire . '"';
        }
    }
@endphp

<select 
    @if($selectId) id="{{ $selectId }}" @endif
    @if($name) name="{{ $name }}" @endif
    @if($wireDirective) {!! $wireDirective !!} @endif
    @if($multiple) multiple @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    class="{{ $classes }}"
    {{ $attributes->except(['name', 'id', 'wire', 'options', 'selected', 'placeholder', 'multiple', 'required', 'disabled', 'error', 'class']) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    
    @foreach($options as $key => $option)
        @if(is_array($option))
            <option value="{{ $option['value'] }}" @if($selected == $option['value'] || (is_array($selected) && in_array($option['value'], $selected))) selected @endif>
                {{ $option['label'] }}
            </option>
        @else
            <option value="{{ $key }}" @if($selected == $key || (is_array($selected) && in_array($key, $selected))) selected @endif>
                {{ $option }}
            </option>
        @endif
    @endforeach
</select>

@if($error)
    @error($error)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
@endif
