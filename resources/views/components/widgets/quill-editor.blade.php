@props([
    'id' => null,
    'wire' => null, // e.g. "translations.1.content" or "defer=translations.1.content"
    'minHeight' => '240px',
    'class' => '',
    'uploadUrl' => null,
])

@php
    $inputId = $id ?? 'quill-editor-' . uniqid();

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

    $wrapperClass = trim('teany-quill border border-gray-200 rounded-xl overflow-hidden bg-white ' . $class);
    $resolvedUploadUrl = $uploadUrl ?? locaRoute('manager.editor-uploads.image');
@endphp

<div class="space-y-2">
    <div wire:ignore class="{{ $wrapperClass }}">
        <div
            data-teany-quill-editor
            data-input-id="{{ $inputId }}"
            data-min-height="{{ $minHeight }}"
            data-upload-url="{{ $resolvedUploadUrl }}"
        ></div>
    </div>

    <textarea
        id="{{ $inputId }}"
        @if($wireDirective) {!! $wireDirective !!} @endif
        class="hidden"
    >{!! $slot !!}</textarea>
</div>

