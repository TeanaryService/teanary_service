@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'checked' => false,
    'wire' => null,
    'wireClick' => null,
    'label' => null,
    'class' => '',
    'standalone' => false, // 如果为 true，不包含 label 容器，只返回 checkbox
])

@php
    $checkboxId = $id ?? $name ?? uniqid('checkbox_');
    // 使用 teal 主题颜色，与整站其他组件风格一致
    // 统一样式：边框、焦点状态、hover 效果与 input/select 等组件保持一致
    $baseClasses = 'w-4 h-4 text-teal-500 bg-white border-2 border-teal-100 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-0 focus:border-teal-500 checked:0 checked:border-teal-500 hover:border-teal-200 hover:shadow-sm transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:border-gray-200';
    $classes = trim($baseClasses . ' ' . $class);
    
    // 处理 wire 属性，支持 wire:model, wire:model.live, wire:click 等
    $wireAttributes = [];
    if ($wire) {
        if (str_starts_with($wire, 'live=')) {
            $wireAttributes['wire:model.live'] = substr($wire, 5);
        } else {
            $wireAttributes['wire:model'] = $wire;
        }
    }
    if ($wireClick) {
        $wireAttributes['wire:click'] = $wireClick;
    }
    // 从 attributes 中提取 wire:click
    if ($attributes->has('wire:click')) {
        $wireAttributes['wire:click'] = $attributes->get('wire:click');
    }
    
    // 处理 checked 状态，支持从 wire:model 自动判断
    $isChecked = $checked;
    if ($wire && $attributes->has('wire:model')) {
        // 如果使用 wire:model，checked 状态由 Livewire 自动管理
        $isChecked = false;
    }
@endphp

@if($standalone)
    <input 
        type="checkbox"
        id="{{ $checkboxId }}"
        @if($name) name="{{ $name }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @if($isChecked) checked @endif
        @foreach($wireAttributes as $key => $val)
            {!! $key !!}="{!! $val !!}"
        @endforeach
        class="{{ $classes }}"
        {{ $attributes->except(['name', 'id', 'value', 'checked', 'wire', 'wireClick', 'label', 'class', 'standalone', 'wire:click'])->merge($wireAttributes) }}
    />
@else
    <div class="flex items-center gap-2">
        <input 
            type="checkbox"
            id="{{ $checkboxId }}"
            @if($name) name="{{ $name }}" @endif
            @if($value !== null) value="{{ $value }}" @endif
            @if($isChecked) checked @endif
            @foreach($wireAttributes as $key => $val)
                {!! $key !!}="{!! $val !!}"
            @endforeach
            class="{{ $classes }}"
            {{ $attributes->except(['name', 'id', 'value', 'checked', 'wire', 'wireClick', 'label', 'class', 'standalone', 'wire:click'])->merge($wireAttributes) }}
        />
        @if($label)
            <label for="{{ $checkboxId }}" class="text-sm font-medium text-gray-700 cursor-pointer select-none hover:text-gray-900 transition-colors">
                {{ $label }}
            </label>
        @endif
        @if($slot->isNotEmpty())
            <label for="{{ $checkboxId }}" class="cursor-pointer select-none">
                {{ $slot }}
            </label>
        @endif
    </div>
@endif
