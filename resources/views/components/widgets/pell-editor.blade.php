@props([
    'id' => null,
    'wire' => null, // e.g. "translations.1.content" or "defer=translations.1.content"
    'minHeight' => '240px',
    'class' => '',
])

@php
    $inputId = $id ?? 'pell-editor-' . uniqid();

    // Parse wire attribute (same convention as other widgets)
    $wireDirective = null;
    if ($wire) {
        if (str_contains($wire, '=')) {
            [$modifiers, $model] = explode('=', $wire, 2);
            $wireDirective = 'wire:model.' . str_replace('.', '.', $modifiers) . '="' . $model . '"';
        } else {
            $wireDirective = 'wire:model="' . $wire . '"';
        }
    }

    $wrapperClass = trim('teany-pell border border-gray-200 rounded-xl overflow-hidden bg-white ' . $class);
@endphp

<div class="space-y-2">
    <div wire:ignore class="{{ $wrapperClass }}">
        <div
            data-teany-pell-editor
            data-input-id="{{ $inputId }}"
            data-min-height="{{ $minHeight }}"
        ></div>
    </div>

    <textarea
        id="{{ $inputId }}"
        @if($wireDirective) {!! $wireDirective !!} @endif
        class="hidden"
    >{!! $slot !!}</textarea>
</div>

